@extends('layouts.app')
@section('title','Master Produk')
@section('page_title','Master Produk')

@section('content')
@php
    $sortLink = function($column, $label) use ($sort, $dir) {
        $next = ($sort === $column && $dir === 'asc') ? 'desc' : 'asc';
        $icon = $sort === $column ? ($dir === 'asc' ? 'bi-sort-up' : 'bi-sort-down') : 'bi-arrow-down-up';
        $url = request()->fullUrlWithQuery(['sort' => $column, 'dir' => $next]);
        return '<a href="'.$url.'" class="text-decoration-none text-muted">'.$label.' <i class="bi '.$icon.'"></i></a>';
    };
@endphp
<div class="page-hdr">
    <div class="page-hdr-left">
        <h1><i class="bi bi-box-seam me-2" style="color:var(--primary)"></i>Master Produk</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item active">Daftar Produk</li></ol></nav>
    </div>
    <a href="{{ route('products.create') }}" class="btn btn-primary px-4"><i class="bi bi-plus-lg me-2"></i>Tambah Produk</a>
</div>

<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-4"><div class="search-bar"><i class="bi bi-search si-search"></i><input type="text" name="search" class="form-control" placeholder="Cari nama / kode produk..." value="{{ request('search') }}"></div></div>
            <div class="col-6 col-md-3"><select name="category" class="form-select"><option value="">Semua Kategori</option>@foreach($categories as $cat)<option value="{{ $cat }}" {{ request('category')==$cat?'selected':'' }}>{{ $cat }}</option>@endforeach</select></div>
            <div class="col-6 col-md-3"><select name="status" class="form-select"><option value="">Semua Status</option><option value="active" {{ request('status')=='active'?'selected':'' }}>Aktif</option><option value="low" {{ request('status')=='low'?'selected':'' }}>Stok Kritis</option><option value="empty" {{ request('status')=='empty'?'selected':'' }}>Stok Habis</option><option value="inactive" {{ request('status')=='inactive'?'selected':'' }}>Non-aktif</option></select></div>
            <div class="col-6 col-md-2"><select name="per_page" class="form-select"><option value="15" {{ request('per_page',15)==15?'selected':'' }}>15 row</option><option value="20" {{ request('per_page')==20?'selected':'' }}>20 row</option><option value="50" {{ request('per_page')==50?'selected':'' }}>50 row</option><option value="100" {{ request('per_page')==100?'selected':'' }}>100 row</option></select></div><div class="col-6 col-md-2 d-flex gap-2"><button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search me-1"></i>Cari</button><a href="{{ route('products.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a></div>
        </form>
    </div>
</div>
@if(auth()->user()->role === 'pemilik')
<div class="card mb-3">
    <div class="card-body py-3">
        <form method="POST" action="{{ route('products.category.update') }}" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-5"><label class="form-label">Edit Kategori Lama</label><select name="old_category" class="form-select" required>@foreach($categories as $cat)<option value="{{ $cat }}">{{ $cat }}</option>@endforeach</select></div>
            <div class="col-md-5"><label class="form-label">Nama Kategori Baru</label><input type="text" name="new_category" class="form-control" required placeholder="Contoh: PUPUK CAIR"></div>
            <div class="col-md-2"><button class="btn btn-outline-primary w-100" onclick="return confirm('Ubah kategori ini untuk semua produk terkait?')"><i class="bi bi-pencil-square me-1"></i>Update</button></div>
        </form>
    </div>
</div>
@endif

<div class="card">
    <div class="card-header"><h6 class="mb-0">Daftar Produk <span class="badge bg-success ms-1">{{ $products->total() }}</span></h6></div>
    <div class="table-wrapper">
        <table class="table mb-0">
            <thead><tr>
                <th>#</th><th>{!! $sortLink('product_code','Kode') !!}</th><th>{!! $sortLink('product_name','Nama Produk') !!}</th><th>{!! $sortLink('category','Kategori') !!}</th><th>{!! $sortLink('unit','Satuan') !!}</th><th>{!! $sortLink('selling_price','Harga Jual') !!}</th><th>{!! $sortLink('hpp','HPP') !!}</th><th>Stok Semua Tempat</th><th>{!! $sortLink('stock','Stok Toko') !!}</th><th>Status</th><th style="width:110px">Aksi</th>
            </tr></thead>
            <tbody>
                @forelse($products as $i => $p)
                @php $rowBg = $p->stock <= 0 ? '#fef2f2' : ($p->stock <= $p->minimum_stock ? '#fffbeb' : '#f0fdf4'); @endphp
                <tr style="background:{{ $rowBg }}">
                    <td class="text-muted small">{{ $products->firstItem()+$i }}</td>
                    <td><span style="font-family:monospace;font-size:.8rem;background:#f3f4f6;padding:2px 7px;border-radius:5px">{{ $p->product_code }}</span></td>
                    <td><a href="{{ route('products.show',$p) }}" style="font-weight:600;color:var(--primary-dark);text-decoration:none">{{ $p->product_name }}</a></td>
                    <td>{{ $p->category ?? '-' }}</td>
                    <td>{{ $p->unit }}</td>
                    <td><strong>Rp {{ number_format($p->selling_price,0,',','.') }}</strong></td>
                    <td>Rp {{ number_format($p->hpp,0,',','.') }}</td>
                    <td style="min-width:210px">
                        @foreach($warehouses as $wh)
                            @php $ws = $p->warehouseStocks->firstWhere('warehouse_id', $wh->id); @endphp
                            <span class="badge bg-light text-dark border me-1 mb-1">{{ $wh->code }}{{ $wh->is_store ? ' (Utama)' : '' }}: {{ number_format($ws->stock ?? 0) }}</span>
                        @endforeach
                    </td>
                    <td><strong>{{ number_format($p->stock) }}</strong></td>
                    <td>@if($p->is_active)<span class="badge bg-success">Aktif</span>@else<span class="badge bg-secondary">Nonaktif</span>@endif</td>
                    <td><div class="d-flex gap-1"><a href="{{ route('products.show',$p) }}" class="btn btn-sm btn-icon btn-outline-secondary"><i class="bi bi-eye"></i></a><a href="{{ route('products.edit',$p) }}" class="btn btn-sm btn-icon btn-outline-primary"><i class="bi bi-pencil"></i></a>@if(auth()->user()->role === 'pemilik')<form method="POST" action="{{ route('products.destroy',$p) }}" onsubmit="return confirm('Hapus produk ini?')">@csrf @method('DELETE')<button class="btn btn-sm btn-icon btn-outline-danger"><i class="bi bi-trash"></i></button></form>@endif</div></td>
                </tr>
                @empty
                <tr><td colspan="11" class="text-center py-5 text-muted"><i class="bi bi-box-seam" style="font-size:2.5rem;display:block;margin-bottom:10px"></i>Tidak ada produk ditemukan</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())<div class="card-body border-top py-3">{{ $products->withQueryString()->links() }}</div>@endif
</div>
@endsection
