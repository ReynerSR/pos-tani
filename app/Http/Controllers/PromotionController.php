<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index(Request $request)
    {
        $query = Promotion::with(['product', 'createdBy']);

        if ($request->filled('search')) {
            $query->where('promo_name', 'like', "%{$request->search}%")
                ->orWhereHas('product', fn($q) => $q->where('product_name', 'like', "%{$request->search}%"));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('start_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('start_date', '<=', $request->date_to);
        }

        if ($request->filled('status')) {
            $today = now()->toDateString();
            match ($request->status) {
                'active'   => $query->where('is_active', true)->where('start_date', '<=', $today)->where('end_date', '>=', $today),
                'expired'  => $query->where('end_date', '<', $today),
                'upcoming' => $query->where('start_date', '>', $today),
                'inactive' => $query->where('is_active', false),
                default    => null,
            };
        }

        $perPage = in_array((int) $request->get('per_page'), [10,15,20,50,100], true) ? (int) $request->get('per_page') : 15;

        $sortBy = request('sort_by', 'created_at');
        $sortDir = request('sort_dir', 'desc');
        $allowedSorts = ['promo_name', 'discount_amount', 'start_date', 'end_date', 'is_active', 'created_at', 'product_name', 'id'];

        if ($sortBy === 'product_name') {
            $query->orderBy(Product::select('product_name')->whereColumn('products.id', 'promotions.product_id')->limit(1), $sortDir === 'asc' ? 'asc' : 'desc');
        } elseif (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderByDesc('created_at');
        }

        $promotions = $query->paginate($perPage)->withQueryString();

        return view('promotions.index', compact('promotions'));
    }

    public function create()
    {
        if (auth()->user()->role !== 'pemilik') {
            return redirect()->route('promotions.index')->with('error', 'Akses ditolak. Tambah promo hanya boleh dilakukan oleh Pemilik Toko.');
        }
        $products = Product::where('is_active', true)->orderBy('product_name')->get();

        return view('promotions.create', compact('products'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'pemilik') {
            return redirect()->route('promotions.index')->with('error', 'Akses ditolak. Tambah promo hanya boleh dilakukan oleh Pemilik Toko.');
        }
        $data = $request->validate([
            'promo_name'      => ['required','string','max:150', function ($attribute, $value, $fail) {
                if (Promotion::whereRaw('LOWER(promo_name) = ?', [strtolower($value)])->exists()) {
                    $fail('Nama promo sudah digunakan. Nama tidak boleh double.');
                }
            }],
            'product_id'      => 'required|exists:products,id',
            'discount_amount' => 'required|numeric|min:1',
            'eligible_tiers' => 'nullable|array',
            'eligible_tiers.*' => 'in:bronze,silver,gold',
            'start_date'      => 'required|date',
            'end_date'        => 'required|date|after_or_equal:start_date',
            'is_active'       => 'boolean',
            'notes'           => 'nullable|string',
        ]);

        $data['is_active']   = $request->boolean('is_active', true);
        $data['eligible_tiers'] = $request->input('eligible_tiers', []);
        $data['created_by']  = auth()->id();

        $promo = Promotion::create($data);

        ActivityLog::record(
            'CREATE_PROMO',
            "Membuat promo: {$promo->promo_name} — Produk: {$promo->product->product_name} — Potongan: Rp " . number_format($promo->discount_amount, 0, ',', '.')
        );

        return redirect()->route('promotions.index')
            ->with('success', "Promo \"{$promo->promo_name}\" berhasil ditambahkan.");
    }

    public function show(Promotion $promotion)
    {
        $promotion->load(['product', 'createdBy']);
        return view('promotions.show', compact('promotion'));
    }

    public function edit(Promotion $promotion)
    {
        if (auth()->user()->role !== 'pemilik') {
            return redirect()->route('promotions.index')->with('error', 'Akses ditolak. Edit promo hanya boleh dilakukan oleh Pemilik Toko.');
        }
        $products = Product::where('is_active', true)->orderBy('product_name')->get();

        return view('promotions.edit', compact('promotion', 'products'));
    }

    public function update(Request $request, Promotion $promotion)
    {
        if (auth()->user()->role !== 'pemilik') {
            return redirect()->route('promotions.index')->with('error', 'Akses ditolak. Edit promo hanya boleh dilakukan oleh Pemilik Toko.');
        }
        $data = $request->validate([
            'promo_name'      => ['required','string','max:150', function ($attribute, $value, $fail) use ($promotion) {
                if (Promotion::whereRaw('LOWER(promo_name) = ?', [strtolower($value)])->where('id', '<>', $promotion->id)->exists()) {
                    $fail('Nama promo sudah digunakan. Nama tidak boleh double.');
                }
            }],
            'product_id'      => 'required|exists:products,id',
            'discount_amount' => 'required|numeric|min:1',
            'eligible_tiers' => 'nullable|array',
            'eligible_tiers.*' => 'in:bronze,silver,gold',
            'start_date'      => 'required|date',
            'end_date'        => 'required|date|after_or_equal:start_date',
            'is_active'       => 'boolean',
            'notes'           => 'nullable|string',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['eligible_tiers'] = $request->input('eligible_tiers', []);

        $promotion->update($data);

        ActivityLog::record('UPDATE_PROMO', "Memperbarui promo: {$promotion->promo_name} (ID: {$promotion->id})");

        return redirect()->route('promotions.index')
            ->with('success', "Promo \"{$promotion->promo_name}\" berhasil diperbarui.");
    }

    public function destroy(Promotion $promotion)
    {
        if (auth()->user()->role !== 'pemilik') {
            return back()->with('error', 'Hapus promo hanya boleh dilakukan oleh owner/pemilik.');
        }

        $name = $promotion->promo_name;
        $promotion->delete();

        ActivityLog::record('DELETE_PROMO', "Menghapus promo: {$name}");

        return redirect()->route('promotions.index')
            ->with('success', "Promo \"{$name}\" berhasil dihapus.");
    }
}
