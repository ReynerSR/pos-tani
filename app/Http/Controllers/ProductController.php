<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    private array $sortableColumns = ['product_code', 'product_name', 'category', 'unit', 'selling_price', 'hpp', 'stock', 'minimum_stock', 'is_active', 'id'];

    // Menampilkan daftar produk dengan fitur pencarian, filter, dan pengurutan
    public function index(Request $request)
    {
        $query = Product::with('warehouseStocks.warehouse');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                  ->orWhere('product_code', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            if ($request->status === 'low') {
                $query->whereColumn('stock', '<=', 'minimum_stock')->where('stock', '>', 0);
            } elseif ($request->status === 'empty') {
                $query->where('stock', '<=', 0);
            } elseif ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $sortBy = in_array($request->get('sort_by'), $this->sortableColumns, true) ? $request->get('sort_by') : 'product_name';
        $sortDir = $request->get('sort_dir') === 'desc' ? 'desc' : 'asc';

        $perPage = in_array((int) $request->get('per_page'), [10,15,20,50,100], true) ? (int) $request->get('per_page') : 20;
        $products = $query->orderBy($sortBy, $sortDir)->paginate($perPage)->withQueryString();

        $categories = Product::select('category')
            ->whereNotNull('category')
            ->where('category', '<>', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $units = Product::select('unit')
            ->whereNotNull('unit')
            ->where('unit', '<>', '')
            ->distinct()
            ->orderBy('unit')
            ->pluck('unit')
            ->map(fn ($unit) => strtoupper(trim((string) $unit)))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        if ($units->isEmpty()) {
            $units = collect(['PCS', 'SAK', 'LITER', 'DOS', 'KG', 'BOTOL', 'KARUNG']);
        }

        $warehouses = Warehouse::where('is_active', true)->orderBy('code')->get();

        return view('products.index', compact('products', 'categories', 'units', 'warehouses', 'sortBy', 'sortDir'));
    }

    // Menampilkan form tambah produk baru beserta daftar kategori dan satuan unik
    public function create()
    {
        $categories = Product::select('category')
            ->whereNotNull('category')
            ->where('category', '<>', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $units = Product::select('unit')
            ->whereNotNull('unit')
            ->where('unit', '<>', '')
            ->distinct()
            ->orderBy('unit')
            ->pluck('unit')
            ->map(fn ($unit) => strtoupper(trim((string) $unit)))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        if ($units->isEmpty()) {
            $units = collect(['PCS', 'SAK', 'LITER', 'DOS', 'KG', 'BOTOL', 'KARUNG']);
        }

        return view('products.create', compact('categories', 'units'));
    }

    // Menyimpan data produk baru ke database
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_name'     => ['required','string','max:150', function($attribute, $value, $fail) { if (Product::whereRaw('LOWER(product_name)=?', [mb_strtolower(trim($value))])->exists()) { $fail('Nama produk sudah ada. Nama produk tidak boleh double.'); } }],
            'category'         => 'nullable|required_without:new_category|string|max:100',
            'new_category'     => 'nullable|required_without:category|string|max:100',
            'unit'             => 'nullable|required_without:new_unit|string|max:30',
            'new_unit'         => 'nullable|required_without:unit|string|max:30',
            'selling_price'    => 'required|numeric|min:0',
            'hpp'              => 'nullable|numeric|min:0',
            'stock'            => 'nullable|integer|min:0',
            'minimum_stock'    => 'required|integer|min:0',
            'is_active'        => 'boolean',
        ]);

        // Terapkan pembatasan harga jika pengguna adalah admin
        if (auth()->user()->role === 'admin') {
            $data['selling_price'] = 0;
            $data['hpp'] = 0;
        }

        $category = trim($request->new_category ?: ($request->category ?: 'UMUM'));
        $unit     = strtoupper(trim($request->new_unit ?: ($request->unit ?: 'PCS')));

        DB::beginTransaction();
        try {
            $product = Product::create([
                'product_code'  => 'TMP-' . uniqid(),
                'product_name'  => $data['product_name'],
                'category'      => $category,
                'unit'          => $unit,
                'selling_price' => (float) $data['selling_price'],
                'hpp'           => (float) ($data['hpp'] ?? 0),
                'stock'         => (int) ($data['stock'] ?? 0),
                'minimum_stock' => (int) $data['minimum_stock'],
                'is_active'     => $request->boolean('is_active', true),
            ]);

            $product->update([
                'product_code' => $this->generateProductCode($category, $product->id),
            ]);

            // Jika toko utama diatur dan ada stok awal, sinkronkan ke stok gudang toko utama
            if ((int) ($data['stock'] ?? 0) > 0) {
                $storeWarehouse = Warehouse::where('is_store', true)->first();
                if ($storeWarehouse) {
                    $product->warehouseStocks()->updateOrCreate(
                        ['warehouse_id' => $storeWarehouse->id],
                        ['stock' => (int) ($data['stock'] ?? 0), 'minimum_stock' => (int) $data['minimum_stock']]
                    );
                }
            }

            DB::commit();

            ActivityLog::record('CREATE_PRODUCT', "Menambahkan produk: {$product->product_name} (Kode: {$product->product_code})");

            return redirect()->route('products.create')
                ->withInput(['markup' => $request->input('markup', 0), 'category' => $category, 'unit' => $unit])
                ->with('success', "Produk \"{$product->product_name}\" berhasil ditambahkan dengan kode {$product->product_code}. Silakan lanjut input produk berikutnya.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menambahkan produk: ' . $e->getMessage());
        }
    }

    // Menampilkan detail produk termasuk riwayat transaksi dan penyesuaian stok
    public function show(Product $product)
    {
        $product->load(['transactionDetails.transaction', 'purchaseDetails.purchase.supplier', 'stockAdjustments.user', 'warehouseStocks.warehouse']);

        $recentTransactions = $product->transactionDetails()
            ->with('transaction.cashier')
            ->latest()
            ->limit(10)
            ->get();

        $recentPurchases = $product->purchaseDetails()
            ->with('purchase.supplier')
            ->latest()
            ->limit(10)
            ->get();

        return view('products.show', compact('product', 'recentTransactions', 'recentPurchases'));
    }

    // Menampilkan form edit data produk (hanya untuk pemilik)
    public function edit(Product $product)
    {
        if (auth()->user()->role !== 'pemilik') {
            return redirect()->route('products.index')->with('error', 'Akses ditolak. Edit produk hanya boleh dilakukan oleh Pemilik Toko.');
        }

        $categories = Product::select('category')
            ->whereNotNull('category')
            ->where('category', '<>', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $units = Product::select('unit')
            ->whereNotNull('unit')
            ->where('unit', '<>', '')
            ->distinct()
            ->orderBy('unit')
            ->pluck('unit')
            ->map(fn ($unit) => strtoupper(trim((string) $unit)))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        if ($units->isEmpty()) {
            $units = collect(['PCS', 'SAK', 'LITER', 'DOS', 'KG', 'BOTOL', 'KARUNG']);
        }

        return view('products.edit', compact('product', 'categories', 'units'));
    }

    // Memperbarui data produk di database
    public function update(Request $request, Product $product)
    {
        if (auth()->user()->role !== 'pemilik') {
            return redirect()->route('products.index')->with('error', 'Akses ditolak. Edit produk hanya boleh dilakukan oleh Pemilik Toko.');
        }

        $data = $request->validate([
            'product_code'     => ['required', 'string', 'max:50', Rule::unique('products', 'product_code')->ignore($product->id)],
            'product_name'     => ['required','string','max:150', function($attribute, $value, $fail) use ($product) { if (Product::whereRaw('LOWER(product_name)=?', [mb_strtolower(trim($value))])->where('id', '<>', $product->id)->exists()) { $fail('Nama produk sudah ada. Nama produk tidak boleh double.'); } }],
            'category'         => 'nullable|required_without:new_category|string|max:100',
            'new_category'     => 'nullable|required_without:category|string|max:100',
            'unit'             => 'nullable|required_without:new_unit|string|max:30',
            'new_unit'         => 'nullable|required_without:unit|string|max:30',
            'selling_price'    => 'required|numeric|min:0',
            'hpp'              => 'required|numeric|min:0',
            'minimum_stock'    => 'required|integer|min:0',
            'is_active'        => 'boolean',
        ]);

        // Terapkan pembatasan harga jika pengguna adalah admin
        if (auth()->user()->role === 'admin') {
            $data['selling_price'] = $product->selling_price;
            $data['hpp'] = $product->hpp;
        }

        $oldCategory = $product->category;
        $oldUnit = $product->unit;

        $data['category']  = trim($request->new_category ?: ($request->category ?: 'UMUM'));
        $data['unit']      = strtoupper(trim($request->new_unit ?: ($request->unit ?: 'PCS')));
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->has('update_all_category') && $request->new_category && $oldCategory) {
            Product::where('category', $oldCategory)->update(['category' => $data['category']]);
        }
        
        if ($request->has('update_all_unit') && $request->new_unit && $oldUnit) {
            Product::where('unit', $oldUnit)->update(['unit' => $data['unit']]);
        }

        unset($data['new_category'], $data['new_unit']);

        $product->update($data);

        ActivityLog::record('UPDATE_PRODUCT', "Memperbarui produk: {$product->product_name} (ID: {$product->id})");

        return redirect()->route('products.index')
            ->with('success', "Produk \"{$product->product_name}\" berhasil diperbarui.");
    }

    // Menghapus produk dari database (jika belum ada riwayat)
    public function destroy(Product $product)
    {
        if (auth()->user()->role !== 'pemilik') {
            return back()->with('error', 'Hapus produk hanya boleh dilakukan oleh owner/pemilik.');
        }

        if ($product->transactionDetails()->exists() || $product->purchaseDetails()->exists()) {
            return back()->with('error', 'Produk tidak dapat dihapus karena sudah memiliki riwayat transaksi atau pembelian. Nonaktifkan produk jika sudah tidak digunakan.');
        }

        $name = $product->product_name;
        $product->delete();

        ActivityLog::record('DELETE_PRODUCT', "Menghapus produk: {$name}");

        return redirect()->route('products.index')
            ->with('success', "Produk \"{$name}\" berhasil dihapus.");
    }

    // Endpoint untuk pencarian produk melalui AJAX
    public function search(Request $request)
    {
        $query = $request->get('q', '');

        $products = Product::where('is_active', true)
            ->where(function ($q) use ($query) {
                // Mencocokkan awalan nama produk (Starts with)
                $q->where('product_name', 'like', "{$query}%")
                  ->orWhere('product_code', 'like', "%{$query}%")
                  ->orWhere('category', 'like', "%{$query}%");
            })
            ->select('id', 'product_code', 'product_name', 'selling_price', 'stock', 'unit', 'hpp')
            ->orderBy('product_name', 'asc')
            ->limit(20)
            ->get();

        return response()->json($products);
    }


    // Mengubah nama kategori secara massal pada semua produk yang menggunakannya
    public function updateCategory(Request $request)
    {
        if (auth()->user()->role !== 'pemilik') {
            return back()->with('error', 'Edit kategori massal hanya boleh dilakukan oleh owner/pemilik.');
        }

        $data = $request->validate([
            'old_category' => 'required|string|max:100',
            'new_category' => 'required|string|max:100',
        ]);

        $old = trim($data['old_category']);
        $new = trim($data['new_category']);

        if ($old === $new) {
            return back()->with('error', 'Kategori baru sama dengan kategori lama.');
        }

        $count = Product::where('category', $old)->update(['category' => $new]);
        ActivityLog::record('UPDATE_PRODUCT_CATEGORY', "Mengubah kategori {$old} menjadi {$new} untuk {$count} produk.");

        return back()->with('success', "Kategori {$old} berhasil diubah menjadi {$new} untuk {$count} produk.");
    }

    // Mengubah nama satuan secara massal pada semua produk yang menggunakannya
    public function updateUnit(Request $request)
    {
        if (auth()->user()->role !== 'pemilik') {
            return back()->with('error', 'Edit satuan massal hanya boleh dilakukan oleh owner/pemilik.');
        }

        $data = $request->validate([
            'old_unit' => 'required|string|max:30',
            'new_unit' => 'required|string|max:30',
        ]);

        $old = strtoupper(trim($data['old_unit']));
        $new = strtoupper(trim($data['new_unit']));

        if ($old === $new) {
            return back()->with('error', 'Satuan baru sama dengan satuan lama.');
        }

        $count = Product::where('unit', $old)->update(['unit' => $new]);
        ActivityLog::record('UPDATE_PRODUCT_UNIT', "Mengubah satuan {$old} menjadi {$new} untuk {$count} produk.");

        return back()->with('success', "Satuan {$old} berhasil diubah menjadi {$new} untuk {$count} produk.");
    }


    // Menghapus kategori dan memindahkan produk di dalamnya ke kategori "LAIN-LAIN"
    public function destroyCategory(Request $request)
    {
        if (auth()->user()->role !== 'pemilik') {
            return back()->with('error', 'Hapus kategori hanya boleh dilakukan oleh owner/pemilik.');
        }

        $data = $request->validate([
            'category' => 'required|string|max:100',
        ]);

        $category = trim($data['category']);
        if ($category === '' || strcasecmp($category, 'LAIN-LAIN') === 0) {
            return back()->with('error', 'Kategori default LAIN-LAIN tidak boleh dihapus.');
        }

        $count = Product::whereRaw('LOWER(category)=?', [mb_strtolower($category)])->count();
        if ($count < 1) {
            return back()->with('error', 'Kategori tidak ditemukan atau sudah tidak digunakan.');
        }

        Product::whereRaw('LOWER(category)=?', [mb_strtolower($category)])->update(['category' => 'LAIN-LAIN']);
        ActivityLog::record('DELETE_PRODUCT_CATEGORY', "Menghapus kategori {$category}. {$count} produk dipindahkan ke kategori LAIN-LAIN.");

        return back()->with('success', "Kategori {$category} berhasil dihapus. {$count} produk dipindahkan ke kategori LAIN-LAIN.");
    }

    // Menghapus satuan dan memindahkan produk di dalamnya ke satuan default "PCS"
    public function destroyUnit(Request $request)
    {
        if (auth()->user()->role !== 'pemilik') {
            return back()->with('error', 'Hapus satuan hanya boleh dilakukan oleh owner/pemilik.');
        }

        $data = $request->validate([
            'unit' => 'required|string|max:30',
        ]);

        $unit = strtoupper(trim($data['unit']));
        if ($unit === '' || $unit === 'PCS') {
            return back()->with('error', 'Satuan default PCS tidak boleh dihapus.');
        }

        $count = Product::whereRaw('UPPER(unit)=?', [$unit])->count();
        if ($count < 1) {
            return back()->with('error', 'Satuan tidak ditemukan atau sudah tidak digunakan.');
        }

        Product::whereRaw('UPPER(unit)=?', [$unit])->update(['unit' => 'PCS']);
        ActivityLog::record('DELETE_PRODUCT_UNIT', "Menghapus satuan {$unit}. {$count} produk dipindahkan ke satuan PCS.");

        return back()->with('success', "Satuan {$unit} berhasil dihapus. {$count} produk dipindahkan ke satuan PCS.");
    }

    // Fungsi bantuan untuk membuat kode produk otomatis berdasarkan kategori dan ID
    private function generateProductCode(string $category, int $id): string
    {
        $clean = strtoupper(preg_replace('/[^A-Z0-9]/', '', $category));
        $prefix = substr($clean ?: 'UMUM', 0, 4);
        return $prefix . '-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT);
    }
}
