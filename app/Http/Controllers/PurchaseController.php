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

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products  = Product::where('is_active', true)->orderBy('product_name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        return view('purchases.create', compact('suppliers', 'products', 'warehouses'));
    }

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
                $unitPrice = $isOwner ? (float) ($item['unit_buy_price'] ?? 0) : 0;
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
                $unitPrice = (float) ($request->input("items.{$detail->id}.unit_buy_price", 0));
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

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'user', 'warehouse', 'details.product', 'approver']);

        return view('purchases.show', compact('purchase'));
    }
}
