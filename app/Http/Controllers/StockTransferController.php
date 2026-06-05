<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\StockTransferDetail;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockTransferController extends Controller
{
    public function index(Request $request)
    {
        $query = StockTransfer::with(['fromWarehouse', 'toWarehouse', 'user', 'details.product']);

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where('transfer_number', 'like', "%{$term}%")
                ->orWhereHas('fromWarehouse', fn($q) => $q->where('name', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"))
                ->orWhereHas('toWarehouse', fn($q) => $q->where('name', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"));
        }

        $perPage = in_array((int) $request->get('per_page'), [10,15,20,50,100], true) ? (int) $request->get('per_page') : 20;
        $transfers = $query->latest('transfer_date')->paginate($perPage)->withQueryString();
        return view('stock_transfers.index', compact('transfers'));
    }

    public function create()
    {
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)
            ->with('warehouseStocks.warehouse')
            ->orderBy('product_name')
            ->get();

        return view('stock_transfers.create', compact('warehouses', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_warehouse_id'  => 'required|exists:warehouses,id|different:to_warehouse_id',
            'to_warehouse_id'    => 'required|exists:warehouses,id',
            'transfer_date'      => 'required|date',
            'notes'              => 'nullable|string',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty'        => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $fromWarehouse = Warehouse::lockForUpdate()->findOrFail($request->from_warehouse_id);
            $toWarehouse = Warehouse::lockForUpdate()->findOrFail($request->to_warehouse_id);

            $datePart = date('Ymd', strtotime($request->transfer_date));
            $lastId = (int) (StockTransfer::max('id') ?? 0) + 1;
            $transferNumber = sprintf('TRF-%s-%04d', $datePart, $lastId);

            $transfer = StockTransfer::create([
                'transfer_number'   => $transferNumber,
                'from_warehouse_id' => $fromWarehouse->id,
                'to_warehouse_id'   => $toWarehouse->id,
                'user_id'           => auth()->id(),
                'status'            => 'completed',
                'notes'             => $request->notes,
                'transfer_date'     => $request->transfer_date,
            ]);

            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $qty = (int) $item['qty'];

                $sourceStock = WarehouseStock::lockForUpdate()->firstOrNew([
                    'warehouse_id' => $fromWarehouse->id,
                    'product_id'   => $product->id,
                ], [
                    'stock'         => $fromWarehouse->is_store ? (int) $product->stock : 0,
                    'minimum_stock' => $product->minimum_stock,
                ]);

                if ((int) $sourceStock->stock < $qty) {
                    DB::rollBack();
                    return back()->withInput()->with('error', "Stok {$product->product_name} di {$fromWarehouse->name} tidak mencukupi. Sisa: {$sourceStock->stock} {$product->unit}.");
                }

                $sourceStock->stock = (int) $sourceStock->stock - $qty;
                $sourceStock->save();

                $destStock = WarehouseStock::lockForUpdate()->firstOrNew([
                    'warehouse_id' => $toWarehouse->id,
                    'product_id'   => $product->id,
                ], [
                    'stock'         => 0,
                    'minimum_stock' => $product->minimum_stock,
                ]);
                $destStock->stock = (int) $destStock->stock + $qty;
                $destStock->save();

                if ($fromWarehouse->is_store) {
                    $product->decrement('stock', $qty);
                }
                if ($toWarehouse->is_store) {
                    $product->increment('stock', $qty);
                }

                StockTransferDetail::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id'        => $product->id,
                    'qty'               => $qty,
                ]);
            }

            ActivityLog::record('STOCK_TRANSFER', "Transfer stok {$transferNumber} dari {$fromWarehouse->code} - {$fromWarehouse->name} ke {$toWarehouse->code} - {$toWarehouse->name}");

            DB::commit();
            return redirect()->route('stock-transfers.index')->with('success', 'Transfer stok berhasil disimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan transfer stok: ' . $e->getMessage());
        }
    }
}
