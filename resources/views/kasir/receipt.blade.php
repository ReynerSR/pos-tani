@extends('layouts.app')
@section('title','Struk Belanja')
@section('page_title','Struk Belanja')

@push('styles')
<style>
@media print {
    @page { margin: 0; size: auto; }
    html, body { margin: 0; padding: 0; width: 100%; background-color: transparent; }
    
    /* Sembunyikan elemen navigasi dan header agar tidak memakan ruang kosong */
    #sidebar, #topbar, .page-hdr, #sb-overlay, .modal, .alert { display: none !important; }
    
    /* Hapus paksaan tinggi 100vh pada main container */
    #main { margin: 0 !important; padding: 0 !important; width: 100% !important; min-height: 0 !important; }
    
    .receipt-card {
        width: 100% !important; max-width: 100% !important;
        margin: 0 !important; padding: 0 !important;
        box-shadow: none !important; border: none !important;
    }
    .card { border: none !important; box-shadow: none !important; border-radius: 0 !important; }
    .card-body { padding: 0 2mm !important; }
}
.receipt-card { max-width: 350px; margin: 0 auto; color: #000; }
.receipt-divider { border-top: 1px dashed #000; margin: 6px 0; }
.receipt-row { display: flex; justify-content: space-between; font-size: .8rem; padding: 1px 0; }
.receipt-row.total { font-size: .95rem; font-weight: 700; border-top: 1px dashed #000; padding-top: 6px; margin-top: 3px; }
</style>
@endpush

@section('content')
<div class="page-hdr no-print">
    <div class="page-hdr-left">
        <h1><i class="bi bi-receipt me-2" style="color:var(--primary)"></i>Struk Belanja</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('kasir.history') }}">Riwayat</a></li>
            <li class="breadcrumb-item active">{{ $transaction->transaction_number }}</li>
        </ol></nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        {{-- Tombol WA --}}
        @if($transaction->customer && $transaction->customer->whatsapp_number && isset($waMessage))
        <a href="https://wa.me/{{ preg_replace('/^0/', '62', $transaction->customer->whatsapp_number) }}?text={{ $waMessage }}"
           target="_blank" class="btn px-4"
           style="background:#25d366;border-color:#25d366;color:#fff;font-weight:600">
            <i class="bi bi-whatsapp me-2"></i>Kirim Struk ke WA
        </a>
        @endif
        <button onclick="window.print()" class="btn btn-outline-primary px-4">
            <i class="bi bi-printer me-2"></i>Cetak
        </button>
        <a href="{{ route('kasir.pos') }}" class="btn btn-primary px-4">
            <i class="bi bi-cart-plus me-2"></i>Transaksi Baru
        </a>
        @if(request('back_url'))
        <a href="{{ request('back_url') }}" class="btn btn-outline-secondary px-4">
            <i class="bi bi-arrow-left me-2"></i>Kembali
        </a>
        @endif
    </div>
</div>

<div class="receipt-card">
    <div class="card">
        <div class="card-body p-4">
            {{-- Header --}}
            <div class="text-center mb-3">
                <div style="font-weight:800;font-size:1.1rem;color:var(--primary-dark)">UD. TANI AGUNG NGAWI</div>
                <div style="font-size:.76rem;color:#6b7280">Jalan, Walikukun Kulon, Walikukun, Widodaren, Ngawi Regency, East Java 63256</div>
                <div style="font-size:.76rem;color:#6b7280">Telp: 08563624776</div>
            </div>

            <div class="receipt-divider"></div>

            <div style="font-size:.8rem;color:#6b7280">
                <div class="receipt-row"><span>No. Transaksi</span><span style="font-weight:600;color:#1a202c">{{ $transaction->transaction_number }}</span></div>
                <div class="receipt-row"><span>Tanggal</span><span>{{ $transaction->transaction_date->format('d/m/Y H:i') }}</span></div>
                <div class="receipt-row"><span>Kasir</span><span>{{ $transaction->cashier->name ?? '-' }}</span></div>
                @if($transaction->customer)
                <div class="receipt-row"><span>Member</span>
                    <span>{{ $transaction->customer->full_name }}
                        <span class="badge-tier badge-{{ $transaction->customer_tier ?? $transaction->customer->tier }} ms-1">{{ ucfirst($transaction->customer_tier ?? $transaction->customer->tier) }}</span>
                    </span>
                </div>
                @else
                <div class="receipt-row"><span>Pelanggan</span><span>Umum</span></div>
                @endif
            </div>

            <div class="receipt-divider"></div>

            {{-- Items --}}
            @foreach($transaction->details as $d)
            <div style="font-size:.82rem;margin-bottom:6px">
                <div style="font-weight:600">{{ $d->product->product_name ?? '-' }}</div>
                <div class="d-flex justify-content-between" style="color:#4b5563">
                    <span>{{ $d->qty }} {{ $d->product->unit ?? '' }} x Rp {{ number_format($d->final_unit_price,0,',','.') }}</span>
                    <span style="font-weight:600">Rp {{ number_format($d->subtotal,0,',','.') }}</span>
                </div>
                @if($d->unit_price != $d->final_unit_price)
                <div style="font-size:.72rem;color:#27ae60">
                    @php $selisih = $d->unit_price - $d->final_unit_price; @endphp
                    Hemat Rp {{ number_format($selisih,0,',','.') }}/{{ $d->product->unit ?? 'pcs' }}
                </div>
                @endif
            </div>
            @endforeach

            <div class="receipt-divider"></div>

            {{-- Totals --}}
            <div style="font-size:.83rem">
                <div class="receipt-row"><span>Subtotal</span><span>Rp {{ number_format($transaction->subtotal,0,',','.') }}</span></div>
                @if($transaction->discount_amount > 0)
                <div class="receipt-row" style="color:#dc2626">
                    <span>Diskon Member ({{ $transaction->discount_percent }}%)</span>
                    <span>-Rp {{ number_format($transaction->discount_amount,0,',','.') }}</span>
                </div>
                @endif
                @if(($transaction->point_redeem_amount ?? 0) > 0)
                <div class="receipt-row" style="color:#dc2626">
                    <span>Redeem Poin ({{ number_format($transaction->points_redeemed,0,',','.') }} poin)</span>
                    <span>-Rp {{ number_format($transaction->point_redeem_amount,0,',','.') }}</span>
                </div>
                @endif
                <div class="receipt-row total">
                    <span>TOTAL BAYAR</span>
                    <span>Rp {{ number_format($transaction->total_price,0,',','.') }}</span>
                </div>
                <div class="receipt-row" style="margin-top:6px"><span>Tunai</span><span>Rp {{ number_format($transaction->cash_received,0,',','.') }}</span></div>
                <div class="receipt-row" style="font-weight:700;color:var(--primary)"><span>Kembalian</span><span>Rp {{ number_format($transaction->change_amount,0,',','.') }}</span></div>
            </div>

            @if($transaction->points_earned > 0 || $transaction->customer)
            <div class="receipt-divider"></div>
            <div style="font-size:.8rem;background:var(--primary-pale);border-radius:8px;padding:10px 12px">
                @if($transaction->customer)
                @php
                    $poinAkhir = $transaction->customer_point_balance ?? $transaction->customer->point_balance;
                    $poinAwal = $poinAkhir + ($transaction->points_redeemed ?? 0) - ($transaction->points_earned ?? 0);
                @endphp
                <div class="receipt-row"><span>Saldo Sebelumnya</span><span style="font-weight:700">{{ number_format($poinAwal,0,',','.') }} poin</span></div>
                @endif
                @if(($transaction->points_redeemed ?? 0) > 0)
                <div class="receipt-row"><span>Poin Diredeem</span><span style="font-weight:700;color:#dc2626">-{{ number_format($transaction->points_redeemed,0,',','.') }} poin</span></div>
                @endif
                @if($transaction->points_earned > 0)
                <div class="receipt-row"><span>Poin Didapat</span><span style="font-weight:700;color:var(--primary)">+{{ number_format($transaction->points_earned,0,',','.') }} poin</span></div>
                @endif
                @if($transaction->customer)
                <div class="receipt-row"><span>Saldo Akhir</span><span style="font-weight:700">{{ number_format($poinAkhir,0,',','.') }} poin</span></div>
                <div class="receipt-row"><span>Status Tier</span>
                    <span class="badge-tier badge-{{ $transaction->customer_tier ?? $transaction->customer->tier }}">{{ ucfirst($transaction->customer_tier ?? $transaction->customer->tier) }}</span>
                </div>
                @endif
            </div>
            @endif


            @if($transaction->notes)
            <div class="receipt-divider"></div>
            <div style="font-size:.8rem;color:#4b5563">
                <div style="font-weight:600">Catatan:</div>
                <div style="white-space:pre-line">{{ $transaction->notes }}</div>
            </div>
            @endif

            <div class="receipt-divider"></div>
            <div class="text-center" style="font-size:.76rem;color:#9ca3af">
                <div>Terima kasih telah berbelanja!</div>
                <div>Barang yang sudah dibeli tidak dapat dikembalikan</div>
                <div class="mt-1" style="font-weight:600;color:#6b7280">UD. Tani Agung Ngawi</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
try { localStorage.removeItem('pos_tani_draft_v2'); } catch(e) {}
</script>
@endpush
