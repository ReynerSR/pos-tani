<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
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
        $suppliers = $query->orderBy('name')->paginate($perPage)->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create(Request $request)
    {
        $returnTo = $request->query('return_to');
        return view('suppliers.create', compact('returnTo'));
    }

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

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
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
