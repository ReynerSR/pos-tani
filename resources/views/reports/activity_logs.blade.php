@extends('layouts.app')
@section('title','Log Aktivitas')
@section('page_title','Log Aktivitas Sistem')

@section('content')
<!-- Header Halaman -->
<div class="page-hdr">
    <div class="page-hdr-left">
        <h1><i class="bi bi-shield-check me-2" style="color:var(--primary)"></i>Log Aktivitas</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item active">Audit Trail</li></ol></nav>
    </div>
</div>

<!-- Kartu Filter dan Pencarian -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" id="activity-logs-filter-form" class="row g-2 align-items-end" onsubmit="event.preventDefault(); go();">
            <div class="col-12 col-md-4">
                <div class="search-bar">
                    <i class="bi bi-search si-search"></i>
                    <input type="text" name="search" id="activity-logs-search" class="form-control" placeholder="Cari aksi / detail..." value="{{ request('search') }}" autocomplete="off">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-1 small">Dari Tanggal</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-1 small">Sampai Tanggal</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-1 small">Filter Aksi</label>
                <select name="action_filter" class="form-select">
                    <option value="">Semua Aksi</option>
                    @foreach($actions as $action)
                    <option value="{{ $action }}" {{ request('action_filter') == $action ? 'selected' : '' }}>{{ $action }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2 d-flex gap-1">
                <a href="{{ route('reports.activity') }}" class="btn btn-outline-secondary w-100" title="Reset"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Kontainer Hasil Log Aktivitas -->
<div id="activity-logs-results">
<!-- Kartu Daftar Riwayat Aktivitas -->
<div class="card">
    <div class="card-header">
        <h6 class="mb-0">Riwayat Aktivitas <span class="badge bg-success ms-1">{{ $logs->total() }}</span></h6>
    </div>
    <div class="table-wrapper">
        <table class="table mb-0" style="font-size:.82rem">
            <thead>
                <tr>
                    <x-sortable-column column="created_at" label="Waktu" />
                    <th>Pengguna</th>
                    <x-sortable-column column="action" label="Aksi" />
                    <th>Detail</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td style="white-space:nowrap;color:#6b7280">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                    <td>
                        @if($log->user)
                        <div style="font-weight:600">{{ $log->user->name }}</div>
                        <div style="font-size:.72rem;color:#9ca3af">{{ $log->user->role_label }}</div>
                        @else
                        <span style="color:#9ca3af">System</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $actionColor = match(true) {
                                str_starts_with($log->action,'LOGIN')     => ['#d1fae5','#065f46'],
                                str_starts_with($log->action,'LOGOUT')    => ['#f3f4f6','#374151'],
                                str_starts_with($log->action,'DELETE')    => ['#fee2e2','#991b1b'],
                                str_starts_with($log->action,'TRANSACTION')=>['#dbeafe','#1e40af'],
                                str_starts_with($log->action,'PURCHASE')  => ['#fef3c7','#92400e'],
                                str_starts_with($log->action,'STOCK')     => ['#ede9fe','#5b21b6'],
                                default                                    => ['#f3f4f6','#374151'],
                            };
                        @endphp
                        <span style="background:{{ $actionColor[0] }};color:{{ $actionColor[1] }};font-size:.72rem;font-weight:700;padding:3px 9px;border-radius:10px;white-space:nowrap">
                            {{ $log->action }}
                        </span>
                    </td>
                    <td style="max-width:300px;color:#4b5563">{{ $log->detail ?? '-' }}</td>
                    <td style="font-family:monospace;font-size:.76rem;color:#9ca3af">{{ $log->ip_address ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-5" style="color:#9ca3af">
                    <i class="bi bi-shield-check" style="font-size:2rem;display:block;margin-bottom:8px"></i>
                    Tidak ada log aktivitas
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div class="card-body border-top py-3">{{ $logs->withQueryString()->links() }}</div>
    @endif
</div>
</div><!-- Akhir Kontainer Hasil Log Aktivitas -->
@endsection

@push('scripts')
<script>
// Fungsi inisialisasi pencarian dan filter AJAX untuk log aktivitas
(function(){
    const si = document.getElementById('activity-logs-search');
    const f  = document.getElementById('activity-logs-filter-form');
    if(!f) return;
    const base = '{{ route('reports.activity') }}';
    // Fungsi untuk mengambil parameter pencarian dari form
    function params(){ return new URLSearchParams(new FormData(f)).toString(); }
    // Fungsi untuk mengirim permintaan dan memperbarui DOM
    async function go(){ 
        const url=base+'?'+params(); 
        history.replaceState(null,'',url); 
        try{ 
            const r=await fetch(url,{headers:{'X-Requested-With':'XMLHttpRequest'}}); 
            const html=await r.text(); 
            const doc=new DOMParser().parseFromString(html,'text/html'); 
            const p=doc.getElementById('activity-logs-results'); 
            if(p) document.getElementById('activity-logs-results').innerHTML=p.innerHTML; 
        }catch(e){ window.location.href=url; } 
    }
    window.go = go;
    let t; 
    // Mendaftarkan event listener pada input pencarian (dengan jeda)
    if(si) {
        si.addEventListener('input',function(){ clearTimeout(t); t=setTimeout(()=>go(),380); });
    }
    // Mendaftarkan event listener pada select dan input tanggal
    const selects = f.querySelectorAll('select, input[type="date"]');
    selects.forEach(el => el.addEventListener('change', go));
})();
</script>
@endpush