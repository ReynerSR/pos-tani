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

<div class="card">
    <div class="card-header">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Cari no. faktur / supplier..."
                    value="{{ request('search') }}">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label mb-1" style="font-size:.78rem;">Dari Tanggal</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label mb-1" style="font-size:.78rem;">Sampai Tanggal</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-1" style="font-size:.78rem;">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua</option>
                    <option value="draft" {{ request('status')==='draft'?'selected':'' }}>Draft</option>
                    <option value="approved" {{ request('status')==='approved'?'selected':'' }}>Approved</option>
                </select>
            </div>
            <div class="col-6 col-md-1">
                <label class="form-label mb-1" style="font-size:.78rem;">Row</label>
                <select name="per_page" class="form-select">
                    @foreach([10,15,20,50,100] as $n)<option value="{{ $n }}" {{ request('per_page',20)==$n?'selected':'' }}>{{ $n }}</option>@endforeach
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-1"><i class="bi bi-search"></i></button>
                <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>No. Faktur</th>
                    <th>Tanggal Beli</th>
                    <th>Supplier</th>
                    <th>Tempat Simpan</th>
                    <th>Dicatat Oleh</th>
                    <th>Status</th>
                    <th class="text-end">Total Pembelian</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $purchase)
                <tr>
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
                    <td class="text-end fw-700" style="font-size:.88rem; color:#16a34a;">
                        Rp {{ number_format($purchase->total_price, 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                        <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-sm" style="padding:3px 10px; background:#f0fdf4; color:#16a34a;">
                            <i class="bi bi-eye me-1"></i> Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">
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
</div>
@endsection
