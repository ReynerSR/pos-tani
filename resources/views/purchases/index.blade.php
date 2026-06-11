@extends('layouts.app')
@section('title', 'Pembelian & Restok')
@section('page-title', 'Pembelian & Restok')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Pembelian</li>
        </ol>
    </nav>
    <a href="{{ route('purchases.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
        <i class="bi bi-plus-lg"></i> Input Pembelian Baru
    </a>
</div>

<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" id="purchases-filter-form" class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <div class="search-bar">
                    <i class="bi bi-search si-search"></i>
                    <input type="text" name="search" id="purchases-search" class="form-control" placeholder="Cari no. faktur / supplier..." value="{{ request('search') }}" autocomplete="off">
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
                <label class="form-label mb-1 small">Status</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua</option>
                    <option value="draft" {{ request('status')==='draft'?'selected':'' }}>Draft</option>
                    <option value="approved" {{ request('status')==='approved'?'selected':'' }}>Approved</option>
                </select>
            </div>
            <div class="col-6 col-md-1">
                
                <select name="per_page" class="form-select" onchange="this.form.submit()">
                    <option value="20"{{ request('per_page')==20?'selected':'' }}>20 Baris</option>
                    <option value="50" {{ request('per_page')==50?'selected':'' }}>50 Baris</option>
                    <option value="100" {{ request('per_page')==100?'selected':'' }}>100 Baris</option>
                </select>
            </div>
            <div class="col-6 col-md-1 d-flex gap-1">
                <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary w-100" title="Reset"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div id="purchases-results">
<div class="card">
    <div class="card-header"><h6 class="mb-0">Daftar Pembelian <span class="badge bg-success ms-1">{{ $purchases->total() }}</span></h6></div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <x-sortable-column column="id" label="#" />
                    <x-sortable-column column="invoice_number" label="No. Faktur" />
                    <x-sortable-column column="purchase_date" label="Tanggal Beli" />
                    <x-sortable-column column="supplier_name" label="Supplier" />
                    <x-sortable-column column="warehouse_name" label="Tempat Simpan" />
                    <x-sortable-column column="user_name" label="Dicatat Oleh" />
                    <x-sortable-column column="status" label="Status" />
                    @if(auth()->user()->role === 'pemilik')
                    <x-sortable-column column="total_price" label="Total Pembelian" align="right" />
                    @endif
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $purchase)
                <tr>
                    <td style="color:#9ca3af;font-size:.76rem">{{ $purchase->id }}</td>
                    <td class="fw-700" style="font-size:.85rem; color:#16a34a;">{{ $purchase->invoice_number }}</td>
                    <td style="font-size:.85rem;">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y') }}</td>
                    <td style="font-size:.85rem;">{{ $purchase->supplier->name }}</td>
                    <td style="font-size:.85rem;">{{ $purchase->warehouse ? $purchase->warehouse->code . ($purchase->warehouse->is_store ? ' (Utama)' : '') : '-' }}</td>
                    <td style="font-size:.85rem;">{{ $purchase->user->name }}</td>
                    <td style="font-size:.85rem;">
                        @if(($purchase->status ?? 'approved') === 'draft')
                            <span class="badge bg-warning text-dark">Draft</span>
                        @else
                            <span class="badge bg-success">Approved</span>
                        @endif
                    </td>
                    @if(auth()->user()->role === 'pemilik')
                    <td class="text-end fw-700" style="font-size:.88rem; color:#16a34a;">Rp {{ number_format($purchase->total_price, 0, ',', '.') }}</td>
                    @endif
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                            <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-sm" style="padding:3px 10px; background:#f0fdf4; color:#16a34a;" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if(auth()->user()->role === 'pemilik')
                                <a href="{{ route('purchases.edit', $purchase) }}" class="btn btn-sm btn-outline-primary" style="padding:3px 8px;" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('purchases.destroy', $purchase) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-outline-danger" style="padding:3px 8px;" title="Hapus" onclick="event.preventDefault(); Swal.fire({title: 'Hapus Pembelian?', text: 'Yakin ingin menghapus pembelian ini? Stok dan HPP akan direvert sesuai kondisi awal.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal'}).then(r => { if(r.isConfirmed) this.closest('form').submit(); })">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ auth()->user()->role === 'pemilik' ? 9 : 8 }}" class="text-center text-muted py-5">
                        <i class="bi bi-receipt" style="font-size:2rem; color:#d1d5db;"></i>
                        <div class="mt-2">Belum ada data pembelian</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($purchases->hasPages())
    <div class="card-footer d-flex align-items-center justify-content-between flex-wrap gap-2" style="background:#fff; border-top:1px solid #f3f4f6;">
        <small class="text-muted">Menampilkan {{ $purchases->firstItem() }}–{{ $purchases->lastItem() }} dari {{ $purchases->total() }}</small>
        {{ $purchases->links('vendor.pagination.bootstrap-5') }}
    </div>
    @endif
</div>{{-- #purchases-results --}}
</div>
@endsection

@push('scripts')
<script>
(function(){
    const si=document.getElementById('purchases-search');
    const f=document.getElementById('purchases-filter-form');
    if(!si||!f) return;
    const base='{{ route('purchases.index') }}';
    function params(q){ const d=new FormData(f); d.set('search',q); return new URLSearchParams(d).toString(); }
    async function go(q){ const url=base+'?'+params(q); history.replaceState(null,'',url); try{ const r=await fetch(url,{headers:{'X-Requested-With':'XMLHttpRequest'}}); const html=await r.text(); const doc=new DOMParser().parseFromString(html,'text/html'); const p=doc.getElementById('purchases-results'); if(p) document.getElementById('purchases-results').innerHTML=p.innerHTML; }catch(e){ window.location.href=url; } }
    let t; si.addEventListener('input',function(){ clearTimeout(t); const q=this.value; t=setTimeout(()=>go(q),380); });
})();
</script>
@endpush
