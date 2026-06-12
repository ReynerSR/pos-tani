@extends('layouts.app')
@section('title','Detail Transaksi')
@section('page_title','Detail Transaksi')

@section('content')
<!-- Header Halaman dan Tombol Aksi -->
<div class="page-hdr">
    <div class="page-hdr-left">
        <h1><i class="bi bi-receipt me-2" style="color:var(--primary)"></i>Detail Transaksi</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('kasir.history') }}">Riwayat</a></li><li class="breadcrumb-item active">{{ $transaction->transaction_number }}</li></ol></nav>
    </div>
    <div class="d-flex gap-2">
        @if($transaction->payment_status === 'paid' && auth()->user()->role === 'pemilik' && $transaction->isLatestForCustomer())
            <a href="{{ route('kasir.edit',$transaction) }}" class="btn btn-outline-warning"><i class="bi bi-pencil me-2"></i>Edit Nota</a>
            <form method="POST" action="{{ route('kasir.destroy',$transaction) }}" class="delete-form" data-confirm="Hapus/void transaksi ini?">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger"><i class="bi bi-trash me-2"></i>Delete / Void</button>
            </form>
        @endif
        <a href="{{ route('kasir.receipt', ['transaction' => $transaction, 'back_url' => $backUrl ?: request()->fullUrl()]) }}" class="btn btn-outline-primary"><i class="bi bi-printer me-2"></i>Cetak Struk</a>
        <a href="{{ $backUrl ?: route('kasir.history') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Kembali</a>
    </div>
</div>

@if($transaction->payment_status !== 'paid')
<!-- Peringatan Transaksi Void/Dihapus -->
<div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>Transaksi ini sudah dihapus/void. Data tetap ditampilkan untuk audit, tetapi tidak dapat diedit. Stok dan poin member sudah dikoreksi.</div>
@endif

<div class="row g-4">
    <!-- Bagian Kiri: Informasi Transaksi, Member, dan Pembayaran -->
    <div class="col-lg-4">
        <!-- Kartu Info Transaksi -->
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
        <!-- Kartu Info Member dan Poin -->
        <div class="card mb-3">
            <div class="card-header"><h6>Member</h6></div>
            <div class="card-body" style="font-size:.86rem">
                <a href="{{ route('customers.show',$transaction->customer) }}" style="font-weight:700;color:var(--primary-dark);text-decoration:none">{{ $transaction->customer->full_name }}</a>
                <div class="mt-1"><span class="badge-tier badge-{{ $transaction->customer_tier ?? $transaction->customer->tier }}">{{ ucfirst($transaction->customer_tier ?? $transaction->customer->tier) }}</span></div>
                <div class="mt-2 text-muted"><i class="bi bi-whatsapp me-1"></i>{{ $transaction->customer->whatsapp_number ?? 'No WA' }}</div>
                @php
                    $poinAkhir = $transaction->customer_point_balance ?? $transaction->customer->point_balance;
                    $poinAwal = $poinAkhir + ($transaction->points_redeemed ?? 0) - ($transaction->points_earned ?? 0);
                @endphp
                <div class="mt-2 p-2" style="background:#f1f5f9;border-radius:8px;color:#475569">Saldo Sebelumnya: <strong>{{ number_format($poinAwal,0,',','.') }}</strong></div>
                <div class="mt-2 p-2" style="background:#f0fdf4;border-radius:8px;color:#166534">Poin didapat: <strong>+{{ number_format($transaction->points_earned,0,',','.') }}</strong></div>
                @if(($transaction->points_redeemed ?? 0) > 0)
                <div class="mt-2 p-2" style="background:#fffbeb;border-radius:8px;color:#92400e">Poin diredeem: <strong>-{{ number_format($transaction->points_redeemed,0,',','.') }}</strong></div>
                @endif
                <div class="mt-2 p-2" style="background:#e0f2fe;border-radius:8px;color:#075985">Saldo Akhir: <strong>{{ number_format($poinAkhir,0,',','.') }}</strong></div>
            </div>
        </div>
        @endif
        <!-- Kartu Rincian Pembayaran -->
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
    <!-- Bagian Kanan: Detail Item Belanja -->
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
        <!-- Kartu Catatan Transaksi -->
        <div class="card mt-3"><div class="card-header"><h6>Catatan</h6></div><div class="card-body" style="white-space:pre-line">{{ $transaction->notes }}</div></div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('submit', function(e) {
    if (e.target && e.target.classList.contains('delete-form')) {
        e.preventDefault();
        const form = e.target;
        Swal.fire({
            title: 'Hapus/Void Transaksi?',
            text: form.dataset.confirm || 'Yakin ingin melanjutkan?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc2626'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }
});
</script>
@endpush
