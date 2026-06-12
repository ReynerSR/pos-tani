<!-- Menggunakan template utama aplikasi -->
@extends('layouts.app')

<!-- Menentukan judul halaman -->
@section('title','Data Pelanggan')
@section('page_title','Data Pelanggan')

@section('content')
<!-- Header halaman -->
<div class="page-hdr">
    <div class="page-hdr-left">
        <h1><i class="bi bi-people me-2" style="color:var(--primary)"></i>Data Pelanggan</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item active">Daftar Member</li></ol></nav>
    </div>
    <a href="{{ route('customers.create') }}" class="btn btn-primary px-4">
        <i class="bi bi-person-plus me-2"></i>Daftarkan Member Baru
    </a>
</div>

<!-- Kartu Ringkasan Jumlah Member Berdasarkan Tier -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card p-3 text-center">
            <div style="font-size:1.6rem;font-weight:800;color:var(--primary-dark)">{{ array_sum($tierCounts) }}</div>
            <div style="font-size:.76rem;color:#6b7280;font-weight:600">Total Member</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card p-3 text-center" style="border-left:4px solid #f39c12">
            <div style="font-size:1.6rem;font-weight:800;color:#92400e">{{ $tierCounts['gold'] ?? 0 }}</div>
            <div style="font-size:.76rem;color:#6b7280;font-weight:600"><i class="bi bi-award-fill text-warning me-1"></i>Gold</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card p-3 text-center" style="border-left:4px solid #6b7280">
            <div style="font-size:1.6rem;font-weight:800;color:#374151">{{ $tierCounts['silver'] ?? 0 }}</div>
            <div style="font-size:.76rem;color:#6b7280;font-weight:600"><i class="bi bi-award-fill text-secondary me-1"></i>Silver</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card p-3 text-center" style="border-left:4px solid #e74c3c">
            <div style="font-size:1.6rem;font-weight:800;color:#991b1b">{{ $tierCounts['bronze'] ?? 0 }}</div>
            <div style="font-size:.76rem;color:#6b7280;font-weight:600"><i class="bi bi-award-fill text-danger me-1"></i>Bronze</div>
        </div>
    </div>
</div>

<!-- Bagian Filter dan Pencarian Member -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" id="member-filter-form" class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <div class="search-bar">
                    <i class="bi bi-search si-search"></i>
                    <input type="text" name="search" id="member-search" class="form-control"
                           placeholder="Cari nama / nomor WhatsApp..." value="{{ request('search') }}" autocomplete="off">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select name="tier" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Tier</option>
                    <option value="gold"   {{ request('tier')=='gold'  ?'selected':'' }}>Gold</option>
                    <option value="silver" {{ request('tier')=='silver'?'selected':'' }}>Silver</option>
                    <option value="bronze" {{ request('tier')=='bronze'?'selected':'' }}>Bronze</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="per_page" class="form-select" onchange="this.form.submit()">
                    @foreach([20,50,100] as $n)
                    <option value="{{ $n }}" {{ request('per_page',20)==$n?'selected':'' }}>{{ $n }} Baris</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-1">
                <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary w-100" title="Reset filter"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Kontainer hasil pencarian untuk di-update via AJAX -->
<div id="member-results">
<div class="card">
    <div class="card-header">
        <h6 class="mb-0">Daftar Member <span class="badge bg-success ms-1">{{ $customers->total() }}</span></h6>
    </div>
    <div class="table-wrapper">
        <table class="table mb-0">
            <thead>
                <tr>
                    <x-sortable-column column="id" label="#" />
                    <x-sortable-column column="full_name" label="Nama Member" />
                    <x-sortable-column column="whatsapp_number" label="WhatsApp" />
                    <x-sortable-column column="tier" label="Tier" />
                    <x-sortable-column column="total_accumulation" label="Total Belanja" />
                    <x-sortable-column column="point_balance" label="Saldo Poin" />
                    <x-sortable-column column="registered_at" label="Terdaftar" />
                    <th style="width:90px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $i => $c)
                <tr>
                    <td style="color:#9ca3af;font-size:.76rem">{{ $c->id }}</td>
                    <td>
                        <a href="{{ route('customers.show',$c) }}" style="font-weight:600;color:var(--primary-dark);text-decoration:none">
                            {{ $c->full_name }}
                        </a>
                        @if($c->address)
                        <div style="font-size:.71rem;color:#9ca3af;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px">{{ $c->address }}</div>
                        @endif
                    </td>
                    <td style="font-size:.83rem">
                        @if($c->whatsapp_number)
                        <a href="https://wa.me/{{ preg_replace('/^0/',62,$c->whatsapp_number) }}" target="_blank" style="color:var(--primary);text-decoration:none">
                            <i class="bi bi-whatsapp me-1"></i>{{ $c->whatsapp_number }}
                        </a>
                        @else
                        <span style="color:#9ca3af">-</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge-tier badge-{{ $c->tier }}">
                            <i class="bi bi-award-fill"></i> {{ ucfirst($c->tier) }}
                        </span>
                    </td>
                    <td style="font-weight:600;font-size:.855rem">Rp {{ number_format($c->total_accumulation,0,',','.') }}</td>
                    <td>
                        <span style="font-weight:700;color:var(--primary-dark)">{{ number_format($c->point_balance,0,',','.') }}</span>
                        <span style="font-size:.72rem;color:#9ca3af"> poin</span>
                    </td>
                    <td style="font-size:.8rem;color:#6b7280">{{ $c->registered_at->format('d/m/Y') }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('customers.show',$c) }}" class="btn btn-sm btn-icon btn-outline-secondary" title="Detail"><i class="bi bi-eye"></i></a>
                            @if(auth()->user()->role === 'pemilik')
                            <a href="{{ route('customers.edit',$c) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-5" style="color:#9ca3af">
                    <i class="bi bi-people" style="font-size:2.5rem;display:block;margin-bottom:10px"></i>
                    Belum ada member terdaftar
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($customers->hasPages())
    <div class="card-body border-top py-3">{{ $customers->withQueryString()->links() }}</div>
    @endif
</div>
</div><!-- Akhir #member-results -->
@endsection

<!-- Script Javascript untuk fitur live search menggunakan AJAX -->
@push('scripts')
<script>
(function () {
    const searchInput = document.getElementById('member-search');
    const form        = document.getElementById('member-filter-form');
    if (!searchInput || !form) return;

    const baseUrl = '{{ route('customers.index') }}';

    function buildParams(searchVal) {
        const data = new FormData(form);
        data.set('search', searchVal);
        return new URLSearchParams(data).toString();
    }

    async function doAjaxSearch(q) {
        const qs  = buildParams(q);
        const url = baseUrl + '?' + qs;

        history.replaceState(null, '', url);

        try {
            const res  = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const html = await res.text();
            const doc  = new DOMParser().parseFromString(html, 'text/html');
            const part = doc.getElementById('member-results');
            if (part) document.getElementById('member-results').innerHTML = part.innerHTML;
        } catch (e) {
            window.location.href = url;
        }
    }

    let timer;
    searchInput.addEventListener('input', function () {
        clearTimeout(timer);
        const q = this.value;
        timer = setTimeout(() => doAjaxSearch(q), 380);
    });
})();
</script>
@endpush