<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\CustomerTierHistory;
use App\Models\MembershipRule;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    // Menampilkan daftar pelanggan dengan fitur pencarian, pengurutan, dan filter tier
    public function index(Request $request)
    {
        // Inisialisasi query builder untuk model Customer
        $query = Customer::query();

        // Filter pencarian berdasarkan nama lengkap atau nomor WhatsApp
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('whatsapp_number', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan tier (misalnya: bronze, silver, gold)
        if ($request->filled('tier')) {
            $query->where('tier', $request->tier);
        }

        // Konfigurasi jumlah data per halaman (default: 15)
        $perPage = in_array((int) $request->get('per_page'), [10,15,20,50,100], true) ? (int) $request->get('per_page') : 20;

        // Konfigurasi pengurutan data beserta kolom yang diizinkan
        $sortBy = request('sort_by', 'full_name');
        $sortDir = request('sort_dir', 'asc');
        $allowedSorts = ['full_name', 'whatsapp_number', 'total_accumulation', 'point_balance', 'tier', 'registered_at', 'id'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('full_name');
        }

        // Eksekusi query dengan pagination dan pertahankan parameter URL saat ini
        $customers = $query->paginate($perPage)->withQueryString();

        // Hitung total pelanggan untuk setiap tier
        $tierCounts = Customer::selectRaw('tier, COUNT(*) as total')
            ->groupBy('tier')
            ->pluck('total', 'tier')
            ->toArray();

        return view('customers.index', compact('customers', 'tierCounts'));
    }

    // Menampilkan form tambah pelanggan baru
    public function create(Request $request)
    {
        $returnTo = $request->query('return_to') === 'kasir' ? 'kasir' : null;

        return view('customers.create', compact('returnTo'));
    }

    // Menyimpan data pelanggan baru ke database
    public function store(Request $request)
    {
        // Validasi input data dari form
        $validated = $request->validate([
            'full_name'       => ['required','string','max:150', function ($attribute, $value, $fail) {
                if (Customer::whereRaw('LOWER(full_name) = ?', [strtolower($value)])->exists()) {
                    $fail('Nama pelanggan/member sudah digunakan. Nama tidak boleh double.');
                }
            }],
            'whatsapp_number' => 'required|string|max:20',
            'address'         => 'nullable|string',
            'return_to'       => 'nullable|in:kasir',
        ]);

        // Pisahkan parameter return_to karena tidak tersimpan di database
        $returnTo = $validated['return_to'] ?? null;
        unset($validated['return_to']);

        // Set nilai bawaan untuk pelanggan baru
        $validated['registered_at']      = now()->toDateString();
        $validated['tier']               = 'bronze';
        $validated['total_accumulation'] = 0;
        $validated['point_balance']      = 0;

        // Simpan data ke database
        $customer = Customer::create($validated);

        // Catat aktivitas
        ActivityLog::record('CREATE_CUSTOMER', "Mendaftarkan member baru: {$customer->full_name}");

        // Jika pelanggan didaftarkan dari kasir, arahkan kembali ke kasir beserta ID-nya
        if ($returnTo === 'kasir') {
            return redirect()->route('kasir.pos')
                ->with('pos_new_customer_id', $customer->id)
                ->with('success', "Member \"{$customer->full_name}\" berhasil didaftarkan dan siap dipakai di kasir.");
        }

        return redirect()->route('customers.index')
            ->with('success', "Member \"{$customer->full_name}\" berhasil didaftarkan.");
    }

    // Menampilkan detail pelanggan termasuk riwayat transaksi dan poin
    public function show(Customer $customer)
    {
        // Muat data relasi yang diperlukan untuk efisiensi query
        $customer->load(['transactions.cashier', 'pointHistory.transaction', 'tierHistories.changedBy', 'tierHistories.transaction']);

        $rule = MembershipRule::getCurrent();

        $nextTierLabel    = null;
        $nextTierProgress = 0;
        $nextTierAmount   = 0;

        // Hitung progres akumulasi menuju tier selanjutnya
        if ($customer->tier === 'bronze') {
            $nextTierLabel    = 'Silver';
            $nextTierAmount   = (float) $rule->tier_silver_min;
            $nextTierProgress = $nextTierAmount > 0 ? min(100, round(($customer->total_accumulation / $nextTierAmount) * 100)) : 100;
        } elseif ($customer->tier === 'silver') {
            $nextTierLabel    = 'Gold';
            $nextTierAmount   = (float) $rule->tier_gold_min;
            $nextTierProgress = $nextTierAmount > 0 ? min(100, round(($customer->total_accumulation / $nextTierAmount) * 100)) : 100;
        } else {
            // Sudah mencapai tier maksimal
            $nextTierProgress = 100;
        }

        // Ambil 10 transaksi terakhir pelanggan
        $recentTransactions = $customer->transactions()
            ->with('cashier')
            ->latest('transaction_date')
            ->limit(10)
            ->get();

        $pointHistories = $customer->pointHistory()
            ->with('transaction')
            ->latest()
            ->limit(10)
            ->get();

        return view('customers.show', compact(
            'customer',
            'rule',
            'nextTierLabel',
            'nextTierProgress',
            'nextTierAmount',
            'recentTransactions',
            'pointHistories',
        ));
    }

    // Menampilkan form edit pelanggan
    public function edit(Customer $customer)
    {
        // Hanya pemilik yang dapat mengedit data member
        if (auth()->user()->role !== 'pemilik') {
            return redirect()->route('customers.show', $customer)->with('error', 'Akses ditolak. Kasir/Admin hanya bisa mendaftar member baru, tidak bisa mengedit data member.');
        }
        return view('customers.edit', compact('customer'));
    }

    // Memperbarui data pelanggan
    public function update(Request $request, Customer $customer)
    {
        // Hanya pemilik yang dapat menyimpan perubahan data member
        if (auth()->user()->role !== 'pemilik') {
            return redirect()->route('customers.show', $customer)->with('error', 'Akses ditolak. Kasir/Admin hanya bisa mendaftar member baru, tidak bisa mengedit data member.');
        }

        $rules = [
            'full_name'       => ['required','string','max:150', function ($attribute, $value, $fail) use ($customer) {
                if (Customer::whereRaw('LOWER(full_name) = ?', [strtolower($value)])->where('id', '<>', $customer->id)->exists()) {
                    $fail('Nama pelanggan/member sudah digunakan. Nama tidak boleh double.');
                }
            }],
            'whatsapp_number' => 'required|string|max:20',
            'address'         => 'nullable|string',
        ];

        // Cek validasi untuk opsi tier (hanya jika user adalah pemilik)
        if (auth()->user()->isPemilik()) {
            $rules['tier'] = 'required|in:bronze,silver,gold';
        }

        $data = $request->validate($rules);

        // Abaikan pembaruan tier jika bukan pemilik
        if (! auth()->user()->isPemilik()) {
            unset($data['tier']);
        }

        // Simpan nilai tier dan total sebelumnya sebelum diperbarui
        $oldTier = $customer->tier;
        $oldTotal = (float) $customer->total_accumulation;
        $customer->update($data);

        // Jika terjadi perubahan tier secara manual, catat di riwayat tier
        if ($oldTier !== $customer->tier) {
            CustomerTierHistory::create([
                'customer_id' => $customer->id,
                'changed_by' => auth()->id(),
                'old_tier' => $oldTier,
                'new_tier' => $customer->tier,
                'old_total_accumulation' => $oldTotal,
                'new_total_accumulation' => (float) $customer->total_accumulation,
                'source' => 'manual',
                'notes' => 'Tier diubah manual dari halaman Data Member.',
            ]);
        }

        ActivityLog::record(
            'UPDATE_CUSTOMER',
            "Memperbarui data member: {$customer->full_name} (ID: {$customer->id})" . ($oldTier !== $customer->tier ? " — Tier {$oldTier} menjadi {$customer->tier}" : '')
        );

        return redirect()->route('customers.show', $customer)
            ->with('success', "Data member berhasil diperbarui.");
    }

    // Endpoint untuk fitur pencarian berbasis AJAX
    public function search(Request $request)
    {
        $query = $request->get('q', '');

        $customers = Customer::where(function ($q) use ($query) {
            // Mencocokkan awalan nama pelanggan (Starts with)
            $q->where('full_name', 'like', "{$query}%")
              ->orWhere('whatsapp_number', 'like', "{$query}%")
              ->orWhere('address', 'like', "{$query}%");
        })
        ->select('id', 'full_name', 'whatsapp_number', 'address', 'tier', 'point_balance', 'total_accumulation')
        ->orderBy('full_name')->limit(20)
        ->get();

        return response()->json($customers);
    }
}
