@extends('layouts.app')
@section('title','Gudang')
@section('page_title','Master Gudang')

@section('content')
<div class="page-hdr">
    <div class="page-hdr-left"><h1><i class="bi bi-building me-2" style="color:var(--primary)"></i>Master Tempat Penyimpanan</h1><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item active">Toko, gudang, dan lokasi penyimpanan stok</li></ol></nav></div>
    <a href="{{ route('warehouses.create') }}" class="btn btn-primary px-4"><i class="bi bi-plus-lg me-2"></i>Tambah Lokasi</a>
</div>
<div class="card mb-3"><div class="card-body"><form method="GET" class="row g-2"><div class="col-md-4"><input type="text" name="search" class="form-control" placeholder="Cari kode/nama/lokasi..." value="{{ request('search') }}"></div><div class="col-md-2"><select name="per_page" class="form-select">@foreach([10,15,20,50,100] as $n)<option value="{{ $n }}" {{ request('per_page',20)==$n?'selected':'' }}>{{ $n }} row</option>@endforeach</select></div><div class="col-md-3 d-flex gap-2"><button class="btn btn-primary"><i class="bi bi-search me-1"></i>Cari</button><a href="{{ route('warehouses.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a></div></form></div></div>
<div class="card">
    <div class="card-header"><h6 class="mb-0">Daftar Tempat Penyimpanan <span class="badge bg-success ms-1">{{ $warehouses->total() }}</span></h6></div>
    <div class="table-wrapper"><table class="table mb-0"><thead><tr><x-sortable-column column="id" label="#" /><x-sortable-column column="code" label="Kode" /><x-sortable-column column="name" label="Nama" /><x-sortable-column column="location" label="Lokasi" /><x-sortable-column column="is_active" label="Aktif?" /><th style="width:100px">Aksi</th></tr></thead><tbody>
        @forelse($warehouses as $i => $w)
        <tr><td class="text-muted small">{{ $w->id }}</td><td><strong>{{ $w->code }}{{ $w->is_store ? ' (Utama)' : '' }}</strong></td><td>{{ $w->name }}</td><td>{{ $w->location ?? '-' }}</td><td><span class="badge bg-{{ $w->is_active ? 'success' : 'danger' }}">{{ $w->is_active ? 'Aktif' : 'Nonaktif' }}</span></td><td><div class="d-flex gap-1"><a href="{{ route('warehouses.edit',$w) }}" class="btn btn-sm btn-icon btn-outline-primary"><i class="bi bi-pencil"></i></a>@if(auth()->user()->role === 'pemilik')<form method="POST" action="{{ route('warehouses.destroy',$w) }}" onsubmit="return confirm('Hapus lokasi ini? Lokasi hanya bisa dihapus jika tidak memiliki produk/stok.')">@csrf @method('DELETE')<button class="btn btn-sm btn-icon btn-outline-danger"><i class="bi bi-trash"></i></button></form>@endif</div></td></tr>
        @empty<tr><td colspan="6" class="text-center py-5 text-muted"><i class="bi bi-building" style="font-size:2.5rem;display:block;margin-bottom:10px"></i>Belum ada lokasi penyimpanan</td></tr>@endforelse
    </tbody></table></div>
    @if($warehouses->hasPages())<div class="card-body border-top py-3">{{ $warehouses->withQueryString()->links() }}</div>@endif
</div>
@endsection
