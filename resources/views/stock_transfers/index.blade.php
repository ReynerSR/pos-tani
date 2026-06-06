@extends('layouts.app')
@section('title','Transfer Stok')
@section('page_title','Transfer Stok Gudang')

@section('content')
<div class="page-hdr">
    <div class="page-hdr-left">
        <h1><i class="bi bi-arrow-left-right me-2" style="color:var(--primary)"></i>Transfer Stok</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item active">Daftar Transfer</li></ol></nav>
    </div>
    <a href="{{ route('stock-transfers.create') }}" class="btn btn-primary px-4">
        <i class="bi bi-plus-lg me-2"></i>Transfer Baru
    </a>
</div>

<div class="card mb-3"><div class="card-body"><form method="GET" class="row g-2 align-items-end"><div class="col-md-5"><input type="text" name="search" class="form-control" placeholder="Cari no transfer/gudang..." value="{{ request('search') }}"></div><div class="col-md-2"><select name="per_page" class="form-select">@foreach([10,15,20,50,100] as $n)<option value="{{ $n }}" {{ request('per_page',20)==$n?'selected':'' }}>{{ $n }} row</option>@endforeach</select></div><div class="col-md-3 d-flex gap-2"><button class="btn btn-primary"><i class="bi bi-search me-1"></i>Cari</button><a href="{{ route('stock-transfers.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a></div></form></div></div>

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
@endsection