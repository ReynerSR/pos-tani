<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    // Menampilkan daftar supplier dengan fitur pencarian dan pengurutan data
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $perPage = in_array((int) $request->get('per_page'), [10,15,20,50,100], true) ? (int) $request->get('per_page') : 15;

        $sortBy = request('sort_by', 'name');
        $sortDir = request('sort_dir', 'asc');
        $allowedSorts = ['name', 'contact_person', 'phone', 'address', 'id'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('name');
        }

        $suppliers = $query->paginate($perPage)->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    // Menampilkan form untuk menambahkan supplier baru
    public function create(Request $request)
    {
        $returnTo = $request->query('return_to');
        return view('suppliers.create', compact('returnTo'));
    }

    // Menyimpan data supplier baru ke database
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => ['required','string','max:150', function ($attribute, $value, $fail) {
                if (Supplier::whereRaw('LOWER(name) = ?', [strtolower($value)])->exists()) {
                    $fail('Nama supplier sudah digunakan. Nama tidak boleh double.');
                }
            }],
            'address'        => 'required|string',
            'contact_person' => 'required|string|max:100',
            'phone'          => 'required|string|max:20',
            'return_to'      => 'nullable|string|max:100',
        ]);

        $returnTo = $data['return_to'] ?? null;
        unset($data['return_to']);

        $supplier = Supplier::create($data);

        ActivityLog::record('CREATE_SUPPLIER', "Menambahkan supplier: {$supplier->name}");

        if ($returnTo === 'purchases.create') {
            return redirect()->route('purchases.create')
                ->with('purchase_new_supplier_id', $supplier->id)
                ->with('success', "Supplier \"{$supplier->name}\" berhasil ditambahkan dan siap dipilih di restok.");
        }

        return redirect()->route('suppliers.index')
            ->with('success', "Supplier \"{$supplier->name}\" berhasil ditambahkan.");
    }

    // Menampilkan form untuk mengedit data supplier (hanya untuk pemilik)
    public function edit(Supplier $supplier)
    {
        if (auth()->user()->role !== 'pemilik') {
            return redirect()->route('suppliers.index')->with('error', 'Akses ditolak. Edit data supplier hanya boleh dilakukan oleh Pemilik Toko.');
        }
        return view('suppliers.edit', compact('supplier'));
    }

    // Memperbarui data supplier di database
    public function update(Request $request, Supplier $supplier)
    {
        if (auth()->user()->role !== 'pemilik') {
            return redirect()->route('suppliers.index')->with('error', 'Akses ditolak. Edit data supplier hanya boleh dilakukan oleh Pemilik Toko.');
        }
        $data = $request->validate([
            'name'           => ['required','string','max:150', function ($attribute, $value, $fail) use ($supplier) {
                if (Supplier::whereRaw('LOWER(name) = ?', [strtolower($value)])->where('id', '<>', $supplier->id)->exists()) {
                    $fail('Nama supplier sudah digunakan. Nama tidak boleh double.');
                }
            }],
            'address'        => 'required|string',
            'contact_person' => 'required|string|max:100',
            'phone'          => 'required|string|max:20',
        ]);

        $supplier->update($data);

        ActivityLog::record('UPDATE_SUPPLIER', "Memperbarui supplier: {$supplier->name} (ID: {$supplier->id})");

        return redirect()->route('suppliers.index')
            ->with('success', "Supplier \"{$supplier->name}\" berhasil diperbarui.");
    }

    // Menghapus data supplier (hanya jika belum memiliki riwayat pembelian)
    public function destroy(Supplier $supplier)
    {
        if (auth()->user()->role !== 'pemilik') {
            return back()->with('error', 'Hapus supplier hanya boleh dilakukan oleh owner/pemilik.');
        }

        if ($supplier->purchases()->exists()) {
            return back()->with('error', 'Supplier tidak dapat dihapus karena memiliki riwayat pembelian.');
        }

        $name = $supplier->name;
        $supplier->delete();

        ActivityLog::record('DELETE_SUPPLIER', "Menghapus supplier: {$name}");

        return redirect()->route('suppliers.index')
            ->with('success', "Supplier \"{$name}\" berhasil dihapus.");
    }
}
