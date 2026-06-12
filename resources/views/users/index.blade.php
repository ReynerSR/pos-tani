@extends('layouts.app')
@section('title','Pengguna Sistem')
@section('page_title','Pengguna Sistem')
@section('content')
@php
$sortLink=function($column,$label) use($sort,$dir){$next=($sort===$column&&$dir==='asc')?'desc':'asc';$icon=$sort===$column?($dir==='asc'?'bi-sort-up':'bi-sort-down'):'bi-arrow-down-up';return '<a class="text-decoration-none text-muted" href="'.request()->fullUrlWithQuery(['sort'=>$column,'dir'=>$next]).'">'.$label.' <i class="bi '.$icon.'"></i></a>';};
@endphp
<!-- Header Halaman -->
<div class="page-hdr"><div class="page-hdr-left"><h1><i class="bi bi-person-gear me-2" style="color:var(--primary)"></i>Pengguna Sistem</h1><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item active">Auto logout setelah {{ env('AUTH_TIMEOUT', 15) }} menit tidak aktif</li></ol></nav></div><a href="{{ route('users.create') }}" class="btn btn-primary px-4"><i class="bi bi-person-plus me-2"></i>Tambah User</a></div>
<!-- Kartu Pencarian dan Filter -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" id="users-filter-form" class="row g-2 align-items-end" onsubmit="event.preventDefault(); go();">
            <div class="col-12 col-md-4">
                <div class="search-bar">
                    <i class="bi bi-search si-search"></i>
                    <input type="text" name="search" id="users-search" class="form-control" placeholder="Cari nama/username/email..." value="{{ request('search') }}" autocomplete="off">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <select name="role" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Role</option>
                    @foreach($roles as $value=>$label)
                    <option value="{{ $value }}" {{ request('role')===$value?'selected':'' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status')==='active'?'selected':'' }}>Aktif</option>
                    <option value="inactive" {{ request('status')==='inactive'?'selected':'' }}>Nonaktif</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="per_page" class="form-select" onchange="this.form.submit()">
                    @foreach([10,15,20,50,100] as $n)
                    <option value="{{ $n }}" {{ request('per_page',15)==$n?'selected':'' }}>{{ $n }} Baris</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2 d-flex gap-1">
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary w-100" title="Reset"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>
<!-- Kontainer Hasil Daftar Pengguna -->
<div id="users-results">
<!-- Tabel Daftar Pengguna -->
<table class="table table-hover align-middle mb-0">
    <thead class="table-light">
        <tr>
            <th style="width:50px">#</th>
            <th>{!! $sortLink('name','Pengguna') !!}</th>
            <th>{!! $sortLink('role','Role') !!}</th>
            <th style="min-width:180px">Password</th>
            <th>Status & Aktivitas</th>
            <th style="width:100px">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($users as $i=>$u)
        <tr>
            <td class="text-muted small">{{ $users->firstItem()+$i }}</td>
            <td>
                <div style="font-weight:600; font-size:.95rem;">
                    {{ $u->name }} 
                    @if($u->id===auth()->id()) <span class="badge bg-success ms-1" style="font-size:0.6rem">Anda</span>@endif
                </div>
                <div style="font-size:0.82rem; color:#4b5563; margin-top:3px;">
                    <i class="bi bi-person text-muted me-1"></i><span style="font-family:monospace;background:#f3f4f6;padding:2px 6px;border-radius:4px; margin-right:8px">{{ $u->username }}</span>
                    @if($u->email)<i class="bi bi-envelope text-muted me-1"></i>{{ $u->email }}@endif
                </div>
                <div class="small text-muted mt-1" style="font-size:0.75rem">
                    <i class="bi bi-calendar3 me-1"></i>Bergabung: {{ $u->created_at->format('d M Y') }}
                </div>
            </td>
            <td>
                <span class="badge bg-{{ $u->role==='pemilik'?'warning':($u->role==='admin'?'primary':'secondary') }}">{{ $u->role_label }}</span>
                @if($u->role==='pemilik' && $u->is_main_owner) <span class="badge bg-danger ms-1">Utama</span>@endif
            </td>
            <td>
                <span class="text-muted" style="font-size:0.75rem"><i class="bi bi-info-circle me-1"></i>Reset via Edit</span>
            </td>
            <td>
                <div style="font-size:0.82rem; margin-bottom:3px;">
                    <span class="text-muted">Akses:</span> 
                    @if($u->is_active)<span class="badge bg-success ms-1" style="font-size:0.65rem">Diizinkan</span>@else<span class="badge bg-danger ms-1" style="font-size:0.65rem">Ditangguhkan</span>@endif
                </div>
                <div style="font-size:0.82rem; margin-bottom:4px;">
                    <span class="text-muted">Sesi:</span> 
                    @if($u->is_online)<span class="badge bg-primary ms-1" style="font-size:0.65rem">Online</span>@else<span class="badge bg-secondary ms-1" style="font-size:0.65rem">Offline</span>@endif
                </div>
                <div class="small text-muted" style="font-size:0.75rem"><i class="bi bi-clock-history me-1"></i>{{ $u->last_seen_at ? $u->last_seen_at->diffForHumans() : 'Belum pernah login' }}</div>
            </td>
            <td>
                <div class="d-flex gap-1">
                    @if($u->id === auth()->id() || !$u->is_main_owner || auth()->user()->is_main_owner)
                    <a href="{{ route('users.edit',$u) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                    @endif 
                    @if($u->id!==auth()->id() && ($u->role!=='pemilik' || (auth()->user()->is_main_owner && !$u->is_main_owner)))
                    <form method="POST" action="{{ route('users.destroy',$u) }}" onsubmit="event.preventDefault(); Swal.fire({title: 'Hapus User?', text: 'Hapus user {{ $u->name }}?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal'}).then(r => { if(r.isConfirmed) this.submit(); })" class="d-inline">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-icon btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                    </form>
                    @endif
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center py-5 text-muted">Belum ada pengguna</td>
        </tr>
        @endforelse
</tbody></table></div>@if($users->hasPages())<div class="card-body border-top">{{ $users->withQueryString()->links() }}</div>@endif</div>
</div><!-- Akhir Kontainer Hasil -->
@endsection

@push('scripts')
<script>
// Fungsi inisialisasi pencarian dan filter AJAX untuk daftar pengguna
(function(){
    const si = document.getElementById('users-search');
    const f  = document.getElementById('users-filter-form');
    if(!f) return;
    const base = '{{ route('users.index') }}';
    // Mengambil parameter form pencarian
    function params(){ return new URLSearchParams(new FormData(f)).toString(); }
    // Mengirim permintaan (request) AJAX dan memperbarui DOM
    async function go(){ 
        const url=base+'?'+params(); 
        history.replaceState(null,'',url); 
        try{ 
            const r=await fetch(url,{headers:{'X-Requested-With':'XMLHttpRequest'}}); 
            const html=await r.text(); 
            const doc=new DOMParser().parseFromString(html,'text/html'); 
            const p=doc.getElementById('users-results'); 
            if(p) document.getElementById('users-results').innerHTML=p.innerHTML; 
        }catch(e){ window.location.href=url; } 
    }
    window.go = go;
    let t; 
    // Mendaftarkan event listener pada input pencarian (dengan jeda)
    if(si) {
        si.addEventListener('input',function(){ clearTimeout(t); t=setTimeout(()=>go(),380); });
    }
    // Mendaftarkan event listener pada select filter
    const selects = f.querySelectorAll('select, input[type="date"]');
    selects.forEach(el => el.addEventListener('change', go));
})();
</script>
@endpush
