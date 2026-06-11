@extends('layouts.app')
@section('title','Detail Promo')
@section('page_title','Detail Diskon & Promo')

@section('content')
<div class="page-hdr">
    <div class="page-hdr-left">
        <h1><i class="bi bi-tag me-2" style="color:var(--primary)"></i>{{ $promotion->promo_name }}</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('promotions.index') }}">Diskon & Promo</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol></nav>
    </div>
    <div class="d-flex gap-2">
        @if(auth()->user()->role === 'pemilik')
        <a href="{{ route('promotions.edit',$promotion) }}" class="btn btn-outline-primary"><i class="bi bi-pencil me-2"></i>Edit</a>
        @endif
        <a href="{{ route('promotions.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Kembali</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-5">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0">Informasi Promo</h6>
            </div>
            <div class="card-body">
                @php [$bg, $col] = $promotion->status_color; @endphp
                <div class="mb-4 text-center">
                    <span style="background:{{ $bg }};color:{{ $col }};font-size:.85rem;font-weight:700;padding:5px 12px;border-radius:15px">
                        {{ $promotion->status_label }}
                    </span>
                </div>
                
                <table class="table table-borderless table-sm mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted" style="width: 140px;">Nama Promo</td>
                            <td style="font-weight:600;">{{ $promotion->promo_name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Potongan Harga</td>
                            <td><span style="font-weight:700;color:#dc2626;font-size:1.1rem;">-Rp {{ number_format($promotion->discount_amount,0,',','.') }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Mulai Berlaku</td>
                            <td style="font-weight:600;"><i class="bi bi-calendar-event text-muted me-1"></i>{{ $promotion->start_date->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Berakhir Pada</td>
                            <td style="font-weight:600;"><i class="bi bi-calendar-x text-muted me-1"></i>{{ $promotion->end_date->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Berlaku Untuk</td>
                            <td>
                                @if(empty($promotion->eligible_tiers))
                                    <span class="badge bg-secondary">Semua Pelanggan</span>
                                @else
                                    <div class="d-flex flex-wrap gap-1">
                                    @foreach($promotion->eligible_tiers as $tier)
                                        @if($tier === 'bronze') <span class="badge" style="background:#fee2e2;color:#991b1b;border:1px solid #fca5a5">Bronze</span>
                                        @elseif($tier === 'silver') <span class="badge" style="background:#f3f4f6;color:#4b5563;border:1px solid #d1d5db">Silver</span>
                                        @elseif($tier === 'gold') <span class="badge" style="background:#fef3c7;color:#92400e;border:1px solid #fcd34d">Gold</span>
                                        @endif
                                    @endforeach
                                    </div>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Dibuat Oleh</td>
                            <td style="font-weight:600;">{{ $promotion->createdBy->name ?? '-' }}</td>
                        </tr>
                        @if($promotion->notes)
                        <tr>
                            <td class="text-muted">Keterangan Tambahan</td>
                            <td style="font-size: 0.9rem;">{{ $promotion->notes }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-7">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0">Produk yang Didiskon</h6>
            </div>
            <div class="card-body text-center d-flex flex-column justify-content-center align-items-center py-5">
                @if($promotion->product)
                    <div style="width:80px;height:80px;background:var(--primary-pale);border-radius:20px;display:flex;align-items:center;justify-content:center;font-size:36px;color:var(--primary);margin:0 auto 20px">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <h4 style="font-weight:700;margin-bottom:8px">{{ $promotion->product->product_name }}</h4>
                    <span style="font-family:monospace;font-size:.9rem;background:#f3f4f6;padding:4px 12px;border-radius:8px">{{ $promotion->product->product_code }}</span>
                    
                    <div class="mt-4 pt-4 border-top w-100 px-4" style="max-width: 400px; text-align:left;">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Harga Normal</span>
                            <span style="text-decoration: line-through; color:#6b7280;">Rp {{ number_format($promotion->product->selling_price, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Potongan Promo</span>
                            <span style="color:#dc2626;">-Rp {{ number_format($promotion->discount_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between pt-2 border-top">
                            <span style="font-weight:bold;">Harga Setelah Diskon</span>
                            <span style="font-weight:bold; color:var(--primary-dark); font-size: 1.1rem;">
                                Rp {{ number_format(max(0, $promotion->product->selling_price - $promotion->discount_amount), 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('products.show', $promotion->product) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-box-arrow-up-right me-2"></i>Lihat Data Produk</a>
                    </div>
                @else
                    <div class="text-muted"><i class="bi bi-exclamation-triangle" style="font-size: 2rem; display:block; margin-bottom: 10px;"></i> Data produk telah dihapus atau tidak ditemukan.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
