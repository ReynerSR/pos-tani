@extends('layouts.app')
@section('title','Riwayat Transaksi')
@section('page_title','Riwayat Transaksi')

@section('content')
<div class="page-hdr">
    <div class="page-hdr-left">
        <h1><i class="bi bi-clock-history me-2" style="color:var(--primary)"></i>Riwayat Transaksi</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item active">Semua transaksi, termasuk transaksi yang dihapus/void</li></ol></nav>
    </div>
    <a href="{{ route('kasir.pos') }}" class="btn btn-primary px-4"><i class="bi bi-cart-plus me-2"></i>Transaksi Baru</a>
</div>

<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" id="history-filter-form" class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <div class="search-bar">
                    <i class="bi bi-search si-search"></i>
                    <input type="text" name="search" id="history-search" class="form-control"
                           placeholder="Cari no. transaksi / nama member"
                           value="{{ request('search') }}" autocomplete="off">
                </div>
            </div>
            <div class="col-6 col-md-2"><label class="form-label mb-1 small">Dari Tanggal</label><input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" onchange="this.form.submit()"></div>
            <div class="col-6 col-md-2"><label class="form-label mb-1 small">Sampai Tanggal</label><input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" onchange="this.form.submit()"></div>
            <div class="col-6 col-md-2">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="paid" {{ request('status')==='paid'?'selected':'' }}>Lunas/Aktif</option>
                    <option value="void" {{ request('status')==='void'?'selected':'' }}>Dihapus/Void</option>
                </select>
            </div>
            <div class="col-6 col-md-1">
                <select name="per_page" class="form-select" onchange="this.form.submit()">
                    <option value="20" {{ request('per_page',20)==20?'selected':'' }}>20 Baris</option>
                    <option value="50" {{ request('per_page')==50?'selected':'' }}>50 Baris</option>
                    <option value="100" {{ request('per_page')==100?'selected':'' }}>100 Baris</option>
                </select>
            </div>
            <div class="col-6 col-md-1 d-flex gap-1">
                <a href="{{ route('kasir.history') }}" class="btn btn-outline-secondary w-100" title="Reset filter"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div id="history-results">
<div class="card">
    <div class="card-header"><h6 class="mb-0">Daftar Transaksi <span class="badge bg-success ms-1">{{ $transactions->total() }}</span></h6></div>
    <div class="table-wrapper">
        <table class="table mb-0">
            <thead>
                <tr>
                    <x-sortable-column column="id" label="#" />
                    <x-sortable-column column="transaction_number" label="No. Transaksi" />
                    <x-sortable-column column="transaction_date" label="Tanggal" />
                    <x-sortable-column column="customer_name" label="Member" />
                    <x-sortable-column column="cashier_name" label="Kasir" />
                    <x-sortable-column column="subtotal" label="Subtotal" />
                    <x-sortable-column column="discount_amount" label="Diskon" />
                    <x-sortable-column column="point_redeem_amount" label="Redeem" />
                    <x-sortable-column column="total_price" label="Total" />
                    <x-sortable-column column="payment_status" label="Status" />
                    <th style="width:150px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $trx)
                <tr @if($trx->payment_status !== 'paid') style="background:#fff7ed" @endif>
                    <td style="color:#9ca3af;font-size:.76rem">{{ $trx->id }}</td>
                    <td>
                        <a href="{{ route('kasir.show', ['transaction' => $trx, 'back_url' => request()->fullUrl()]) }}" style="font-weight:600;color:var(--primary);text-decoration:none">{{ $trx->transaction_number }}</a>
                    </td>
                    <td style="font-size:.82rem;color:#6b7280">{{ $trx->transaction_date->format('d/m/Y H:i') }}</td>
                    <td>
                        @if($trx->customer)
                            <span class="badge-tier badge-{{ $trx->customer_tier ?? $trx->customer->tier }}">{{ ucfirst($trx->customer_tier ?? $trx->customer->tier) }}</span>
                            <span style="font-size:.83rem;margin-left:4px">{{ $trx->customer->full_name }}</span>
                        @else
                            <span style="color:#9ca3af;font-size:.82rem">Umum</span>
                        @endif
                    </td>
                    <td style="font-size:.83rem">{{ $trx->cashier->name ?? '-' }}</td>
                    <td style="font-size:.83rem">Rp {{ number_format($trx->subtotal,0,',','.') }}</td>
                    <td style="font-size:.83rem">{{ $trx->discount_amount > 0 ? '-Rp '.number_format($trx->discount_amount,0,',','.') : '-' }}</td>
                    <td style="font-size:.83rem">{{ ($trx->point_redeem_amount ?? 0) > 0 ? '-Rp '.number_format($trx->point_redeem_amount,0,',','.') : '-' }}</td>
                    <td style="font-weight:700;font-size:.9rem">Rp {{ number_format($trx->total_price,0,',','.') }}</td>
                    <td>
                        @if($trx->payment_status === 'paid')
                            <span class="badge bg-success">Lunas</span>
                        @else
                            <span class="badge bg-danger">Dihapus/Void</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('kasir.show', ['transaction' => $trx, 'back_url' => request()->fullUrl()]) }}" class="btn btn-sm btn-icon btn-outline-secondary" title="Detail"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('kasir.receipt', ['transaction' => $trx, 'back_url' => request()->fullUrl()]) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Struk"><i class="bi bi-receipt"></i></a>
                            @if($trx->payment_status === 'paid' && auth()->user()->role === 'pemilik' && $trx->isLatestForCustomer())
                                <a href="{{ route('kasir.edit', ['transaction' => $trx, 'back_url' => request()->fullUrl()]) }}" class="btn btn-sm btn-icon btn-outline-warning" title="Edit Nota"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('kasir.destroy',$trx) }}" class="delete-form" data-confirm="Hapus/void transaksi ini? Stok akan dikembalikan dan poin member akan dikurangi.">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-icon btn-outline-danger" title="Delete/Void"><i class="bi bi-trash"></i></button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="11" class="text-center py-5" style="color:#9ca3af"><i class="bi bi-receipt" style="font-size:2.5rem;display:block;margin-bottom:10px"></i>Tidak ada transaksi ditemukan</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($transactions->hasPages())<div class="card-body border-top py-3">{{ $transactions->withQueryString()->links() }}</div>@endif
</div>
</div>{{-- #history-results --}}
@endsection

@push('scripts')
<script>
(function () {
    const searchInput = document.getElementById('history-search');
    const form        = document.getElementById('history-filter-form');
    if (!searchInput || !form) return;

    const baseUrl = '{{ route('kasir.history') }}';

    // Ambil semua nilai filter saat ini dari form (kecuali field search)
    function buildParams(searchVal) {
        const data = new FormData(form);
        data.set('search', searchVal);
        return new URLSearchParams(data).toString();
    }

    async function doAjaxSearch(q) {
        const qs  = buildParams(q);
        const url = baseUrl + '?' + qs;

        // Update URL di browser tanpa reload
        history.replaceState(null, '', url);

        try {
            const res  = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const html = await res.text();

            // Parse HTML response dan ambil hanya bagian #history-results
            const parser  = new DOMParser();
            const doc     = parser.parseFromString(html, 'text/html');
            const newPart = doc.getElementById('history-results');

            if (newPart) {
                document.getElementById('history-results').innerHTML = newPart.innerHTML;
            }
        } catch (e) {
            // Fallback ke full reload jika fetch gagal
            window.location.href = url;
        }
    }

    let timer;
    searchInput.addEventListener('input', function () {
        clearTimeout(timer);
        const q = this.value;
        timer = setTimeout(() => doAjaxSearch(q), 380);
    });

    // Filter lain (tanggal, status, per_page) tetap full-page submit
    form.querySelectorAll('input[type="date"], select').forEach(el => {
        el.addEventListener('change', () => form.submit());
    });
})();
</script>
@endpush
