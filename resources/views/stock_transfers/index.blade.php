@extends('layouts.app')
@section('title','Transfer Stok')
@section('page_title','Transfer Stok Gudang')

@section('content')
<!-- Header Halaman -->
<div class="page-hdr">
    <div class="page-hdr-left">
        <h1><i class="bi bi-arrow-left-right me-2" style="color:var(--primary)"></i>Transfer Stok</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item active">Daftar Transfer</li></ol></nav>
    </div>
    <a href="{{ route('stock-transfers.create') }}" class="btn btn-primary px-4">
        <i class="bi bi-plus-lg me-2"></i>Transfer Baru
    </a>
</div>

<!-- Kartu Filter dan Pencarian -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" id="stock-transfers-filter-form" class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <div class="search-bar">
                    <i class="bi bi-search si-search"></i>
                    <input type="text" name="search" id="stock-transfers-search" class="form-control" placeholder="Cari no transfer/gudang..." value="{{ request('search') }}" autocomplete="off">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-1 small">Dari Tanggal</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" onchange="this.form.submit()">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-1 small">Sampai Tanggal</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" onchange="this.form.submit()">
            </div>
            <div class="col-6 col-md-2">
                <select name="per_page" class="form-select" onchange="this.form.submit()">
                    @foreach([20,50,100] as $n)
                        <option value="{{ $n }}" {{ request('per_page',20)==$n?'selected':'' }}>{{ $n }} Baris</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-1 d-flex gap-1">
                <a href="{{ route('stock-transfers.index') }}" class="btn btn-outline-secondary w-100" title="Reset"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Kontainer Hasil Riwayat Transfer Stok -->
<div id="stock-transfers-results">
    <!-- Kartu Tabel Daftar Transfer Stok -->
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">Riwayat Transfer Stok <span class="badge bg-success ms-1">{{ $transfers->total() }}</span></h6>
        </div>
        <div class="table-wrapper">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <x-sortable-column column="id" label="#" />
                        <x-sortable-column column="transfer_number" label="No. Transfer" />
                        <x-sortable-column column="from_warehouse_name" label="Dari Gudang" />
                        <x-sortable-column column="to_warehouse_name" label="Ke Gudang" />
                        <x-sortable-column column="product_details" label="Detail Produk" />
                        <x-sortable-column column="transfer_date" label="Tanggal" />
                        <x-sortable-column column="status" label="Status" />
                    </tr>
                </thead>
                <tbody>
                    @forelse($transfers as $i => $t)
                    <tr>
                        <td style="color:#9ca3af;font-size:.76rem">{{ $t->id }}</td>
                        <td style="font-weight:600;font-size:.87rem">{{ $t->transfer_number }}</td>
                        <td style="font-size:.85rem">{{ $t->fromWarehouse->name }}</td>
                        <td style="font-size:.85rem">{{ $t->toWarehouse->name }}</td>
                        <td style="font-size:.8rem">
                            @foreach($t->details as $d)
                                <div><strong>{{ $d->product->product_name ?? '-' }}</strong> x{{ $d->qty }}</div>
                            @endforeach
                            @if($t->notes)<div class="small text-muted">Catatan: {{ $t->notes }}</div>@endif
                        </td>
                        <td style="font-size:.85rem">{{ $t->transfer_date->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge bg-{{ $t->status === 'completed' ? 'success' : ($t->status === 'pending' ? 'warning' : 'danger') }}">
                                {{ ucfirst($t->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-5" style="color:#9ca3af">
                        <i class="bi bi-arrow-left-right" style="font-size:2.5rem;display:block;margin-bottom:10px"></i>
                        Belum ada transfer stok
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transfers->hasPages())
        <div class="card-body border-top py-3">{{ $transfers->withQueryString()->links() }}</div>
        @endif
    </div>
</div><!-- Akhir Kontainer Hasil -->
@endsection

@push('scripts')
<script>
// Fungsi inisialisasi pencarian AJAX untuk daftar transfer stok
(function(){
    const si=document.getElementById('stock-transfers-search');
    const f=document.getElementById('stock-transfers-filter-form');
    if(!si||!f) return;
    const base='{{ route('stock-transfers.index') }}';
    function params(q){ const d=new FormData(f); d.set('search',q); return new URLSearchParams(d).toString(); }
    async function go(q){ const url=base+'?'+params(q); history.replaceState(null,'',url); try{ const r=await fetch(url,{headers:{'X-Requested-With':'XMLHttpRequest'}}); const html=await r.text(); const doc=new DOMParser().parseFromString(html,'text/html'); const p=doc.getElementById('stock-transfers-results'); if(p) document.getElementById('stock-transfers-results').innerHTML=p.innerHTML; }catch(e){ window.location.href=url; } }
    let t; si.addEventListener('input',function(){ clearTimeout(t); const q=this.value; t=setTimeout(()=>go(q),380); });
})();
</script>
@endpush