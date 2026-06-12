@extends('layouts.app')
@section('title','Detail Produk')
@section('page_title','Detail Produk')

@section('content')
<!-- Header Halaman -->
<div class="page-hdr">
    <div class="page-hdr-left">
        <h1><i class="bi bi-box-seam me-2" style="color:var(--primary)"></i>{{ $product->product_name }}</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Master Produk</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol></nav>
    </div>
    <div class="d-flex gap-2">
        @if(auth()->user()->role === 'pemilik')
        <a href="{{ route('products.edit',$product) }}" class="btn btn-outline-primary"><i class="bi bi-pencil me-2"></i>Edit</a>
        @endif
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Kembali</a>
    </div>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Informasi Produk -->
    <div class="col-12 col-lg-4">
        <!-- Kartu Profil Produk -->
        <div class="card">
            <div class="card-body text-center py-4">
                <div style="width:72px;height:72px;background:var(--primary-pale);border-radius:18px;display:flex;align-items:center;justify-content:center;font-size:32px;color:var(--primary);margin:0 auto 16px">
                    <i class="bi bi-box-seam"></i>
                </div>
                <h5 style="font-weight:700;margin-bottom:4px">{{ $product->product_name }}</h5>
                <span style="font-family:monospace;font-size:.82rem;background:#f3f4f6;padding:3px 10px;border-radius:6px">{{ $product->product_code }}</span>
                <div class="mt-3">
                    @if($product->stock <= 0)
                        <span class="badge-stock-empty">Stok Habis</span>
                    @elseif($product->stock <= $product->minimum_stock)
                        <span class="badge-stock-low">Stok Kritis</span>
                    @else
                        <span class="badge-stock-ok">Stok Aman</span>
                    @endif
                    &nbsp;
                    @if($product->is_active)
                        <span style="background:#d1fae5;color:#065f46;font-size:.7rem;font-weight:700;padding:3px 9px;border-radius:10px">Aktif</span>
                    @else
                        <span style="background:#f3f4f6;color:#6b7280;font-size:.7rem;font-weight:700;padding:3px 9px;border-radius:10px">Non-aktif</span>
                    @endif
                </div>
            </div>
            <div class="card-body border-top py-3">
                @php $rows = [
                    ['Kategori','category', $product->category ?? '-'],
                    ['Satuan','unit', $product->unit],
                ]; @endphp
                @foreach($rows as [$label,,$val])
                <div class="d-flex justify-content-between py-1" style="font-size:.82rem">
                    <span style="color:#6b7280">{{ $label }}</span>
                    <span style="font-weight:600">{{ $val }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Kartu Informasi Harga -->
        <div class="card mt-3">
            <div class="card-header"><h6>Informasi Harga</h6></div>
            <div class="card-body">
                <div class="d-flex justify-content-between py-1" style="font-size:.83rem">
                    <span style="color:#6b7280">Harga Jual</span>
                    <span style="font-weight:700;color:var(--primary-dark)">Rp {{ number_format($product->selling_price,0,',','.') }}</span>
                </div>
                @if(auth()->user()->canAccessHPP())
                <div class="d-flex justify-content-between py-1" style="font-size:.83rem">
                    <span style="color:#6b7280">HPP</span>
                    <span style="font-weight:700;color:#92400e">Rp {{ number_format($product->hpp,0,',','.') }}</span>
                </div>
                <div class="d-flex justify-content-between py-1" style="font-size:.83rem">
                    <span style="color:#6b7280">Margin</span>
                    @php $margin = $product->selling_price - $product->hpp; @endphp
                    <span style="font-weight:700;color:{{ $margin >= 0 ? 'var(--primary)' : '#dc2626' }}">
                        Rp {{ number_format($margin,0,',','.') }}
                        ({{ $product->hpp > 0 ? round(($margin/$product->hpp)*100,1) : 0 }}%)
                    </span>
                </div>
                @endif
            </div>
        </div>

        <!-- Kartu Informasi Stok -->
        <div class="card mt-3">
            <div class="card-header"><h6>Informasi Stok</h6></div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div style="font-size:2.5rem;font-weight:800;color:{{ $product->stock<=0?'#dc2626':($product->stock<=$product->minimum_stock?'#f39c12':'var(--primary)') }}">
                        {{ number_format($product->stock) }}
                    </div>
                    <div style="font-size:.78rem;color:#6b7280">{{ $product->unit }} tersisa</div>
                </div>
                <div class="progress mb-2" style="height:8px">
                    @php $pct = $product->minimum_stock > 0 ? min(100,round(($product->stock/$product->minimum_stock)*100)) : 100; @endphp
                    <div class="progress-bar" style="width:{{ $pct }}%;background:{{ $product->stock<=0?'#dc2626':($product->stock<=$product->minimum_stock?'#f39c12':'#27ae60') }}"></div>
                </div>
                <div style="font-size:.76rem;color:#9ca3af;text-align:right">Stok min: {{ $product->minimum_stock }}</div>
                <hr>
                <div style="font-weight:700;font-size:.84rem;margin-bottom:8px">Stok per Tempat Penyimpanan</div>
                @forelse($product->warehouseStocks as $ws)
                <div class="d-flex justify-content-between py-1" style="font-size:.82rem">
                    <span>{{ $ws->warehouse->code ?? '-' }}{{ ($ws->warehouse && $ws->warehouse->is_store) ? ' (Utama)' : '' }}</span>
                    <strong>{{ number_format($ws->stock) }} {{ $product->unit }}</strong>
                </div>
                @empty
                <div class="text-muted small">Belum ada stok per gudang. Input melalui Pembelian/Restock, Transfer Gudang, atau Stock Opname.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Riwayat Penjualan -->
    <div class="col-12 col-lg-8">
        <!-- Kartu Riwayat Penjualan Terakhir -->
        <div class="card">
            <div class="card-header"><h6><i class="bi bi-clock-history me-2" style="color:var(--primary)"></i>Riwayat Penjualan Terakhir</h6></div>
            <div class="table-wrapper">
                <table class="table mb-0" style="font-size:.82rem">
                    <thead><tr><th>Tanggal</th><th>No. Transaksi</th><th>Kasir</th><th>Qty</th><th>Harga Satuan</th><th>Subtotal</th></tr></thead>
                    <tbody>
                        @forelse($recentTransactions as $td)
                        <tr>
                            <td>{{ $td->transaction->transaction_date->format('d/m/Y H:i') }}</td>
                            <td><a href="{{ route('kasir.show',$td->transaction) }}" style="color:var(--primary);font-weight:600;text-decoration:none">{{ $td->transaction->transaction_number }}</a></td>
                            <td>{{ $td->transaction->cashier->name ?? '-' }}</td>
                            <td>{{ $td->qty }} {{ $product->unit }}</td>
                            <td>Rp {{ number_format($td->final_unit_price,0,',','.') }}</td>
                            <td style="font-weight:600">Rp {{ number_format($td->subtotal,0,',','.') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-4" style="color:#9ca3af">Belum ada riwayat penjualan</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
