<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $query = StockAdjustment::with(['product', 'user', 'warehouse']);

        if ($request->filled('date_from')) {
            $query->whereDate('adjustment_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('adjustment_date', '<=', $request->date_to);
        }
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->filled('search')) {
            $query->whereHas('product', fn($q) => $q->where('product_name', 'like', "%{$request->search}%")
                ->orWhere('product_code', 'like', "%{$request->search}%"));
        }

        $sortBy = request('sort_by', 'created_at');
        $sortDir = request('sort_dir', 'desc');
        $allowedSorts = ['adjustment_date', 'stock_before', 'stock_after', 'difference', 'created_at', 'product_name', 'warehouse_name', 'user_name', 'id'];

        if ($sortBy === 'product_name') {
            $query->orderBy(Product::select('product_name')->whereColumn('products.id', 'stock_adjustments.product_id')->limit(1), $sortDir === 'asc' ? 'asc' : 'desc');
        } elseif ($sortBy === 'warehouse_name') {
            $query->orderBy(Warehouse::select('name')->whereColumn('warehouses.id', 'stock_adjustments.warehouse_id')->limit(1), $sortDir === 'asc' ? 'asc' : 'desc');
        } elseif ($sortBy === 'user_name') {
            $query->orderBy(\App\Models\User::select('name')->whereColumn('users.id', 'stock_adjustments.user_id')->limit(1), $sortDir === 'asc' ? 'asc' : 'desc');
        } elseif (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderByDesc('created_at');
        }

        $perPage = in_array((int) $request->get('per_page'), [10,15,20,50,100], true) ? (int) $request->get('per_page') : 20;
        $adjustments = $query->paginate($perPage)->withQueryString();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('stock.index', compact('adjustments', 'warehouses'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->with('warehouseStocks')->orderBy('product_name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('stock.create', compact('products', 'warehouses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'warehouse_id'           => 'required|exists:warehouses,id',
            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:products,id',
            'items.*.stock_actual'   => 'required|integer|min:0',
            'items.*.notes'          => 'nullable|string|max:200',
            'adjustment_date'        => 'required|date',
        ]);

        DB::beginTransaction();

        try {
            $count = 0;
            $warehouse = Warehouse::lockForUpdate()->findOrFail($request->warehouse_id);

            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                $stockRecord = WarehouseStock::lockForUpdate()->firstOrNew([
                    'warehouse_id' => $warehouse->id,
                    'product_id'   => $product->id,
                ], [
                    'stock'         => 0,
                    'minimum_stock' => $product->minimum_stock,
                ]);

                $stockBefore = (int) $stockRecord->stock;
                $stockAfter  = (int) $item['stock_actual'];
                $difference  = $stockAfter - $stockBefore;

                if ($difference === 0) {
                    continue;
                }

                StockAdjustment::create([
                    'product_id'      => $product->id,
                    'user_id'         => auth()->id(),
                    'warehouse_id'    => $warehouse->id,
                    'stock_before'    => $stockBefore,
                    'stock_after'     => $stockAfter,
                    'difference'      => $difference,
                    'notes'           => $item['notes'] ?? null,
                    'adjustment_date' => $request->adjustment_date,
                ]);

                $stockRecord->stock = $stockAfter;
                $stockRecord->save();

                if ($warehouse->is_store) {
                    $product->stock = $stockAfter;
                    $product->save();
                }

                $count++;
            }

            DB::commit();

            ActivityLog::record('STOCK_OPNAME', "Stock opname {$warehouse->code} - {$warehouse->name}: {$count} produk disesuaikan.");

            return redirect()->route('stock.index')
                ->with('success', "Stock opname berhasil. {$count} produk disesuaikan di {$warehouse->name}.");

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal melakukan stock opname: ' . $e->getMessage());
        }
    }
}
