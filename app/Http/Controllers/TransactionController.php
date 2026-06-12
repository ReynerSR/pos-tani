<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\CustomerTierHistory;
use App\Models\MembershipRule;
use App\Models\PointHistory;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\RuleBasedMembershipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TransactionController extends Controller
{
    public function __construct(private RuleBasedMembershipService $membershipService)
    {
    }

    // Menampilkan antarmuka kasir (Point of Sale) untuk melakukan transaksi baru
    public function create()
    {
        $rule = MembershipRule::getCurrent();
        $newCustomer = null;

        if (session('pos_new_customer_id')) {
            $newCustomer = Customer::select('id', 'full_name', 'whatsapp_number', 'address', 'tier', 'point_balance', 'total_accumulation')
                ->find(session('pos_new_customer_id'));
        }

        return view('kasir.pos', compact('rule', 'newCustomer'));
    }

    // Menyimpan transaksi baru, memotong stok, mengelola poin/tier, dan mencatat log
    public function store(Request $request)
    {
        $request->validate([
            'customer_id'              => 'nullable|exists:customers,id',
            'items'                    => 'required|array|min:1',
            'items.*.product_id'       => 'required|exists:products,id',
            'items.*.qty'              => 'required|integer|min:1',
            'items.*.final_unit_price' => 'required|numeric|min:0',
            'items.*.promo_redeem_points' => 'nullable|numeric|min:0',
            'items.*.promo_redeem_amount' => 'nullable|numeric|min:0',
            'cash_received'            => 'required|numeric|min:0',
            'redeem_points'            => 'nullable|numeric|min:0',
            'notes'                    => 'nullable|string|max:500',
            'under_hpp_admin_email'    => 'nullable|string|max:150',
            'under_hpp_admin_password' => 'nullable|string|max:150',
        ]);

        DB::beginTransaction();

        try {
            $customer = $request->customer_id ? Customer::lockForUpdate()->find($request->customer_id) : null;
            $storeWarehouse = Warehouse::where('is_store', true)->first();
            [$items, $subtotal] = $this->prepareItems($request->items, $storeWarehouse);
            $promoPointsRedeemed = collect($items)->sum('promo_redeem_points');
            $promoRedeemAmount = collect($items)->sum('promo_redeem_amount');
            $underHppItems = $this->itemsBelowHpp($items);
            $underHppAuthorizedBy = $this->authorizeUnderHppIfNeeded($request, $underHppItems);

            $discountPercent = 0;
            $discountAmount  = 0;

            if ($customer && $request->filled('discount_percent')) {
                $discountPercent = (float) $request->discount_percent;
                $discountAmount  = round($subtotal * ($discountPercent / 100), 2);
            }

            $totalBeforeRedeem = max(0, $subtotal - $discountAmount);
            [$manualPointsRedeemed, $manualPointRedeemAmount] = $this->calculateRedeem($customer, $totalBeforeRedeem, (float) $request->input('redeem_points', 0));

            $pointsRedeemed = $manualPointsRedeemed + $promoPointsRedeemed;
            $pointRedeemAmount = $manualPointRedeemAmount + $promoRedeemAmount;
            if ($customer && $pointsRedeemed > (float) $customer->point_balance) {
                throw new \RuntimeException('Saldo poin member tidak cukup untuk redeem poin dan promo poin.');
            }

            $totalPrice   = max(0, $totalBeforeRedeem - $manualPointRedeemAmount);
            $cashReceived = (float) $request->cash_received;
            $changeAmount = max(0, $cashReceived - $totalPrice);
            $pointsEarned = $customer ? $this->membershipService->calculatePoints($totalPrice) : 0;

            if ($cashReceived < $totalPrice) {
                DB::rollBack();
                return back()->withInput()->with('error', 'Uang diterima kurang dari total belanja.');
            }

            $transaction = Transaction::create([
                'transaction_number' => Transaction::generateTransactionNumber(),
                'cashier_id'         => auth()->id(),
                'customer_id'        => $request->customer_id,
                'customer_tier'      => $customer ? $customer->tier : null,
                'subtotal'           => $subtotal,
                'discount_percent'   => $discountPercent,
                'discount_amount'    => $discountAmount,
                'total_price'        => $totalPrice,
                'cash_received'      => $cashReceived,
                'change_amount'      => $changeAmount,
                'points_earned'      => $pointsEarned,
                'points_redeemed'    => $pointsRedeemed,
                'point_redeem_amount' => $pointRedeemAmount,
                'payment_status'     => 'paid',
                'notes'              => $request->notes,
                'transaction_date'   => now(),
            ]);

            $this->saveTransactionDetailsAndDeductStock($transaction, $items, $storeWarehouse);

            if (! empty($underHppItems)) {
                ActivityLog::record(
                    'UNDER_HPP_TRANSACTION',
                    "Transaksi {$transaction->transaction_number} memakai harga di bawah HPP: " . $this->formatUnderHppItems($underHppItems) .
                    ". Diotorisasi oleh " . ($underHppAuthorizedBy?->name ?? auth()->user()->name) . "."
                );
            }

            if ($customer) {
                $this->membershipService->applyAfterTransaction($transaction);
            }

            DB::commit();

            ActivityLog::record(
                'TRANSACTION',
                "Transaksi {$transaction->transaction_number} — Total: Rp " . number_format($totalPrice, 0, ',', '.') .
                ($pointsRedeemed > 0 ? " — Redeem {$pointsRedeemed} poin/Rp " . number_format($pointRedeemAmount, 0, ',', '.') : '')
            );

            return redirect()->route('kasir.receipt', $transaction->id)
                ->with('success', "Transaksi {$transaction->transaction_number} berhasil disimpan.");

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    // Menampilkan form edit/revisi transaksi yang sudah ada (hanya pemilik & transaksi terakhir member)
    public function edit(Transaction $transaction)
    {
        if (auth()->user()->role !== 'pemilik') {
            return redirect()->route('kasir.show', $transaction)->with('error', 'Akses ditolak. Fitur edit nota hanya bisa diakses oleh Pemilik Toko.');
        }

        if ($transaction->payment_status !== 'paid') {
            return redirect()->route('kasir.show', $transaction)->with('error', 'Transaksi yang sudah dihapus/dibatalkan tidak dapat diedit.');
        }

        if ($transaction->customer_id) {
            $latestTransaction = Transaction::where('customer_id', $transaction->customer_id)
                ->where('payment_status', 'paid')
                ->latest('transaction_date')
                ->latest('id')
                ->first();

            if ($latestTransaction && $latestTransaction->id !== $transaction->id) {
                return redirect()->route('kasir.show', $transaction)->with('error', 'Hanya transaksi terakhir dari member ini yang dapat diedit/direvisi untuk menjaga akurasi poin dan tier.');
            }
        }

        $transaction->load(['details.product', 'customer', 'cashier']);
        $products  = Product::where('is_active', true)->orderBy('product_name')->get();
        $customers = Customer::orderBy('full_name')->get();
        $rule = MembershipRule::getCurrent();

        return view('kasir.edit', compact('transaction', 'products', 'customers', 'rule'));
    }

    // Memperbarui (revisi) data transaksi, membalikkan efek stok/poin lama sebelum menerapkan yang baru
    public function update(Request $request, Transaction $transaction)
    {
        if (auth()->user()->role !== 'pemilik') {
            return redirect()->route('kasir.show', $transaction)->with('error', 'Akses ditolak. Fitur edit nota hanya bisa dilakukan oleh Pemilik Toko.');
        }

        if ($transaction->payment_status !== 'paid') {
            return redirect()->route('kasir.show', $transaction)->with('error', 'Transaksi yang sudah dihapus/dibatalkan tidak dapat diedit.');
        }

        if ($transaction->customer_id) {
            $latestTransaction = Transaction::where('customer_id', $transaction->customer_id)
                ->where('payment_status', 'paid')
                ->latest('transaction_date')
                ->latest('id')
                ->first();

            if ($latestTransaction && $latestTransaction->id !== $transaction->id) {
                return redirect()->route('kasir.show', $transaction)->with('error', 'Hanya transaksi terakhir dari member ini yang dapat diedit/direvisi untuk menjaga akurasi poin dan tier.');
            }
        }

        $request->validate([
            'customer_id'              => 'nullable|exists:customers,id',
            'items'                    => 'required|array|min:1',
            'items.*.product_id'       => 'required|exists:products,id',
            'items.*.qty'              => 'required|integer|min:1',
            'items.*.final_unit_price' => 'required|numeric|min:0',
            'cash_received'            => 'required|numeric|min:0',
            'redeem_points'            => 'nullable|numeric|min:0',
            'transaction_date'         => 'required|date',
            'notes'                    => 'nullable|string|max:500',
            'under_hpp_admin_email'    => 'nullable|string|max:150',
            'under_hpp_admin_password' => 'nullable|string|max:150',
        ]);

        DB::beginTransaction();

        try {
            $transaction = Transaction::lockForUpdate()->with(['details.product', 'customer'])->findOrFail($transaction->id);
            $storeWarehouse = Warehouse::where('is_store', true)->first();

            $this->reverseTransactionEffects($transaction, $storeWarehouse, 'Revisi transaksi');

            [$items, $subtotal] = $this->prepareItems($request->items, $storeWarehouse);
            $underHppItems = $this->itemsBelowHpp($items);
            $underHppAuthorizedBy = $this->authorizeUnderHppIfNeeded($request, $underHppItems);

            $customer = $request->customer_id ? Customer::lockForUpdate()->find($request->customer_id) : null;
            $discountPercent = (float) ($request->discount_percent ?? $transaction->discount_percent ?? 0);
            $discountAmount  = $customer && $discountPercent > 0 ? round($subtotal * ($discountPercent / 100), 2) : 0;
            $totalBeforeRedeem = max(0, $subtotal - $discountAmount);
            [$manualPointsRedeemed, $manualPointRedeemAmount] = $this->calculateRedeem($customer, $totalBeforeRedeem, (float) $request->input('redeem_points', 0));
            $pointsRedeemed = $manualPointsRedeemed;
            $pointRedeemAmount = $manualPointRedeemAmount;
            if ($customer && $pointsRedeemed > (float) $customer->point_balance) {
                throw new \RuntimeException('Saldo poin member tidak cukup untuk redeem poin dan promo poin.');
            }
            $totalPrice      = max(0, $totalBeforeRedeem - $manualPointRedeemAmount);
            $cashReceived    = (float) $request->cash_received;

            if ($cashReceived < $totalPrice) {
                DB::rollBack();
                return back()->withInput()->with('error', 'Uang diterima kurang dari total belanja setelah revisi.');
            }

            $transaction->details()->delete();
            $transaction->update([
                'customer_id'        => $request->customer_id,
                'customer_tier'      => $customer ? $customer->tier : null,
                'subtotal'           => $subtotal,
                'discount_percent'   => $discountPercent,
                'discount_amount'    => $discountAmount,
                'total_price'        => $totalPrice,
                'cash_received'      => $cashReceived,
                'change_amount'      => max(0, $cashReceived - $totalPrice),
                'points_earned'      => $customer ? $this->membershipService->calculatePoints($totalPrice) : 0,
                'points_redeemed'    => $pointsRedeemed,
                'point_redeem_amount' => $pointRedeemAmount,
                'notes'              => $request->notes,
                'transaction_date'   => $request->transaction_date,
                'payment_status'     => 'paid',
            ]);

            $this->saveTransactionDetailsAndDeductStock($transaction, $items, $storeWarehouse);

            if (! empty($underHppItems)) {
                ActivityLog::record(
                    'UNDER_HPP_TRANSACTION_UPDATE',
                    "Revisi transaksi {$transaction->transaction_number} memakai harga di bawah HPP: " . $this->formatUnderHppItems($underHppItems) .
                    ". Diotorisasi oleh " . ($underHppAuthorizedBy?->name ?? auth()->user()->name) . "."
                );
            }

            if ($customer) {
                $this->membershipService->applyAfterTransaction($transaction->fresh());
            }

            DB::commit();

            ActivityLog::record('UPDATE_TRANSACTION', "Merevisi transaksi {$transaction->transaction_number} — Total baru: Rp " . number_format($totalPrice, 0, ',', '.') . ($pointsRedeemed > 0 ? " — Redeem {$pointsRedeemed} poin/Rp " . number_format($pointRedeemAmount, 0, ',', '.') : ''));

            return redirect()->route('kasir.show', $transaction)->with('success', 'Transaksi berhasil direvisi. Stok, poin, dan log sistem sudah disesuaikan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal merevisi transaksi: ' . $e->getMessage());
        }
    }

    // Membatalkan (void) transaksi secara logis dan mengembalikan stok, poin, dan tier
    public function destroy(Request $request, Transaction $transaction)
    {
        if (auth()->user()->role !== 'pemilik') {
            return back()->with('error', 'Penghapusan/Void transaksi hanya boleh dilakukan oleh Pemilik Toko. Jika kasir/admin salah input, laporkan ke Pemilik Toko.');
        }

        if ($transaction->payment_status !== 'paid') {
            return back()->with('error', 'Transaksi ini sudah dihapus/dibatalkan sebelumnya.');
        }

        if ($transaction->customer_id) {
            $latestTransaction = Transaction::where('customer_id', $transaction->customer_id)
                ->where('payment_status', 'paid')
                ->latest('transaction_date')
                ->latest('id')
                ->first();

            if ($latestTransaction && $latestTransaction->id !== $transaction->id) {
                return back()->with('error', 'Hanya transaksi terakhir dari member ini yang dapat divoid/dihapus untuk menjaga akurasi poin dan tier.');
            }
        }

        DB::beginTransaction();

        try {
            $transaction = Transaction::lockForUpdate()->with(['details.product', 'customer'])->findOrFail($transaction->id);
            $storeWarehouse = Warehouse::where('is_store', true)->first();

            $this->reverseTransactionEffects($transaction, $storeWarehouse, 'Penghapusan transaksi');

            $reason = $request->input('reason') ?: 'Transaksi dihapus dari riwayat karena kesalahan nota.';
            $transaction->update([
                'payment_status' => 'void',
                'notes' => trim(($transaction->notes ? $transaction->notes . "\n" : '') . '[VOID] ' . $reason),
            ]);

            DB::commit();

            ActivityLog::record('DELETE_TRANSACTION', "Menghapus/void transaksi {$transaction->transaction_number}. Poin dan stok dikoreksi. Alasan: {$reason}");

            return redirect()->route('kasir.history')->with('success', "Transaksi {$transaction->transaction_number} berhasil dihapus secara logis dan tetap tersimpan di riwayat/log.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }

    // Endpoint AJAX untuk mengecek harga akhir suatu produk (termasuk perhitungan diskon/promo member)
    public function priceCheck(Request $request)
    {
        $request->validate([
            'product_id'  => 'required|exists:products,id',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        $product  = Product::findOrFail($request->product_id);
        $customer = $request->customer_id ? Customer::find($request->customer_id) : null;

        $pricing = $this->membershipService->resolvePricing(
            $product->id,
            (float) $product->selling_price,
            $customer
        );

        return response()->json([
            'product_id'       => $product->id,
            'selling_price'    => (float) $product->selling_price,
            'hpp'              => (float) ($product->hpp ?? 0),
            'final_price'      => $pricing['final_price'],
            'discount_source'  => $pricing['discount_source'],
            'discount_percent' => $pricing['discount_percent'],
            'promo'            => $pricing['promo'] ? [
                'id'              => $pricing['promo']->id,
                'promo_name'      => $pricing['promo']->promo_name,
                'discount_amount' => (float) $pricing['promo']->discount_amount,
                'end_date'        => $pricing['promo']->end_date->format('d/m/Y'),
            ] : null,
        ]);
    }

    // Endpoint AJAX untuk mencatat log aktivitas terkait draf transaksi pada frontend kasir
    public function logDraftAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:Simpan Draft,Muat Draft,Hapus Draft',
            'detail' => 'required|string|max:500',
        ]);

        ActivityLog::record('DRAFT_TRANSACTION', "{$request->action}: {$request->detail}");

        return response()->json(['success' => true]);
    }

    // Menampilkan dan mencetak struk belanja (receipt) serta menyusun format pesan WhatsApp
    public function receipt(Transaction $transaction)
    {
        $transaction->load(['details.product', 'customer', 'cashier']);

        $waMessage = null;
        if ($transaction->customer && $transaction->customer->whatsapp_number) {
            $items = $transaction->details->map(function ($d) {
                return "- {$d->product->product_name} x{$d->qty} = Rp " . number_format($d->subtotal, 0, ',', '.');
            })->join("\n");
            
            $poinAkhir = $transaction->customer_point_balance ?? $transaction->customer->point_balance;
            $poinAwal = $poinAkhir + ($transaction->points_redeemed ?? 0) - ($transaction->points_earned ?? 0);

            $waMessage = urlencode(
                "Halo {$transaction->customer->full_name},\n" .
                "Terima kasih telah berbelanja di *UD. Tani Agung Ngawi*.\n\n" .
                "No. Transaksi : {$transaction->transaction_number}\n" .
                "Tanggal       : {$transaction->transaction_date->format('d/m/Y H:i')}\n\n" .
                "*Detail Belanja:*\n{$items}\n\n" .
                "Subtotal  : Rp " . number_format($transaction->subtotal, 0, ',', '.') . "\n" .
                ($transaction->discount_amount > 0
                    ? "Diskon ({$transaction->discount_percent}%) : -Rp " . number_format($transaction->discount_amount, 0, ',', '.') . "\n"
                    : "") .
                ((float) ($transaction->point_redeem_amount ?? 0) > 0
                    ? "Redeem Poin ({$transaction->points_redeemed} poin) : -Rp " . number_format($transaction->point_redeem_amount, 0, ',', '.') . "\n"
                    : "") .
                "*Total Bayar : Rp " . number_format($transaction->total_price, 0, ',', '.') . "*\n" .
                "Kembalian : Rp " . number_format($transaction->change_amount, 0, ',', '.') . "\n\n" .
                "Saldo Sebelumnya : " . number_format($poinAwal, 0, ',', '.') . " poin\n" .
                ((float) ($transaction->points_redeemed ?? 0) > 0
                    ? "Poin diredeem    : -{$transaction->points_redeemed} poin\n"
                    : "") .
                ($transaction->points_earned > 0
                    ? "Poin didapat     : +{$transaction->points_earned} poin\n"
                    : "") .
                "Saldo Akhir      : " . number_format($poinAkhir, 0, ',', '.') . " poin\n" .
                "Tier             : " . ucfirst($transaction->customer_tier ?? $transaction->customer->tier) . "\n\n" .
                ($transaction->notes ? "*Catatan:*\n{$transaction->notes}\n\n" : "") .
                "Salam hangat,\nUD. Tani Agung Ngawi"
            );
        }

        return view('kasir.receipt', compact('transaction', 'waMessage'));
    }
    // Menampilkan riwayat semua transaksi dengan fitur pencarian, filter tanggal/status, dan pengurutan
    public function index(Request $request)
    {
        $query = Transaction::with(['customer', 'cashier']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($c) => $c->where('full_name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }
        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }
        if ($request->filled('cashier_id')) {
            $query->where('cashier_id', $request->cashier_id);
        }

        $perPage = in_array((int) request('per_page'), [10,15,20,50,100], true) ? (int) request('per_page') : 20;

        $sortBy = request('sort_by', 'transaction_date');
        $sortDir = request('sort_dir', 'desc');
        $allowedSorts = ['transaction_number', 'transaction_date', 'subtotal', 'discount_amount', 'point_redeem_amount', 'total_price', 'payment_status', 'id', 'customer_name', 'cashier_name'];

        if ($sortBy === 'customer_name') {
            $query->orderBy(Customer::select('full_name')->whereColumn('customers.id', 'transactions.customer_id')->limit(1), $sortDir === 'asc' ? 'asc' : 'desc');
        } elseif ($sortBy === 'cashier_name') {
            $query->orderBy(User::select('name')->whereColumn('users.id', 'transactions.cashier_id')->limit(1), $sortDir === 'asc' ? 'asc' : 'desc');
        } elseif (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest('transaction_date');
        }

        $transactions = $query->paginate($perPage)->withQueryString();

        return view('kasir.history', compact('transactions'));
    }

    // Menampilkan detail lengkap dari suatu transaksi
    public function show(Transaction $transaction)
    {
        $transaction->load(['details.product', 'customer', 'cashier']);

        $backUrl = request('back_url');
        return view('kasir.show', compact('transaction', 'backUrl'));
    }

    private function prepareItems(array $requestItems, ?Warehouse $storeWarehouse): array
    {
        $items = [];
        $subtotal = 0;

        foreach ($requestItems as $item) {
            $product = Product::lockForUpdate()->findOrFail($item['product_id']);
            $qty = (int) $item['qty'];
            // Stok sistem boleh menjadi minus karena ada kondisi stok fisik sudah diambil customer
            // tetapi belum sinkron di sistem. Selisih ini tetap terlihat untuk koreksi stock opname.
            $finalPrice = (float) $item['final_unit_price'];
            if ($finalPrice < 0) {
                throw new \RuntimeException("Harga {$product->product_name} tidak valid.");
            }
            $lineTotal = $finalPrice * $qty;
            $subtotal += $lineTotal;

            $items[] = [
                'product'          => $product,
                'qty'              => $qty,
                'unit_price'       => (float) $product->selling_price,
                'hpp'              => (float) ($product->hpp ?? 0),
                'final_unit_price' => $finalPrice,
                'subtotal'         => $lineTotal,
            ];
        }

        return [$items, $subtotal];
    }


    private function itemsBelowHpp(array $items): array
    {
        return collect($items)
            ->filter(function (array $item) {
                $hpp = (float) ($item['hpp'] ?? 0);
                return $hpp > 0 && (float) $item['final_unit_price'] < $hpp;
            })
            ->map(function (array $item) {
                return [
                    'product_name' => $item['product']->product_name,
                    'hpp' => (float) $item['hpp'],
                    'final_unit_price' => (float) $item['final_unit_price'],
                ];
            })
            ->values()
            ->all();
    }

    private function authorizeUnderHppIfNeeded(Request $request, array $underHppItems): ?User
    {
        if (empty($underHppItems)) {
            return null;
        }

        $currentUser = auth()->user();
        if ($currentUser && in_array($currentUser->role, ['pemilik', 'admin'], true)) {
            return $currentUser;
        }

        $login = trim((string) $request->input('under_hpp_admin_email', ''));
        $password = (string) $request->input('under_hpp_admin_password', '');

        if ($login === '' || $password === '') {
            throw new \RuntimeException('Harga jual di bawah HPP membutuhkan otorisasi admin/pemilik. Isi username/email dan password admin terlebih dahulu.');
        }

        $admin = User::where('is_active', true)
            ->whereIn('role', ['admin', 'pemilik'])
            ->where(function ($q) use ($login) {
                $q->where('email', $login)
                  ->orWhere('username', $login);
            })
            ->first();

        if (! $admin || ! Hash::check($password, $admin->password)) {
            throw new \RuntimeException('Otorisasi admin/pemilik tidak valid. Periksa username/email dan password admin.');
        }

        return $admin;
    }

    private function formatUnderHppItems(array $items): string
    {
        return collect($items)->map(function (array $item) {
            return $item['product_name'] . ' (HPP Rp ' . number_format($item['hpp'], 0, ',', '.') . ', harga jual Rp ' . number_format($item['final_unit_price'], 0, ',', '.') . ')';
        })->join('; ');
    }


    private function calculateRedeem(?Customer $customer, float $totalBeforeRedeem, float $requestedPoints): array
    {
        $requestedPoints = floor($requestedPoints);

        if ($requestedPoints <= 0) {
            return [0, 0];
        }

        $rule = MembershipRule::getCurrent();
        $redeemMultiple = (int) ($rule->redeem_multiple ?? 100);

        if (fmod($requestedPoints, $redeemMultiple) != 0) {
            throw new \RuntimeException("Redeem poin hanya bisa dilakukan dalam kelipatan {$redeemMultiple} poin.");
        }

        if (! $customer) {
            throw new \RuntimeException('Redeem poin hanya bisa digunakan untuk transaksi member.');
        }

        $pointValue = (float) ($rule->redeem_point_value ?? 0);
        $minimumPoints = (float) ($rule->minimum_redeem_points ?? 0);
        $maxRedeemPercent = (float) ($rule->max_redeem_percent ?? 100);
        $balance = (float) $customer->point_balance;

        if ($pointValue <= 0) {
            throw new \RuntimeException('Nilai rupiah per poin belum diatur. Silakan cek Aturan Membership.');
        }

        if ($requestedPoints > $balance) {
            throw new \RuntimeException('Saldo poin member tidak mencukupi. Saldo tersedia: ' . number_format($balance, 0, ',', '.') . ' poin.');
        }

        if ($minimumPoints > 0 && $requestedPoints < $minimumPoints) {
            throw new \RuntimeException('Minimal redeem adalah ' . number_format($minimumPoints, 0, ',', '.') . ' poin.');
        }

        $maxRedeemAmountByPercent = $totalBeforeRedeem * ($maxRedeemPercent / 100);
        $maxRedeemAmount = min($totalBeforeRedeem, $maxRedeemAmountByPercent);
        $maxRedeemPoints = floor($maxRedeemAmount / $pointValue);

        if ($maxRedeemPoints <= 0 || $requestedPoints > $maxRedeemPoints) {
            throw new \RuntimeException('Jumlah poin melebihi batas redeem transaksi ini. Maksimal: ' . number_format(max(0, $maxRedeemPoints), 0, ',', '.') . ' poin.');
        }

        $redeemAmount = $requestedPoints * $pointValue;

        return [$requestedPoints, min($redeemAmount, $totalBeforeRedeem)];
    }

    private function availableStoreStock(Product $product, ?Warehouse $storeWarehouse): int
    {
        if (! $storeWarehouse) {
            return (int) $product->stock;
        }

        $stockRecord = WarehouseStock::lockForUpdate()->where([
            ['warehouse_id', '=', $storeWarehouse->id],
            ['product_id', '=', $product->id],
        ])->first();

        return $stockRecord ? (int) $stockRecord->stock : (int) $product->stock;
    }

    private function saveTransactionDetailsAndDeductStock(Transaction $transaction, array $items, ?Warehouse $storeWarehouse): void
    {
        foreach ($items as $item) {
            TransactionDetail::create([
                'transaction_id'   => $transaction->id,
                'product_id'       => $item['product']->id,
                'qty'              => $item['qty'],
                'unit_price'       => $item['unit_price'],
                'final_unit_price' => $item['final_unit_price'],
                'subtotal'         => $item['subtotal'],
            ]);

            $this->moveStoreStock($item['product'], $storeWarehouse, -$item['qty']);
        }
    }

    private function reverseTransactionEffects(Transaction $transaction, ?Warehouse $storeWarehouse, string $reason): void
    {
        foreach ($transaction->details as $detail) {
            if ($detail->product) {
                $this->moveStoreStock($detail->product, $storeWarehouse, (int) $detail->qty);
            }
        }

        if ($transaction->customer) {
            $customer = Customer::lockForUpdate()->find($transaction->customer_id);
            $oldTier = $customer->tier;
            $oldTotalAccumulation = (float) $customer->total_accumulation;

            $customer->total_accumulation = max(0, (float) $customer->total_accumulation - (float) $transaction->total_price);
            $customer->point_balance = max(0, (float) $customer->point_balance - (float) $transaction->points_earned + (float) ($transaction->points_redeemed ?? 0));
            $customer->tier = $this->membershipService->evaluateTier((float) $customer->total_accumulation);
            $customer->save();

            if ($oldTier !== $customer->tier) {
                CustomerTierHistory::create([
                    'customer_id' => $customer->id,
                    'transaction_id' => $transaction->id,
                    'changed_by' => auth()->id(),
                    'old_tier' => $oldTier,
                    'new_tier' => $customer->tier,
                    'old_total_accumulation' => $oldTotalAccumulation,
                    'new_total_accumulation' => (float) $customer->total_accumulation,
                    'source' => str_contains(strtolower($reason), 'penghapusan') ? 'void_transaction' : 'transaction_revision',
                    'notes' => "Tier berubah karena {$reason} {$transaction->transaction_number}.",
                ]);
            }

            if ((float) $transaction->points_earned > 0) {
                PointHistory::create([
                    'customer_id'    => $customer->id,
                    'transaction_id' => $transaction->id,
                    'points_earned'  => -1 * (float) $transaction->points_earned,
                    'description'    => "Pengurangan poin masuk karena {$reason} {$transaction->transaction_number}",
                ]);
            }

            if ((float) ($transaction->points_redeemed ?? 0) > 0) {
                PointHistory::create([
                    'customer_id'    => $customer->id,
                    'transaction_id' => $transaction->id,
                    'points_earned'  => (float) $transaction->points_redeemed,
                    'description'    => "Pengembalian poin redeem karena {$reason} {$transaction->transaction_number}",
                ]);
            }
        }
    }

    private function moveStoreStock(Product $product, ?Warehouse $storeWarehouse, int $qtyChange): void
    {
        if ($storeWarehouse) {
            $stockRecord = WarehouseStock::lockForUpdate()->firstOrNew([
                'warehouse_id' => $storeWarehouse->id,
                'product_id'   => $product->id,
            ], [
                'stock'         => (int) $product->stock,
                'minimum_stock' => (int) $product->minimum_stock,
            ]);

            $stockRecord->stock = (int) $stockRecord->stock + $qtyChange;
            $stockRecord->save();
        }

        $product->stock = (int) $product->stock + $qtyChange;
        $product->save();
    }
}
