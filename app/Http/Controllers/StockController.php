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
    // Menampilkan daftar riwayat stock opname (penyesuaian stok) per tanggal dan gudang
    public function index(Request $request)
    {
        $query = StockAdjustment::with(['warehouse'])
            ->select(
                'adjustment_date', 
                'warehouse_id',
                DB::raw('count(id) as total_items'),
                DB::raw('sum(case when status="draft" then 1 else 0 end) as pending_items'),
                DB::raw('sum(case when status="approved" then 1 else 0 end) as approved_items')
            )
            ->groupBy('adjustment_date', 'warehouse_id');

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

        $query->orderByDesc('adjustment_date');

        $perPage = in_array((int) $request->get('per_page'), [10,15,20,50,100], true) ? (int) $request->get('per_page') : 20;
        $adjustments = $query->paginate($perPage)->withQueryString();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('stock.index', compact('adjustments', 'warehouses'));
    }

    // Menampilkan form untuk melakukan stock opname baru
    public function create()
    {
        $products = Product::where('is_active', true)->with('warehouseStocks')->orderBy('product_name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('stock.create', compact('products', 'warehouses'));
    }

    // Menyimpan data stock opname (langsung disetujui jika pemilik, atau draft jika bukan)
    public function store(Request $request)
    {
        $request->validate([
            'warehouse_id'           => 'required|exists:warehouses,id',
            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:products,id',
            'items.*.stock_actual'   => 'required|integer',
            'items.*.notes'          => 'nullable|string|max:200',
            'adjustment_date'        => 'required|date',
        ]);

        DB::beginTransaction();

        try {
            $count = 0;
            $warehouse = Warehouse::findOrFail($request->warehouse_id);
            $isOwner = auth()->user()->role === 'pemilik';

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);

                $stockRecord = WarehouseStock::firstOrNew([
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

                if (empty($item['notes'])) {
                    throw new \Exception("Keterangan wajib diisi untuk produk yang selisih: {$product->product_name}");
                }

                $status = $isOwner ? 'approved' : 'draft';

                // Hapus draft lama untuk produk dan gudang yang sama jika ada
                StockAdjustment::where('product_id', $product->id)
                    ->where('warehouse_id', $warehouse->id)
                    ->where('status', 'draft')
                    ->delete();

                StockAdjustment::create([
                    'product_id'      => $product->id,
                    'user_id'         => auth()->id(),
                    'warehouse_id'    => $warehouse->id,
                    'stock_before'    => $stockBefore,
                    'stock_after'     => $stockAfter,
                    'difference'      => $difference,
                    'notes'           => $item['notes'],
                    'adjustment_date' => $request->adjustment_date,
                    'status'          => $status,
                    'approved_by'     => $isOwner ? auth()->id() : null,
                    'approved_at'     => $isOwner ? now() : null,
                ]);

                if ($isOwner) {
                    // Update real stock
                    $stockRecord->stock = $stockAfter;
                    $stockRecord->save();

                    if ($warehouse->is_store) {
                        $product->stock = $stockAfter;
                        $product->save();
                    }
                }

                $count++;
            }

            DB::commit();

            if ($isOwner) {
                ActivityLog::record('STOCK_OPNAME', "Stock opname {$warehouse->code} - {$warehouse->name} langsung disetujui: {$count} produk diperbarui.");
                return redirect()->route('stock.index')
                    ->with('success', "Stock opname berhasil disimpan dan stok otomatis diperbarui. ({$count} produk).");
            } else {
                ActivityLog::record('STOCK_OPNAME_DRAFT', "Draft Stock opname {$warehouse->code} - {$warehouse->name} disubmit: {$count} produk perlu persetujuan.");
                return redirect()->route('stock.index')
                    ->with('success', "Draft stock opname berhasil disimpan. {$count} produk menunggu persetujuan owner.");
            }

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal melakukan stock opname: ' . $e->getMessage());
        }
    }

    // Menampilkan detail penyesuaian stok untuk gudang dan tanggal tertentu
    public function show($date, $warehouse_id)
    {
        $warehouse = Warehouse::findOrFail($warehouse_id);
        
        $adjustments = StockAdjustment::with(['product', 'user', 'approver'])
            ->whereDate('adjustment_date', $date)
            ->where('warehouse_id', $warehouse_id)
            ->get();
            
        if ($adjustments->isEmpty()) {
            return redirect()->route('stock.index')->with('error', 'Data stock opname tidak ditemukan.');
        }
        
        $hasDraft = $adjustments->where('status', 'draft')->isNotEmpty();

        return view('stock.show', compact('adjustments', 'warehouse', 'date', 'hasDraft'));
    }

    // Menyetujui draft penyesuaian stok (hanya untuk pemilik)
    public function approve(Request $request, $date, $warehouse_id)
    {
        if (auth()->user()->role !== 'pemilik') {
            return back()->with('error', 'Hanya pemilik yang dapat menyetujui stock opname.');
        }

        $warehouse = Warehouse::findOrFail($warehouse_id);
        
        $request->validate([
            'items' => 'required|array',
            'items.*.stock_after' => 'nullable|integer',
            'items.*.notes' => 'nullable|string',
            'items.*.approve' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $count = 0;
            
            foreach ($request->items as $adj_id => $data) {
                if (empty($data['approve'])) {
                    continue; // Skip items that are not checked
                }
                
                $adj = StockAdjustment::where('status', 'draft')->lockForUpdate()->find($adj_id);
                if (!$adj) continue;

                $product = Product::lockForUpdate()->findOrFail($adj->product_id);
                $stockRecord = WarehouseStock::lockForUpdate()->firstOrNew([
                    'warehouse_id' => $warehouse->id,
                    'product_id'   => $product->id,
                ]);

                $newStockAfter = (int) ($data['stock_after'] ?? $adj->stock_after);
                $newDiff = $newStockAfter - $adj->stock_before;
                
                if ($newDiff !== 0 && empty($data['notes'])) {
                    throw new \Exception("Keterangan wajib diisi untuk {$product->product_name} jika ada selisih.");
                }

                // Update real stock
                $stockRecord->stock = $newStockAfter;
                $stockRecord->save();

                if ($warehouse->is_store) {
                    $product->stock = $newStockAfter;
                    $product->save();
                }

                // Update adjustment
                $adj->stock_after = $newStockAfter;
                $adj->difference = $newDiff;
                $adj->notes = $data['notes'] ?? $adj->notes;
                $adj->status = 'approved';
                $adj->approved_by = auth()->id();
                $adj->approved_at = now();
                $adj->save();

                $count++;
            }

            DB::commit();

            if ($count > 0) {
                ActivityLog::record('STOCK_OPNAME_APPROVE', "Stock opname {$warehouse->code} - {$warehouse->name} disetujui: {$count} produk diperbarui.");
                return redirect()->route('stock.index')->with('success', "{$count} penyesuaian stok telah disetujui dan diperbarui.");
            } else {
                return redirect()->route('stock.index')->with('info', "Tidak ada draft yang dipilih untuk disetujui.");
            }

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyetujui stock opname: ' . $e->getMessage());
        }
    }
}
