@extends('layouts.app')
@section('title','Log Aktivitas')
@section('page_title','Log Aktivitas Sistem')

@section('content')
<div class="page-hdr">
    <div class="page-hdr-left">
        <h1><i class="bi bi-shield-check me-2" style="color:var(--primary)"></i>Log Aktivitas</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item active">Audit Trail</li></ol></nav>
    </div>
</div>

{{-- Filter --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <div class="search-bar">
                    <i class="bi bi-search si-search"></i>
                    <input type="text" name="search" class="form-control" placeholder="Cari aksi / detail..." value="{{ request('search') }}">
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
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search me-1"></i>Cari</button>
                <a href="{{ route('reports.activity') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

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
@endsection