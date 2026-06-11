@extends('layouts.app')
@section('title','Data Supplier')
@section('page_title','Data Supplier')

@section('content')
<div class="page-hdr">
    <div class="page-hdr-left">
        <h1><i class="bi bi-truck me-2" style="color:var(--primary)"></i>Data Supplier</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item active">Daftar Supplier</li></ol></nav>
    </div>
    <a href="{{ route('suppliers.create') }}" class="btn btn-primary px-4">
        <i class="bi bi-plus-lg me-2"></i>Tambah Supplier
    </a>
</div>

<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" id="suppliers-filter-form" class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <div class="search-bar">
                    <i class="bi bi-search si-search"></i>
                    <input type="text" name="search" id="suppliers-search" class="form-control" placeholder="Cari nama / kontak / telepon..." value="{{ request('search') }}" autocomplete="off">
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
                <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary w-100" title="Reset"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div id="suppliers-results">
<div class="card">
    <div class="card-header">
        <h6 class="mb-0">Daftar Supplier <span class="badge bg-success ms-1">{{ $suppliers->total() }}</span></h6>
    </div>
    <div class="table-wrapper">
        <table class="table mb-0">
            <thead>
                <tr>
                    <x-sortable-column column="id" label="#" />
                    <x-sortable-column column="name" label="Nama Supplier" />
                    <x-sortable-column column="contact_person" label="Kontak Person" />
                    <x-sortable-column column="phone" label="Telepon" />
                    <x-sortable-column column="address" label="Alamat" />
                    <th style="width:100px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $i => $s)
                <tr>
                    <td style="color:#9ca3af;font-size:.76rem">{{ $s->id }}</td>
                    <td style="font-weight:600;font-size:.87rem">{{ $s->name }}</td>
                    <td style="font-size:.83rem">{{ $s->contact_person ?? '-' }}</td>
                    <td style="font-size:.83rem">
                        @if($s->phone)
                        <a href="tel:{{ $s->phone }}" style="color:var(--primary);text-decoration:none">{{ $s->phone }}</a>
                        @else -
                        @endif
                    </td>
                    <td style="font-size:.82rem;max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $s->address ?? '-' }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            @if(auth()->user()->role === 'pemilik')
                            <a href="{{ route('suppliers.edit',$s) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            @endif
                            @if(auth()->user()->role === 'pemilik')<form method="POST" action="{{ route('suppliers.destroy',$s) }}" onsubmit="event.preventDefault(); Swal.fire({title: 'Hapus Supplier?', text: 'Hapus supplier ini?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal'}).then(r => { if(r.isConfirmed) this.submit(); })">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-icon btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                            </form>@endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-5" style="color:#9ca3af">
                    <i class="bi bi-truck" style="font-size:2.5rem;display:block;margin-bottom:10px"></i>
                    Belum ada supplier
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($suppliers->hasPages())
    <div class="card-body border-top py-3">{{ $suppliers->withQueryString()->links() }}</div>
    @endif
</div>
</div>{{-- #suppliers-results --}}
@endsection

@push('scripts')
<script>
(function(){
    const si=document.getElementById('suppliers-search');
    const f=document.getElementById('suppliers-filter-form');
    if(!si||!f) return;
    const base='{{ route('suppliers.index') }}';
    function params(q){ const d=new FormData(f); d.set('search',q); return new URLSearchParams(d).toString(); }
    async function go(q){ const url=base+'?'+params(q); history.replaceState(null,'',url); try{ const r=await fetch(url,{headers:{'X-Requested-With':'XMLHttpRequest'}}); const html=await r.text(); const doc=new DOMParser().parseFromString(html,'text/html'); const p=doc.getElementById('suppliers-results'); if(p) document.getElementById('suppliers-results').innerHTML=p.innerHTML; }catch(e){ window.location.href=url; } }
    let t; si.addEventListener('input',function(){ clearTimeout(t); const q=this.value; t=setTimeout(()=>go(q),380); });
})();
</script>
@endpush
