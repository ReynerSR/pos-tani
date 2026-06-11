@extends('layouts.app')
@section('title','Aturan Membership')
@section('page_title','Pengaturan Membership')

@section('content')
<div class="page-hdr">
    <div class="page-hdr-left">
        <h1><i class="bi bi-award me-2" style="color:var(--primary)"></i>Aturan Membership</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item active">Rule-Based System</li></ol></nav>
    </div>
</div>

<div class="row g-4">
    {{-- Tier Info Cards --}}
    <div class="col-12">
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <div class="card p-4" style="border-top:4px solid #e74c3c">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div style="width:44px;height:44px;background:#fee2e2;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;color:#dc2626">
                            <i class="bi bi-award-fill"></i>
                        </div>
                        <div>
                            <div style="font-weight:700;font-size:1rem">Tier Bronze</div>
                            <div style="font-size:.76rem;color:#9ca3af">Member baru / default</div>
                        </div>
                    </div>
                    <div style="font-size:.83rem;color:#6b7280">Akumulasi belanja</div>
                    <div style="font-size:1rem;font-weight:700;color:#1a202c">Rp 0 s/d Rp {{ number_format($rule->tier_silver_min,0,',','.') }}</div>
                    <div class="mt-2 p-2" style="background:#fee2e2;border-radius:8px;font-size:.82rem;color:#991b1b;font-weight:600;text-align:center">
                        Diskon {{ $rule->discount_bronze }}%
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card p-4" style="border-top:4px solid #6b7280">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div style="width:44px;height:44px;background:#e5e7eb;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;color:#374151">
                            <i class="bi bi-award-fill"></i>
                        </div>
                        <div>
                            <div style="font-weight:700;font-size:1rem">Tier Silver</div>
                            <div style="font-size:.76rem;color:#9ca3af">Member menengah</div>
                        </div>
                    </div>
                    <div style="font-size:.83rem;color:#6b7280">Akumulasi belanja</div>
                    <div style="font-size:1rem;font-weight:700;color:#1a202c">Rp {{ number_format($rule->tier_silver_min,0,',','.') }} s/d Rp {{ number_format($rule->tier_gold_min,0,',','.') }}</div>
                    <div class="mt-2 p-2" style="background:#e5e7eb;border-radius:8px;font-size:.82rem;color:#374151;font-weight:600;text-align:center">
                        Diskon {{ $rule->discount_silver }}%
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card p-4" style="border-top:4px solid #f39c12">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div style="width:44px;height:44px;background:#fef3c7;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;color:#f39c12">
                            <i class="bi bi-award-fill"></i>
                        </div>
                        <div>
                            <div style="font-weight:700;font-size:1rem">Tier Gold</div>
                            <div style="font-size:.76rem;color:#9ca3af">Member loyal tertinggi</div>
                        </div>
                    </div>
                    <div style="font-size:.83rem;color:#6b7280">Akumulasi belanja</div>
                    <div style="font-size:1rem;font-weight:700;color:#1a202c">&ge; Rp {{ number_format($rule->tier_gold_min,0,',','.') }}</div>
                    <div class="mt-2 p-2" style="background:#fef3c7;border-radius:8px;font-size:.82rem;color:#92400e;font-weight:600;text-align:center">
                        Diskon {{ $rule->discount_gold }}%
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Form --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center gap-2">
                <h6 class="mb-0"><i class="bi bi-gear me-2" style="color:var(--primary)"></i>Konfigurasi Aturan</h6>
                <span style="background:#d1fae5;color:#065f46;font-size:.68rem;font-weight:700;padding:2px 8px;border-radius:8px">Rule-Based System</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('membership.update') }}">
                @csrf @method('PUT')

                <div class="mb-4">
                    <div style="font-size:.8rem;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px">Batas Tier</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Batas Minimal Tier Silver</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="tier_silver_min"
                                       class="form-control @error('tier_silver_min') is-invalid @enderror"
                                       value="{{ old('tier_silver_min',$rule->tier_silver_min) }}"
                                       min="0" step="any" required>
                            </div>
                            @error('tier_silver_min')<div class="text-danger" style="font-size:.78rem">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Batas Minimal Tier Gold</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="tier_gold_min"
                                       class="form-control @error('tier_gold_min') is-invalid @enderror"
                                       value="{{ old('tier_gold_min',$rule->tier_gold_min) }}"
                                       min="0" step="any" required>
                            </div>
                            @error('tier_gold_min')<div class="text-danger" style="font-size:.78rem">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <div style="font-size:.8rem;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px">Persentase Diskon per Tier</div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Diskon Bronze (%)</label>
                            <div class="input-group">
                                <input type="number" name="discount_bronze"
                                       class="form-control @error('discount_bronze') is-invalid @enderror"
                                       value="{{ old('discount_bronze',$rule->discount_bronze) }}"
                                       min="0" max="100" step="0.5" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Diskon Silver (%)</label>
                            <div class="input-group">
                                <input type="number" name="discount_silver"
                                       class="form-control @error('discount_silver') is-invalid @enderror"
                                       value="{{ old('discount_silver',$rule->discount_silver) }}"
                                       min="0" max="100" step="0.5" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Diskon Gold (%)</label>
                            <div class="input-group">
                                <input type="number" name="discount_gold"
                                       class="form-control @error('discount_gold') is-invalid @enderror"
                                       value="{{ old('discount_gold',$rule->discount_gold) }}"
                                       min="0" max="100" step="0.5" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <div style="font-size:.8rem;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px">Konversi Poin</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">1 Poin per Nominal Belanja</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="point_per_nominal"
                                       class="form-control @error('point_per_nominal') is-invalid @enderror"
                                       value="{{ old('point_per_nominal',$rule->point_per_nominal) }}"
                                       min="100" step="any" required>
                            </div>
                            <div class="form-text">Contoh: 1000 = setiap belanja Rp 1.000 mendapat 1 poin.</div>
                            @error('point_per_nominal')<div class="text-danger" style="font-size:.78rem">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nilai Redeem per 1 Poin</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="redeem_point_value"
                                       class="form-control @error('redeem_point_value') is-invalid @enderror"
                                       value="{{ old('redeem_point_value',$rule->redeem_point_value ?? 100) }}"
                                       min="1" step="1" required>
                            </div>
                            <div class="form-text">Contoh: 100 = 1 poin bernilai potongan Rp 100.</div>
                            @error('redeem_point_value')<div class="text-danger" style="font-size:.78rem">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Minimal Redeem</label>
                            <div class="input-group">
                                <input type="number" name="minimum_redeem_points"
                                       class="form-control @error('minimum_redeem_points') is-invalid @enderror"
                                       value="{{ old('minimum_redeem_points',$rule->minimum_redeem_points ?? 100) }}"
                                       min="0" step="1" required>
                                <span class="input-group-text">poin</span>
                            </div>
                            <div class="form-text">Kasir tidak bisa redeem di bawah batas ini.</div>
                            @error('minimum_redeem_points')<div class="text-danger" style="font-size:.78rem">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Maksimal Potongan Redeem</label>
                            <div class="input-group">
                                <input type="number" name="max_redeem_percent"
                                       class="form-control @error('max_redeem_percent') is-invalid @enderror"
                                       value="{{ old('max_redeem_percent',$rule->max_redeem_percent ?? 50) }}"
                                       min="1" max="100" step="1" required>
                                <span class="input-group-text">%</span>
                            </div>
                            <div class="form-text">Contoh: 50% = poin maksimal hanya boleh memotong separuh total transaksi.</div>
                            @error('max_redeem_percent')<div class="text-danger" style="font-size:.78rem">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kelipatan Redeem Poin</label>
                            <div class="input-group">
                                <input type="number" name="redeem_multiple"
                                       class="form-control @error('redeem_multiple') is-invalid @enderror"
                                       value="{{ old('redeem_multiple',$rule->redeem_multiple ?? 100) }}"
                                       min="1" step="1" required>
                                <span class="input-group-text">poin</span>
                            </div>
                            <div class="form-text">Poin hanya bisa digunakan dalam kelipatan angka ini (misal: 100).</div>
                            @error('redeem_multiple')<div class="text-danger" style="font-size:.78rem">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning py-2 px-3 mb-4" style="font-size:.82rem">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Perubahan aturan berlaku untuk transaksi <strong>selanjutnya</strong>. Tier member yang sudah ada tidak berubah sampai transaksi berikutnya diproses.
                </div>

                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check-lg me-2"></i>Simpan Konfigurasi
                </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="alert alert-info d-flex align-items-start gap-2" style="font-size:.86rem">
            <i class="bi bi-info-circle mt-1"></i>
            <div>
                <strong>Redeem Poin sudah aktif:</strong> Poin diredeem dari halaman Kasir/POS saat member dipilih. Sistem otomatis menghitung potongan, mengurangi saldo poin member, mencatat riwayat poin keluar, dan memasukkan aktivitas redeem ke log transaksi. Jika transaksi direvisi/void, poin redeem dikembalikan otomatis.
            </div>
        </div>
    </div>

    {{-- Log perubahan & Cara Kerja --}}
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header"><h6><i class="bi bi-clock-history me-2" style="color:var(--primary)"></i>Riwayat Perubahan Aturan</h6></div>
            <div class="card-body py-2">
                @forelse($logs as $log)
                <div style="padding:10px 0;border-bottom:1px solid #f3f4f6;font-size:.8rem">
                    <div style="font-weight:600;color:#1a202c">{{ $log->user->name ?? 'System' }}</div>
                    <div style="color:#6b7280;margin:3px 0">{{ $log->detail }}</div>
                    <div style="color:#9ca3af;font-size:.73rem">{{ $log->created_at->format('d/m/Y H:i') }}</div>
                </div>
                @empty
                <div class="text-center py-4" style="color:#9ca3af;font-size:.83rem">Belum ada riwayat perubahan</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="card h-100" style="border-left:4px solid var(--primary)">
            <div class="card-body" style="font-size:.8rem;color:#6b7280">
                <div style="font-weight:700;color:var(--primary-dark);margin-bottom:8px"><i class="bi bi-info-circle me-1"></i>Cara Kerja Rule-Based System</div>
                <div class="mb-2"><strong>IF</strong> akumulasi belanja &ge; Batas Gold <strong>THEN</strong> tier = Gold</div>
                <div class="mb-2"><strong>ELSE IF</strong> akumulasi &ge; Batas Silver <strong>THEN</strong> tier = Silver</div>
                <div><strong>ELSE</strong> tier = Bronze</div>
                <hr style="margin:10px 0">
                <div>Evaluasi dilakukan otomatis setiap kali transaksi member selesai diproses oleh kasir.</div>
            </div>
        </div>
    </div>
</div>
@endsection