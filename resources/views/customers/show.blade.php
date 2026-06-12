<!-- Menggunakan template utama aplikasi -->
@extends('layouts.app')

<!-- Menentukan judul halaman -->
@section('title','Profil Member')
@section('page_title','Profil Member')

@section('content')
<!-- Header Halaman -->
<div class="page-hdr">
    <div class="page-hdr-left">
        <h1><i class="bi bi-person-badge me-2" style="color:var(--primary)"></i>Profil Member</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Data Member</a></li>
            <li class="breadcrumb-item active">{{ $customer->full_name }}</li>
        </ol></nav>
    </div>
    <div class="d-flex gap-2">
        @if(auth()->user()->role === 'pemilik')
        <a href="{{ route('customers.edit',$customer) }}" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-2"></i>Edit
        </a>
        @endif
        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Bagian Kiri: Kartu Profil -->
    <div class="col-12 col-lg-4">

        <!-- Identitas Member -->
        <div class="card mb-3">
            <div class="card-body text-center py-4">
                <div style="width:72px;height:72px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:800;color:#fff;margin:0 auto 14px;
                    background:{{ $customer->tier=='gold'?'linear-gradient(135deg,#c87000,#f39c12)':($customer->tier=='silver'?'linear-gradient(135deg,#4b5563,#9ca3af)':'linear-gradient(135deg,#922b21,#e74c3c)') }}">
                    {{ strtoupper(substr($customer->full_name,0,1)) }}
                </div>
                <h5 style="font-weight:700;margin-bottom:6px">{{ $customer->full_name }}</h5>
                <span class="badge-tier badge-{{ $customer->tier }}">
                    <i class="bi bi-award-fill"></i> {{ ucfirst($customer->tier) }}
                </span>
                <div class="mt-3" style="font-size:.82rem;color:#6b7280">
                    <i class="bi bi-calendar3 me-1"></i>Terdaftar {{ $customer->registered_at->format('d F Y') }}
                </div>
                @if($customer->whatsapp_number)
                <div class="mt-1" style="font-size:.82rem">
                    <a href="https://wa.me/{{ preg_replace('/^0/','62',$customer->whatsapp_number) }}" target="_blank" style="color:var(--primary);text-decoration:none">
                        <i class="bi bi-whatsapp me-1"></i>{{ $customer->whatsapp_number }}
                    </a>
                </div>
                @endif
                @if($customer->address)
                <div class="mt-1" style="font-size:.79rem;color:#9ca3af"><i class="bi bi-geo-alt me-1"></i>{{ $customer->address }}</div>
                @endif
            </div>
        </div>

        <!-- Poin & Akumulasi Belanja -->
        <div class="card mb-3">
            <div class="card-header"><h6>Saldo &amp; Akumulasi</h6></div>
            <div class="card-body">
                <div class="text-center mb-3 p-3" style="background:var(--primary-pale);border-radius:10px">
                    <div style="font-size:2rem;font-weight:800;color:var(--primary-dark)">
                        {{ number_format($customer->point_balance,0,',','.') }}
                    </div>
                    <div style="font-size:.76rem;color:var(--primary);font-weight:600">Total Poin Reward</div>
                </div>
                <div class="d-flex justify-content-between py-1" style="font-size:.83rem">
                    <span style="color:#6b7280">Total Akumulasi Belanja</span>
                    <span style="font-weight:700">Rp {{ number_format($customer->total_accumulation,0,',','.') }}</span>
                </div>
                <div class="d-flex justify-content-between py-1" style="font-size:.83rem">
                    <span style="color:#6b7280">Diskon Berlaku</span>
                    <span style="font-weight:700;color:var(--primary)">{{ $rule->getDiscountForTier($customer->tier) }}%</span>
                </div>
            </div>
        </div>

        <!-- Progress Menuju Tier Selanjutnya -->
        @if($customer->tier !== 'gold')
        <div class="card mb-3">
            <div class="card-header"><h6>Progress Menuju Tier {{ $nextTierLabel }}</h6></div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-1" style="font-size:.78rem;color:#6b7280">
                    <span>{{ ucfirst($customer->tier) }}</span>
                    <span>{{ $nextTierLabel }}</span>
                </div>
                <div class="progress mb-2" style="height:10px;border-radius:8px">
                    <div class="progress-bar" style="width:{{ $nextTierProgress }}%;background:{{ $customer->tier=='silver'?'#f39c12':'#6b7280' }};border-radius:8px"></div>
                </div>
                <div style="font-size:.78rem;color:#6b7280">
                    Rp {{ number_format($customer->total_accumulation,0,',','.') }}
                    / Rp {{ number_format($nextTierAmount,0,',','.') }}
                    <span class="float-end font-weight-bold">{{ $nextTierProgress }}%</span>
                </div>
                @php $remaining = max(0, $nextTierAmount - $customer->total_accumulation); @endphp
                @if($remaining > 0)
                <div class="mt-2 p-2" style="background:#f0fdf4;border-radius:8px;font-size:.77rem;color:var(--primary)">
                    <i class="bi bi-arrow-up-circle me-1"></i>
                    Butuh <strong>Rp {{ number_format($remaining,0,',','.') }}</strong> lagi untuk naik ke {{ $nextTierLabel }}
                </div>
                @endif
            </div>
        </div>
        @else
        <div class="card mb-3" style="border-left:4px solid #f39c12">
            <div class="card-body py-3 text-center">
                <i class="bi bi-award-fill text-warning" style="font-size:2rem"></i>
                <div style="font-weight:700;color:#92400e;margin-top:6px">Member Gold</div>
                <div style="font-size:.78rem;color:#9ca3af">Tier tertinggi telah dicapai</div>
            </div>
        </div>
        @endif

    </div>

    <!-- Bagian Kanan: Transaksi & Poin -->
    <div class="col-12 col-lg-8">

        <!-- Transaksi Terakhir -->
        <div class="card mb-4">
            <div class="card-header"><h6><i class="bi bi-receipt me-2" style="color:var(--primary)"></i>Riwayat Transaksi</h6></div>
            <div class="table-wrapper">
                <table class="table mb-0" style="font-size:.82rem">
                    <thead>
                        <tr><th>No. Transaksi</th><th>Tanggal</th><th>Kasir</th><th>Subtotal</th><th>Diskon/Redeem</th><th>Total Bayar</th><th>Poin</th></tr>
                    </thead>
                    <tbody>
                        @forelse($recentTransactions as $trx)
                        <tr>
                            <td>
                                <a href="{{ route('kasir.show',$trx) }}" style="color:var(--primary);font-weight:600;text-decoration:none">
                                    {{ $trx->transaction_number }}
                                </a>
                            </td>
                            <td>{{ $trx->transaction_date->format('d/m/Y H:i') }}</td>
                            <td>{{ $trx->cashier->name ?? '-' }}</td>
                            <td>Rp {{ number_format($trx->subtotal,0,',','.') }}</td>
                            <td>
                                @if($trx->discount_amount > 0)
                                <div style="color:#dc2626;font-weight:600">
                                    Diskon -Rp {{ number_format($trx->discount_amount,0,',','.') }}
                                    <span style="font-size:.7rem">({{ $trx->discount_percent }}%)</span>
                                </div>
                                @endif
                                @if(($trx->point_redeem_amount ?? 0) > 0)
                                <div style="color:#dc2626;font-weight:600">Redeem -Rp {{ number_format($trx->point_redeem_amount,0,',','.') }}</div>
                                @endif
                                @if($trx->discount_amount <= 0 && ($trx->point_redeem_amount ?? 0) <= 0)
                                <span style="color:#9ca3af">-</span>
                                @endif
                            </td>
                            <td style="font-weight:700;color:var(--primary-dark)">Rp {{ number_format($trx->total_price,0,',','.') }}</td>
                            <td>
                                @if(($trx->points_redeemed ?? 0) > 0)
                                <div><span style="background:#fee2e2;color:#991b1b;font-size:.72rem;font-weight:700;padding:2px 8px;border-radius:10px">-{{ number_format($trx->points_redeemed,0,',','.') }}</span></div>
                                @endif
                                @if($trx->points_earned > 0)
                                <div class="mt-1"><span style="background:#d1fae5;color:#065f46;font-size:.72rem;font-weight:700;padding:2px 8px;border-radius:10px">+{{ number_format($trx->points_earned,0,',','.') }}</span></div>
                                @elseif(($trx->points_redeemed ?? 0) <= 0)
                                <span style="color:#9ca3af">0</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-4" style="color:#9ca3af">Belum ada riwayat transaksi</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Riwayat Mutasi Poin -->
        <div class="card">
            <div class="card-header"><h6><i class="bi bi-star me-2" style="color:var(--primary)"></i>Riwayat Poin</h6></div>
            <div class="table-wrapper">
                <table class="table mb-0" style="font-size:.82rem">
                    <thead>
                        <tr><th>Tanggal</th><th>Keterangan</th><th>Mutasi Poin</th></tr>
                    </thead>
                    <tbody>
                        @forelse($pointHistories as $ph)
                        <tr>
                            <td>{{ $ph->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $ph->description ?? '-' }}</td>
                            <td>
                                @if($ph->points_earned >= 0)
                                <span style="background:#d1fae5;color:#065f46;font-size:.75rem;font-weight:700;padding:3px 10px;border-radius:10px">
                                    +{{ number_format($ph->points_earned,0,',','.') }} poin
                                </span>
                                @else
                                <span style="background:#fee2e2;color:#991b1b;font-size:.75rem;font-weight:700;padding:3px 10px;border-radius:10px">
                                    {{ number_format($ph->points_earned,0,',','.') }} poin
                                </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center py-4" style="color:#9ca3af">Belum ada riwayat poin</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Riwayat Perubahan Tier -->
        <div class="card mt-4">
            <div class="card-header"><h6><i class="bi bi-clock-history me-2" style="color:var(--primary)"></i>Histori Perubahan Tier</h6></div>
            <div class="table-wrapper">
                <table class="table mb-0" style="font-size:.82rem">
                    <thead><tr><th>Tanggal</th><th>Dari</th><th>Ke</th><th>Sumber</th><th>Catatan</th></tr></thead>
                    <tbody>
                        @forelse($customer->tierHistories->sortByDesc('created_at')->take(10) as $th)
                        <tr>
                            <td>{{ $th->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ ucfirst($th->old_tier ?? '-') }}</td>
                            <td><strong>{{ ucfirst($th->new_tier) }}</strong></td>
                            <td><span class="badge bg-light text-dark border">{{ str_replace('_',' ',ucfirst($th->source)) }}</span></td>
                            <td>{{ $th->notes ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-4" style="color:#9ca3af">Belum ada histori perubahan tier</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>


    </div>
</div>
@endsection