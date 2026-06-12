<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseController extends Controller
{
    // Menampilkan daftar tempat penyimpanan (gudang/toko utama) dengan fitur pencarian dan pengurutan
    public function index(Request $request)
    {
        $query = Warehouse::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $perPage = in_array((int) $request->get('per_page'), [10,15,20,50,100], true) ? (int) $request->get('per_page') : 20;

        $sortBy = request('sort_by', 'code');
        $sortDir = request('sort_dir', 'asc');
        $allowedSorts = ['code', 'name', 'location', 'is_store', 'is_active', 'id'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('code');
        }

        $warehouses = $query->paginate($perPage)->withQueryString();

        return view('warehouses.index', compact('warehouses'));
    }

    // Menampilkan form untuk menambahkan tempat penyimpanan baru
    public function create()
    {
        return view('warehouses.create');
    }

    // Menyimpan data tempat penyimpanan baru ke database
    public function store(Request $request)
    {
        $request->validate([
            'code'      => 'required|string|max:20|unique:warehouses,code',
            'name'      => ['required','string','max:100', function ($attribute, $value, $fail) {
                if (Warehouse::whereRaw('LOWER(name) = ?', [strtolower($value)])->exists()) {
                    $fail('Nama tempat penyimpanan sudah digunakan. Nama tidak boleh double.');
                }
            }],
            'location'  => 'nullable|string|max:255',
            'is_store'  => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            if ($request->boolean('is_store')) {
                Warehouse::where('is_store', true)->update(['is_store' => false]);
            }

            $warehouse = Warehouse::create([
                'code'      => strtoupper($request->code),
                'name'      => $request->name,
                'location'  => $request->location,
                'is_store'  => $request->boolean('is_store'),
                'is_active' => $request->boolean('is_active', true),
            ]);

            DB::commit();

            ActivityLog::record('CREATE_WAREHOUSE', "Menambahkan tempat penyimpanan {$warehouse->code} - {$warehouse->name}" . ($warehouse->is_store ? ' sebagai toko utama' : ''));
            return redirect()->route('warehouses.index')->with('success', 'Tempat penyimpanan berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menambahkan gudang: ' . $e->getMessage());
        }
    }

    // Menampilkan form edit data tempat penyimpanan
    public function edit(Warehouse $warehouse)
    {
        $currentMainWarehouse = Warehouse::where('is_store', true)->where('id', '<>', $warehouse->id)->first();
        return view('warehouses.edit', compact('warehouse', 'currentMainWarehouse'));
    }

    // Memperbarui data tempat penyimpanan di database
    public function update(Request $request, Warehouse $warehouse)
    {
        $request->validate([
            'code'      => 'required|string|max:20|unique:warehouses,code,' . $warehouse->id,
            'name'      => ['required','string','max:100', function ($attribute, $value, $fail) use ($warehouse) {
                if (Warehouse::whereRaw('LOWER(name) = ?', [strtolower($value)])->where('id', '<>', $warehouse->id)->exists()) {
                    $fail('Nama tempat penyimpanan sudah digunakan. Nama tidak boleh double.');
                }
            }],
            'location'  => 'nullable|string|max:255',
            'is_store'  => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            if ($request->boolean('is_store')) {
                Warehouse::where('id', '<>', $warehouse->id)->where('is_store', true)->update(['is_store' => false]);
            }

            $warehouse->update([
                'code'      => strtoupper($request->code),
                'name'      => $request->name,
                'location'  => $request->location,
                'is_store'  => $request->boolean('is_store'),
                'is_active' => $request->boolean('is_active', true),
            ]);

            DB::commit();

            ActivityLog::record('UPDATE_WAREHOUSE', "Memperbarui tempat penyimpanan {$warehouse->code} - {$warehouse->name}" . ($warehouse->is_store ? ' sebagai toko utama' : ''));
            return redirect()->route('warehouses.index')->with('success', 'Tempat penyimpanan berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui gudang: ' . $e->getMessage());
        }
    }

    // Menghapus data tempat penyimpanan (hanya untuk pemilik dan jika gudang kosong)
    public function destroy(Warehouse $warehouse)
    {
        if (auth()->user()->role !== 'pemilik') {
            return back()->with('error', 'Hapus tempat penyimpanan hanya boleh dilakukan oleh owner/pemilik.');
        }

        if ($warehouse->is_store) {
            return back()->with('error', 'Toko utama tidak boleh dihapus sebelum toko utama lain dipilih.');
        }

        if ($warehouse->warehouseStocks()->where('stock', '<>', 0)->exists()) {
            return back()->with('error', 'Gudang hanya bisa dihapus jika tidak ada produk/stok di dalamnya. Kosongkan atau transfer stok terlebih dahulu.');
        }

        $code = $warehouse->code;
        $warehouse->delete();
        ActivityLog::record('DELETE_WAREHOUSE', "Menghapus tempat penyimpanan {$code} karena tidak memiliki produk/stok.");
        return redirect()->route('warehouses.index')->with('success', 'Tempat penyimpanan berhasil dihapus.');
    }
}
