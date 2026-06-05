@extends('layouts.app')
@section('title','Promo Produk')
@section('page_title','Promo Produk')

@section('content')
<div class="page-hdr">
    <div class="page-hdr-left">
        <h1><i class="bi bi-tag me-2" style="color:var(--primary)"></i>Promo Produk</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item active">Daftar Promo</li></ol></nav>
    </div>
    <a href="{{ route('promotions.create') }}" class="btn btn-primary px-4">
        <i class="bi bi-plus-lg me-2"></i>Tambah Promo
    </a>
</div>

<div class="alert alert-info py-2 px-3 mb-4" style="font-size:.82rem">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Catatan:</strong> Jika produk memiliki promo aktif, maka <strong>diskon member tidak berlaku</strong> pada produk tersebut. Promo nominal (Rp) digunakan sebagai pengganti.
</div>

{{-- Filter --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <div class="search-bar">
                    <i class="bi bi-search si-search"></i>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama promo / produk..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select name="status" class="form-select" style="font-size:.85rem">
                    <option value="">Semua Status</option>
                    <option value="active"   {{ request('status')=='active'  ?'selected':'' }}>Aktif Sekarang</option>
                    <option value="upcoming" {{ request('status')=='upcoming'?'selected':'' }}>Belum Mulai</option>
                    <option value="expired"  {{ request('status')=='expired' ?'selected':'' }}>Kedaluwarsa</option>
                    <option value="inactive" {{ request('status')=='inactive'?'selected':'' }}>Dinonaktifkan</option>
                </select>
            </div>
            <div class="col-6 col-md-2"><select name="per_page" class="form-select" style="font-size:.85rem">@foreach([10,15,20,50,100] as $n)<option value="{{ $n }}" {{ request('per_page',15)==$n?'selected':'' }}>{{ $n }} row</option>@endforeach</select></div>
            <div class="col-6 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search me-1"></i>Cari</button>
                <a href="{{ route('promotions.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0">Daftar Promo <span class="badge bg-success ms-1">{{ $promotions->total() }}</span></h6>
    </div>
    <div class="table-wrapper">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Promo</th>
                    <th>Produk</th>
                    <th>Potongan</th>
                    <th>Periode</th>
                    <th>Status</th>
                    <th style="width:90px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($promotions as $i => $promo)
                @php [$bg,$col] = $promo->status_color; @endphp
                <tr>
                    <td style="color:#9ca3af;font-size:.76rem">{{ $promotions->firstItem()+$i }}</td>
                    <td style="font-weight:600;font-size:.87rem">{{ $promo->promo_name }}</td>
                    <td>
                        <div style="font-weight:600;font-size:.84rem">{{ $promo->product->product_name ?? '-' }}</div>
                        <div style="font-size:.72rem;color:#9ca3af">{{ $promo->product->product_code ?? '' }}</div>
                    </td>
                    <td>
                        <span style="font-weight:700;font-size:.95rem;color:#dc2626">
                            -Rp {{ number_format($promo->discount_amount,0,',','.') }}
                        </span>
                    </td>
                    <td style="font-size:.81rem">
                        <div><i class="bi bi-calendar-event me-1" style="color:#9ca3af"></i>{{ $promo->start_date->format('d/m/Y') }}</div>
                        <div><i class="bi bi-calendar-x me-1" style="color:#9ca3af"></i>{{ $promo->end_date->format('d/m/Y') }}</div>
                    </td>
                    <td>
                        <span style="background:{{ $bg }};color:{{ $col }};font-size:.72rem;font-weight:700;padding:3px 10px;border-radius:10px">
                            {{ $promo->status_label }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('promotions.edit',$promo) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            @if(auth()->user()->role === 'pemilik')<form method="POST" action="{{ route('promotions.destroy',$promo) }}" onsubmit="return confirm('Hapus promo ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-icon btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                            </form>@endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-5" style="color:#9ca3af">
                    <i class="bi bi-tag" style="font-size:2.5rem;display:block;margin-bottom:10px"></i>
                    Belum ada promo
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($promotions->hasPages())
    <div class="card-body border-top py-3">{{ $promotions->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
