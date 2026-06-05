@extends('layouts.app')
@section('title','Detail Transaksi')
@section('page_title','Detail Transaksi')

@section('content')
<div class="page-hdr">
    <div class="page-hdr-left">
        <h1><i class="bi bi-receipt me-2" style="color:var(--primary)"></i>Detail Transaksi</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('kasir.history') }}">Riwayat</a></li><li class="breadcrumb-item active">{{ $transaction->transaction_number }}</li></ol></nav>
    </div>
    <div class="d-flex gap-2">
        @if($transaction->payment_status === 'paid')
            <a href="{{ route('kasir.edit',$transaction) }}" class="btn btn-outline-warning"><i class="bi bi-pencil me-2"></i>Edit Nota</a>
            @if(auth()->user()->role === 'pemilik')
            <form method="POST" action="{{ route('kasir.destroy',$transaction) }}" onsubmit="return confirm('Hapus/void transaksi ini?')">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger"><i class="bi bi-trash me-2"></i>Delete</button>
            </form>
            @endif
        @endif
        <a href="{{ route('kasir.receipt',$transaction) }}" class="btn btn-outline-primary"><i class="bi bi-printer me-2"></i>Cetak Struk</a>
        <a href="{{ $backUrl ?: route('kasir.history') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Kembali</a>
    </div>
</div>

@if($transaction->payment_status !== 'paid')
<div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>Transaksi ini sudah dihapus/void. Data tetap ditampilkan untuk audit, tetapi tidak dapat diedit. Stok dan poin member sudah dikoreksi.</div>
@endif

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><h6>Info Transaksi</h6></div>
            <div class="card-body" style="font-size:.86rem">
                <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">No. Transaksi</span><strong>{{ $transaction->transaction_number }}</strong></div>
                <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">Tanggal</span><strong>{{ $transaction->transaction_date->format('d/m/Y H:i') }}</strong></div>
                <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">Kasir</span><strong>{{ $transaction->cashier->name ?? '-' }}</strong></div>
                <div class="d-flex justify-content-between py-1"><span class="text-muted">Status</span><strong>{{ $transaction->payment_status === 'paid' ? 'Lunas' : 'Dihapus/Void' }}</strong></div>
            </div>
        </div>
        @if($transaction->customer)
        <div class="card mb-3">
            <div class="card-header"><h6>Member</h6></div>
            <div class="card-body" style="font-size:.86rem">
                <a href="{{ route('customers.show',$transaction->customer) }}" style="font-weight:700;color:var(--primary-dark);text-decoration:none">{{ $transaction->customer->full_name }}</a>
                <div class="mt-1"><span class="badge-tier badge-{{ $transaction->customer->tier }}">{{ ucfirst($transaction->customer->tier) }}</span></div>
                <div class="mt-2 text-muted"><i class="bi bi-whatsapp me-1"></i>{{ $transaction->customer->whatsapp_number }}</div>
                <div class="mt-2 p-2" style="background:#f0fdf4;border-radius:8px;color:#166534">Poin didapat: <strong>+{{ number_format($transaction->points_earned,0,',','.') }}</strong></div>
                @if(($transaction->points_redeemed ?? 0) > 0)
                <div class="mt-2 p-2" style="background:#fffbeb;border-radius:8px;color:#92400e">Poin diredeem: <strong>-{{ number_format($transaction->points_redeemed,0,',','.') }}</strong></div>
                @endif
            </div>
        </div>
        @endif
        <div class="card">
            <div class="card-header"><h6>Pembayaran</h6></div>
            <div class="card-body" style="font-size:.86rem">
                <div class="d-flex justify-content-between py-1"><span>Subtotal</span><strong>Rp {{ number_format($transaction->subtotal,0,',','.') }}</strong></div>
                <div class="d-flex justify-content-between py-1"><span>Diskon</span><strong>-Rp {{ number_format($transaction->discount_amount,0,',','.') }}</strong></div>
                @if(($transaction->point_redeem_amount ?? 0) > 0)
                <div class="d-flex justify-content-between py-1"><span>Potongan Poin</span><strong class="text-danger">-Rp {{ number_format($transaction->point_redeem_amount,0,',','.') }}</strong></div>
                @endif
                <hr>
                <div class="d-flex justify-content-between py-1"><span>Total</span><strong class="text-success">Rp {{ number_format($transaction->total_price,0,',','.') }}</strong></div>
                <div class="d-flex justify-content-between py-1"><span>Uang Diterima</span><strong>Rp {{ number_format($transaction->cash_received,0,',','.') }}</strong></div>
                <div class="d-flex justify-content-between py-1"><span>Kembalian</span><strong>Rp {{ number_format($transaction->change_amount,0,',','.') }}</strong></div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h6>Detail Item</h6></div>
            <div class="table-wrapper">
                <table class="table mb-0">
                    <thead><tr><th>Produk</th><th>Qty</th><th>Harga Normal</th><th>Harga Akhir</th><th>Subtotal</th></tr></thead>
                    <tbody>
                        @foreach($transaction->details as $detail)
                        <tr>
                            <td><strong>{{ $detail->product->product_name ?? '-' }}</strong><div class="text-muted small">{{ $detail->product->product_code ?? '-' }}</div></td>
                            <td>{{ number_format($detail->qty) }}</td>
                            <td>Rp {{ number_format($detail->unit_price,0,',','.') }}</td>
                            <td>Rp {{ number_format($detail->final_unit_price,0,',','.') }}</td>
                            <td><strong>Rp {{ number_format($detail->subtotal,0,',','.') }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if($transaction->notes)
        <div class="card mt-3"><div class="card-header"><h6>Catatan</h6></div><div class="card-body" style="white-space:pre-line">{{ $transaction->notes }}</div></div>
        @endif
    </div>
</div>
@endsection
