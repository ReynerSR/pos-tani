<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\HppCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function __construct(private HppCalculatorService $hppCalculator)
    {
    }

    // Menampilkan daftar pembelian/restok dengan fitur pencarian, filter tanggal, status, dan pengurutan
    public function index(Request $request)
    {
        $query = Purchase::with(['supplier', 'user', 'warehouse']);

        if ($request->filled('search')) {
            $query->where('invoice_number', 'like', "%{$request->search}%")
                ->orWhereHas('supplier', fn($q) => $q->where('name', 'like', "%{$request->search}%"));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('purchase_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('purchase_date', '<=', $request->date_to);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = in_array((int) $request->get('per_page'), [10,15,20,50,100], true) ? (int) $request->get('per_page') : 20;

        $sortBy = request('sort_by', 'purchase_date');
        $sortDir = request('sort_dir', 'desc');
        $allowedSorts = ['invoice_number', 'purchase_date', 'total_price', 'status', 'supplier_name', 'warehouse_name', 'user_name', 'id'];

        if ($sortBy === 'supplier_name') {
            $query->orderBy(Supplier::select('name')->whereColumn('suppliers.id', 'purchases.supplier_id')->limit(1), $sortDir === 'asc' ? 'asc' : 'desc');
        } elseif ($sortBy === 'warehouse_name') {
            $query->orderBy(Warehouse::select('name')->whereColumn('warehouses.id', 'purchases.warehouse_id')->limit(1), $sortDir === 'asc' ? 'asc' : 'desc');
        } elseif ($sortBy === 'user_name') {
            $query->orderBy(\App\Models\User::select('name')->whereColumn('users.id', 'purchases.user_id')->limit(1), $sortDir === 'asc' ? 'asc' : 'desc');
        } elseif (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest('purchase_date');
        }

        $purchases = $query->paginate($perPage)->withQueryString();

        return view('purchases.index', compact('purchases'));
    }

    // Menampilkan form tambah daftar pembelian baru
    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products  = Product::where('is_active', true)->orderBy('product_name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        return view('purchases.create', compact('suppliers', 'products', 'warehouses'));
    }

    // Menyimpan data pembelian baru ke database (sebagai draft jika admin, atau approved jika pemilik)
    public function store(Request $request)
    {
        $isOwner = auth()->user()->role === 'pemilik';

        $request->validate([
            'invoice_number' => 'required|string|max:50|unique:purchases,invoice_number',
            'supplier_id'    => 'required|exists:suppliers,id',
            'warehouse_id'   => 'required|exists:warehouses,id',
            'purchase_date'  => 'required|date',
            'notes'          => 'nullable|string',
            'items'          => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:products,id',
            'items.*.qty'            => 'required|integer|min:1',
            'items.*.unit_buy_price' => [$isOwner ? 'required' : 'nullable', 'numeric', 'min:0'],
        ]);

        DB::beginTransaction();

        try {
            $totalPrice = 0;
            $lines = [];
            foreach ($request->items as $item) {
                $rawPrice = $item['unit_buy_price'] ?? 0;
                $cleanPrice = str_replace(['.', ','], '', (string)$rawPrice);
                $unitPrice = $isOwner ? (float) $cleanPrice : 0;
                $subtotal = $unitPrice * (int) $item['qty'];
                $totalPrice += $subtotal;
                $lines[] = array_merge($item, ['unit_buy_price' => $unitPrice, 'subtotal' => $subtotal]);
            }

            $purchase = Purchase::create([
                'invoice_number' => $request->invoice_number,
                'supplier_id'    => $request->supplier_id,
                'user_id'        => auth()->id(),
                'warehouse_id'   => $request->warehouse_id,
                'purchase_date'  => $request->purchase_date,
                'total_price'    => $totalPrice,
                'status'         => $isOwner ? 'approved' : 'draft',
                'approved_by'    => $isOwner ? auth()->id() : null,
                'approved_at'    => $isOwner ? now() : null,
                'notes'          => $request->notes,
            ]);

            foreach ($lines as $line) {
                $product = Product::lockForUpdate()->find($line['product_id']);
                $newHpp = $isOwner
                    ? $this->hppCalculator->calculateWeightedAverage($product, (int) $line['qty'], (float) $line['unit_buy_price'])
                    : (float) $product->hpp;

                PurchaseDetail::create([
                    'purchase_id'    => $purchase->id,
                    'product_id'     => $product->id,
                    'qty'            => (int) $line['qty'],
                    'unit_buy_price' => (float) $line['unit_buy_price'],
                    'new_hpp'        => $newHpp,
                    'subtotal'       => $line['subtotal'],
                ]);
            }

            // Terapkan penambahan stok dan perubahan HPP jika pembelian langsung disetujui (oleh pemilik)
            if ($isOwner) {
                $this->applyApprovedPurchaseStock($purchase);
            }

            DB::commit();

            ActivityLog::record(
                $isOwner ? 'PURCHASE' : 'PURCHASE_DRAFT',
                $isOwner
                    ? "Input pembelian {$purchase->invoice_number} langsung approved — Total: Rp " . number_format($totalPrice, 0, ',', '.')
                    : "Admin input draft restok {$purchase->invoice_number}. Harga beli disembunyikan sampai owner approve."
            );

            return redirect()->route('purchases.show', $purchase)
                ->with('success', $isOwner ? "Pembelian {$purchase->invoice_number} berhasil disimpan dan stok diperbarui." : "Draft restok {$purchase->invoice_number} berhasil disimpan. Stok belum bertambah sampai owner mengisi harga dan approve.");

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan pembelian: ' . $e->getMessage());
        }
    }

    // Menyetujui draft pembelian (hanya untuk pemilik), memperbarui stok dan HPP
    public function approve(Request $request, Purchase $purchase)
    {
        if (auth()->user()->role !== 'pemilik') {
            return back()->with('error', 'Approve restok hanya boleh dilakukan oleh owner/pemilik.');
        }
        if ($purchase->status === 'approved') {
            return back()->with('error', 'Pembelian ini sudah approved.');
        }

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.unit_buy_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $purchase = Purchase::lockForUpdate()->with('details.product')->findOrFail($purchase->id);
            $total = 0;
            foreach ($purchase->details as $detail) {
                $rawInput = $request->input("items.{$detail->id}.unit_buy_price", 0);
                // Hapus koma dan titik untuk menangani format mata uang Indonesia
                $cleanInput = str_replace(['.', ','], '', (string)$rawInput);
                $unitPrice = (float) $cleanInput;
                $product = Product::lockForUpdate()->find($detail->product_id);
                $newHpp = $this->hppCalculator->calculateWeightedAverage($product, (int) $detail->qty, $unitPrice);
                $subtotal = $unitPrice * (int) $detail->qty;
                $detail->update([
                    'unit_buy_price' => $unitPrice,
                    'new_hpp' => $newHpp,
                    'subtotal' => $subtotal,
                ]);
                $total += $subtotal;
            }

            $purchase->update([
                'total_price' => $total,
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            $this->applyApprovedPurchaseStock($purchase->fresh('details.product'));
            DB::commit();

            ActivityLog::record('APPROVE_PURCHASE_DRAFT', "Owner approve draft restok {$purchase->invoice_number}. Stok dan HPP diperbarui.");
            return redirect()->route('purchases.show', $purchase)->with('success', 'Draft restok berhasil diapprove. Stok dan HPP sudah diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal approve restok: ' . $e->getMessage());
        }
    }

    // Menambahkan stok produk ke gudang dan memperbarui HPP berdasarkan pembelian yang disetujui
    private function applyApprovedPurchaseStock(Purchase $purchase): void
    {
        $purchase->loadMissing('details.product', 'warehouse');
        $warehouse = Warehouse::lockForUpdate()->find($purchase->warehouse_id);

        foreach ($purchase->details as $detail) {
            $product = Product::lockForUpdate()->find($detail->product_id);

            $stockRecord = WarehouseStock::lockForUpdate()->firstOrNew([
                'warehouse_id' => $warehouse->id,
                'product_id'   => $product->id,
            ], [
                'stock'         => 0,
                'minimum_stock' => $product->minimum_stock,
            ]);
            $stockRecord->stock += (int) $detail->qty;
            $stockRecord->save();

            if ($warehouse->is_store) {
                $product->stock += (int) $detail->qty;
            }
            $product->hpp = (float) $detail->new_hpp;
            $product->save();
        }
    }

    // Membalikkan (mengurangi) stok dan mengembalikan nilai HPP seperti sebelum pembelian ini (digunakan saat batal/edit/hapus)
    private function reversePurchaseStock(Purchase $purchase): void
    {
        $purchase->loadMissing('details.product', 'warehouse');
        $warehouse = Warehouse::lockForUpdate()->find($purchase->warehouse_id);

        foreach ($purchase->details as $detail) {
            $product = Product::lockForUpdate()->find($detail->product_id);
            $qtyBought = (int) $detail->qty;
            $buyPrice = (float) $detail->unit_buy_price;

            $currentStock = max(0, $product->total_stock);
            $currentHpp = (float) $product->hpp;

            $newTotalStock = $currentStock - $qtyBought;

            // Mengembalikan nilai HPP menggunakan perhitungan rata-rata tertimbang secara matematis
            if ($newTotalStock > 0) {
                $totalValue = ($currentStock * $currentHpp) - ($qtyBought * $buyPrice);
                $newHpp = max(0, round($totalValue / $newTotalStock, 2));
            } else {
                $newHpp = $currentHpp;
            }

            $product->hpp = $newHpp;

            $stockRecord = WarehouseStock::lockForUpdate()->where('warehouse_id', $warehouse->id)->where('product_id', $product->id)->first();
            if ($stockRecord) {
                $stockRecord->stock -= $qtyBought;
                $stockRecord->save();
            }

            if ($warehouse->is_store) {
                $product->stock -= $qtyBought;
            }
            $product->save();
        }
    }

    // Menampilkan form edit pembelian (hanya untuk pemilik)
    public function edit(Purchase $purchase)
    {
        if (auth()->user()->role !== 'pemilik') {
            return back()->with('error', 'Hanya pemilik yang dapat mengedit daftar pembelian.');
        }

        $purchase->load(['details.product', 'supplier', 'warehouse']);
        $suppliers = Supplier::orderBy('name')->get();
        $products  = Product::where('is_active', true)->orderBy('product_name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('purchases.edit', compact('purchase', 'suppliers', 'products', 'warehouses'));
    }

    // Memperbarui data pembelian
    public function update(Request $request, Purchase $purchase)
    {
        if (auth()->user()->role !== 'pemilik') {
            return back()->with('error', 'Hanya pemilik yang dapat mengedit daftar pembelian.');
        }

        $request->validate([
            'invoice_number' => 'required|string|max:50|unique:purchases,invoice_number,' . $purchase->id,
            'supplier_id'    => 'required|exists:suppliers,id',
            'warehouse_id'   => 'required|exists:warehouses,id',
            'purchase_date'  => 'required|date',
            'notes'          => 'nullable|string',
            'items'          => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:products,id',
            'items.*.qty'            => 'required|integer|min:1',
            'items.*.unit_buy_price' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $wasApproved = $purchase->status === 'approved';

            if ($wasApproved) {
                // Tarik kembali stok dan efek HPP sebelum menghapus detail yang lama
                $this->reversePurchaseStock($purchase);
            }

            // Hapus detail pembelian yang lama
            $purchase->details()->delete();

            $totalPrice = 0;
            $lines = [];
            foreach ($request->items as $item) {
                $rawPrice = $item['unit_buy_price'] ?? 0;
                $cleanPrice = str_replace(['.', ','], '', (string)$rawPrice);
                $unitPrice = (float) $cleanPrice;
                $subtotal = $unitPrice * (int) $item['qty'];
                $totalPrice += $subtotal;
                $lines[] = array_merge($item, ['unit_buy_price' => $unitPrice, 'subtotal' => $subtotal]);
            }

            $purchase->update([
                'invoice_number' => $request->invoice_number,
                'supplier_id'    => $request->supplier_id,
                'warehouse_id'   => $request->warehouse_id,
                'purchase_date'  => $request->purchase_date,
                'total_price'    => $totalPrice,
                'notes'          => $request->notes,
            ]);

            foreach ($lines as $line) {
                $product = Product::lockForUpdate()->find($line['product_id']);
                
                // Jika pembelian sudah disetujui, hitung HPP aktual berdasarkan rata-rata saat ini.
                // Jika belum disetujui, ini hanya simulasi untuk ditampilkan.
                $newHpp = $this->hppCalculator->calculateWeightedAverage($product, (int) $line['qty'], (float) $line['unit_buy_price']);

                PurchaseDetail::create([
                    'purchase_id'    => $purchase->id,
                    'product_id'     => $product->id,
                    'qty'            => (int) $line['qty'],
                    'unit_buy_price' => (float) $line['unit_buy_price'],
                    'new_hpp'        => $newHpp,
                    'subtotal'       => $line['subtotal'],
                ]);
            }

            if ($wasApproved) {
                $this->applyApprovedPurchaseStock($purchase->fresh('details.product', 'warehouse'));
            }

            DB::commit();

            $statusText = $wasApproved ? 'approved' : 'draft';
            ActivityLog::record('UPDATE_PURCHASE', "Owner mengedit daftar pembelian {$statusText} {$purchase->invoice_number}. Stok dan HPP telah disesuaikan.");

            return redirect()->route('purchases.show', $purchase)->with('success', "Daftar pembelian {$purchase->invoice_number} berhasil diperbarui.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal mengedit pembelian: ' . $e->getMessage());
        }
    }

    // Menampilkan detail pembelian
    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'user', 'warehouse', 'details.product', 'approver']);

        return view('purchases.show', compact('purchase'));
    }

    // Menghapus data pembelian dari database (beserta detailnya)
    public function destroy(Purchase $purchase)
    {
        if (auth()->user()->role !== 'pemilik') {
            return back()->with('error', 'Hanya pemilik yang dapat menghapus daftar pembelian.');
        }

        DB::beginTransaction();
        try {
            if ($purchase->status === 'approved') {
                $this->reversePurchaseStock($purchase);
            }

            $purchase->details()->delete();
            $purchase->delete();

            DB::commit();

            ActivityLog::record('DELETE_PURCHASE', "Owner menghapus daftar pembelian {$purchase->invoice_number}. Stok dan HPP telah direvert.");

            return redirect()->route('purchases.index')->with('success', "Daftar pembelian {$purchase->invoice_number} berhasil dihapus.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus pembelian: ' . $e->getMessage());
        }
    }
}
