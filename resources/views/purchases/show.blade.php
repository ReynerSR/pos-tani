@extends('layouts.app')
@section('title', 'Detail Pembelian')
@section('page-title', 'Detail Pembelian')

@section('content')
<!-- Breadcrumb Navigasi -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchases.index') }}">Pembelian</a></li>
        <li class="breadcrumb-item active">{{ $purchase->invoice_number }}</li>
    </ol>
</nav>

<!-- Kontainer Utama -->
<div class="row g-3 justify-content-center">
    <!-- Kolom Tengah: Detail Pembelian -->
    <div class="col-lg-9">
        <!-- Kartu Detail Pembelian -->
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <h6 class="mb-0"><i class="bi bi-receipt me-2" style="color:#16a34a;"></i>{{ $purchase->invoice_number }}</h6>
                    @if(auth()->user()->role === 'pemilik')
                        <a href="{{ route('purchases.edit', $purchase) }}" class="btn btn-sm btn-outline-primary ms-2"><i class="bi bi-pencil me-1"></i>Edit Pembelian</a>
                    @endif
                </div>
                @if(($purchase->status ?? 'approved') === 'draft')
                    <span class="badge" style="background:#fef3c7; color:#92400e;">Draft - Menunggu Owner</span>
                @else
                    <span class="badge" style="background:#dcfce7; color:#15803d;">Approved / Selesai</span>
                @endif
            </div>
            <div class="card-body p-4">
                <div class="row g-3 mb-4" style="font-size:.85rem;">
                    <div class="col-md-3">
                        <div class="text-muted mb-1" style="font-size:.72rem; font-weight:600; text-transform:uppercase;">Supplier</div>
                        <div class="fw-700">{{ $purchase->supplier->name }}</div>
                        <div style="color:#6b7280; font-size:.78rem;">{{ $purchase->supplier->phone }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted mb-1" style="font-size:.72rem; font-weight:600; text-transform:uppercase;">Tanggal Beli</div>
                        <div class="fw-600">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d F Y') }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted mb-1" style="font-size:.72rem; font-weight:600; text-transform:uppercase;">Tempat Simpan</div>
                        <div class="fw-600">{{ $purchase->warehouse ? $purchase->warehouse->code . ($purchase->warehouse->is_store ? ' (Utama)' : '') : '-' }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted mb-1" style="font-size:.72rem; font-weight:600; text-transform:uppercase;">Dicatat Oleh</div>
                        <div class="fw-600">{{ $purchase->user->name }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted mb-1" style="font-size:.72rem; font-weight:600; text-transform:uppercase;">Status</div>
                        <div class="fw-600">{{ strtoupper($purchase->status ?? 'approved') }}</div>
                        @if($purchase->approver)<div style="color:#6b7280; font-size:.78rem;">Approve: {{ $purchase->approver->name }}</div>@endif
                    </div>
                    @if(auth()->user()->role === 'pemilik')
                    <div class="col-md-3">
                        <div class="text-muted mb-1" style="font-size:.72rem; font-weight:600; text-transform:uppercase;">Total Pembelian</div>
                        <div class="fw-800" style="font-size:1.1rem; color:#16a34a;">Rp {{ number_format($purchase->total_price, 0, ',', '.') }}</div>
                    </div>
                    @endif
                </div>
                @if($purchase->notes)
                <div class="mb-3 p-2 rounded" style="background:#fef9c3; font-size:.82rem; border:1px solid #fde68a;">
                    <i class="bi bi-chat-left-text me-1"></i>{{ $purchase->notes }}
                </div>
                @endif

                @if(($purchase->status ?? 'approved') === 'draft' && auth()->user()->role === 'pemilik')
                <div class="alert alert-warning">
                    <strong>Draft restok dari admin.</strong> Isi harga beli setiap produk lalu approve. Stok dan HPP baru akan bertambah setelah approve.
                </div>
                <form method="POST" action="{{ route('purchases.approve', $purchase) }}">
                    @csrf
                @elseif(($purchase->status ?? 'approved') === 'draft')
                <div class="alert alert-warning">
                    Draft restok belum diapprove owner. Harga beli, total pembelian, stok, dan HPP belum diperbarui.
                </div>
                @endif

                <!-- Tabel Daftar Barang -->
                <div class="table-responsive">
                    <table class="table mb-0" style="font-size:.88rem;">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th class="text-center">Qty</th>
                                @if(auth()->user()->role === 'pemilik')
                                <th class="text-end">Harga Beli/satuan</th>
                                @endif
                                @if(auth()->user()->canAccessHPP())
                                <th class="text-end">HPP Baru</th>
                                @endif
                                @if(auth()->user()->role === 'pemilik')
                                <th class="text-end">Subtotal</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchase->details as $detail)
                            <tr>
                                <td>
                                    <div class="fw-600">{{ $detail->product->product_name }}</div>
                                    <div style="font-size:.75rem; color:#6b7280;">{{ $detail->product->product_code }}</div>
                                </td>
                                <td class="text-center fw-600">{{ $detail->qty }} {{ $detail->product->unit }}</td>
                                @if(auth()->user()->role === 'pemilik')
                                <td class="text-end">
                                    @if(($purchase->status ?? 'approved') === 'draft')
                                        <div class="input-group input-group-sm justify-content-end" style="min-width:170px">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" name="items[{{ $detail->id }}][unit_buy_price]" class="form-control text-end" value="{{ old('items.'.$detail->id.'.unit_buy_price', (int)$detail->unit_buy_price) }}" required>
                                        </div>
                                    @else
                                        Rp {{ number_format($detail->unit_buy_price, 0, ',', '.') }}
                                    @endif
                                </td>
                                @endif
                                @if(auth()->user()->canAccessHPP())
                                <td class="text-end" style="color:#16a34a;">{{ ($purchase->status ?? 'approved') === 'draft' ? 'Belum update' : 'Rp '.number_format($detail->new_hpp, 0, ',', '.') }}</td>
                                @endif
                                @if(auth()->user()->role === 'pemilik')
                                <td class="text-end fw-700" style="color:#16a34a;">{{ ($purchase->status ?? 'approved') === 'draft' ? '-' : 'Rp '.number_format($detail->subtotal, 0, ',', '.') }}</td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                        @if(auth()->user()->role === 'pemilik')
                        <tfoot>
                            <tr style="background:#f9fafb;">
                                <td colspan="{{ auth()->user()->canAccessHPP() ? 4 : 3 }}" class="text-end fw-700">Total Pembelian</td>
                                <td class="text-end fw-800" style="font-size:1rem; color:#16a34a;">Rp {{ number_format($purchase->total_price, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
                @if(($purchase->status ?? 'approved') === 'draft' && auth()->user()->role === 'pemilik')
                    <div class="d-flex justify-content-end mt-3">
                        <button class="btn btn-primary" onclick="event.preventDefault(); Swal.fire({title: 'Approve?', text: 'Approve draft restok ini? Stok dan HPP akan diperbarui setelah disimpan.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Approve', cancelButtonText: 'Batal'}).then(r => { if(r.isConfirmed) this.closest('form').submit(); })">
                            <i class="bi bi-check-circle me-2"></i>Approve Draft & Update Stok
                        </button>
                    </div>
                </form>
                @endif
            </div>
            <div class="card-footer" style="background:#f9fafb;">
                <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
