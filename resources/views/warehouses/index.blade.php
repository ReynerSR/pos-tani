@extends('layouts.app')
@section('title','Gudang')
@section('page_title','Data Gudang')

@section('content')
<!-- Header Halaman -->
<div class="page-hdr">
    <div class="page-hdr-left"><h1><i class="bi bi-building me-2" style="color:var(--primary)"></i>Data Gudang</h1><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item active">Toko, gudang, dan lokasi penyimpanan stok</li></ol></nav></div>
    <a href="{{ route('warehouses.create') }}" class="btn btn-primary px-4"><i class="bi bi-plus-lg me-2"></i>Tambah Lokasi</a>
</div>

<!-- Kartu Filter dan Pencarian -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" id="warehouses-filter-form" class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <div class="search-bar">
                    <i class="bi bi-search si-search"></i>
                    <input type="text" name="search" id="warehouses-search" class="form-control" placeholder="Cari kode/nama/lokasi..." value="{{ request('search') }}" autocomplete="off">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <select name="per_page" class="form-select" onchange="this.form.submit()">
                    @foreach([20,50,100] as $n)
                        <option value="{{ $n }}" {{ request('per_page',20)==$n?'selected':'' }}>{{ $n }} baris</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-1">
                <a href="{{ route('warehouses.index') }}" class="btn btn-outline-secondary w-100" title="Reset"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Kontainer Hasil Daftar Gudang -->
<div id="warehouses-results">
    <!-- Kartu Tabel Daftar Gudang -->
    <div class="card">
        <div class="card-header"><h6 class="mb-0">Daftar Tempat Penyimpanan <span class="badge bg-success ms-1">{{ $warehouses->total() }}</span></h6></div>
        <div class="table-wrapper"><table class="table mb-0"><thead><tr><x-sortable-column column="id" label="#" /><x-sortable-column column="code" label="Kode" /><x-sortable-column column="name" label="Nama" /><x-sortable-column column="location" label="Lokasi" /><x-sortable-column column="is_active" label="Aktif?" /><th style="width:100px">Aksi</th></tr></thead><tbody>
            @forelse($warehouses as $i => $w)
            <tr><td class="text-muted small">{{ $w->id }}</td><td><strong>{{ $w->code }}{{ $w->is_store ? ' (Utama)' : '' }}</strong></td><td>{{ $w->name }}</td><td>{{ $w->location ?? '-' }}</td><td><span class="badge bg-{{ $w->is_active ? 'success' : 'danger' }}">{{ $w->is_active ? 'Aktif' : 'Nonaktif' }}</span></td><td><div class="d-flex gap-1"><a href="{{ route('warehouses.edit',$w) }}" class="btn btn-sm btn-icon btn-outline-primary"><i class="bi bi-pencil"></i></a>@if(auth()->user()->role === 'pemilik')<form method="POST" action="{{ route('warehouses.destroy',$w) }}" onsubmit="event.preventDefault(); Swal.fire({title: 'Hapus Lokasi?', text: 'Lokasi hanya bisa dihapus jika tidak memiliki produk/stok.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal'}).then(r => { if(r.isConfirmed) this.submit(); })">@csrf @method('DELETE')<button class="btn btn-sm btn-icon btn-outline-danger"><i class="bi bi-trash"></i></button></form>@endif</div></td></tr>
            @empty<tr><td colspan="6" class="text-center py-5 text-muted"><i class="bi bi-building" style="font-size:2.5rem;display:block;margin-bottom:10px"></i>Belum ada lokasi penyimpanan</td></tr>@endforelse
        </tbody></table></div>
        @if($warehouses->hasPages())<div class="card-body border-top py-3">{{ $warehouses->withQueryString()->links() }}</div>@endif
    </div>
</div><!-- Akhir Kontainer Hasil -->
@endsection

@push('scripts')
<script>
// Fungsi inisialisasi pencarian AJAX untuk daftar gudang
(function(){
    const si=document.getElementById('warehouses-search');
    const f=document.getElementById('warehouses-filter-form');
    if(!si||!f) return;
    const base='{{ route('warehouses.index') }}';
    function params(q){ const d=new FormData(f); d.set('search',q); return new URLSearchParams(d).toString(); }
    async function go(q){ const url=base+'?'+params(q); history.replaceState(null,'',url); try{ const r=await fetch(url,{headers:{'X-Requested-With':'XMLHttpRequest'}}); const html=await r.text(); const doc=new DOMParser().parseFromString(html,'text/html'); const p=doc.getElementById('warehouses-results'); if(p) document.getElementById('warehouses-results').innerHTML=p.innerHTML; }catch(e){ window.location.href=url; } }
    let t; si.addEventListener('input',function(){ clearTimeout(t); const q=this.value; t=setTimeout(()=>go(q),380); });
})();
</script>
@endpush
