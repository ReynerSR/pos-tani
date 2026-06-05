@extends('layouts.app')
@section('title','Daftarkan Member Baru')
@section('page_title','Daftarkan Member Baru')

@section('content')
<div class="page-hdr">
    <div class="page-hdr-left">
        <h1><i class="bi bi-person-plus me-2" style="color:var(--primary)"></i>Daftarkan Member Baru</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ ($returnTo ?? null) === 'kasir' ? route('kasir.pos') : route('customers.index') }}">{{ ($returnTo ?? null) === 'kasir' ? 'Kasir / POS' : 'Data Member' }}</a></li>
            <li class="breadcrumb-item active">Daftar Baru</li>
        </ol></nav>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-header"><h6>Form Pendaftaran Member</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('customers.store') }}">
                @csrf
                @if(($returnTo ?? null) === 'kasir')
                    <input type="hidden" name="return_to" value="kasir">
                @endif
                <div class="mb-3">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="full_name"
                           class="form-control @error('full_name') is-invalid @enderror"
                           value="{{ old('full_name') }}"
                           placeholder="Nama lengkap pelanggan" required>
                    @error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Nomor WhatsApp <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-whatsapp"></i></span>
                        <input type="text" name="whatsapp_number" class="form-control @error('whatsapp_number') is-invalid @enderror"
                               value="{{ old('whatsapp_number') }}" placeholder="08xxxxxxxxxx" required>
                    </div>
                    @error('whatsapp_number')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    <div class="form-text">Wajib diisi untuk keperluan nota, komunikasi transaksi, dan promo.</div>
                </div>
                <div class="mb-4">
                    <label class="form-label">Alamat</label>
                    <textarea name="address" class="form-control" rows="3"
                              placeholder="Desa/Kelurahan, Kecamatan, Kabupaten">{{ old('address') }}</textarea>
                </div>

                @if(($returnTo ?? null) === 'kasir')
                <div class="alert alert-warning py-2 px-3 mb-3" style="font-size:.82rem">
                    <i class="bi bi-arrow-left-right me-2"></i>Form ini dibuka dari Kasir/POS. Setelah disimpan atau batal, sistem akan kembali ke Kasir/POS dan keranjang yang sudah diinput tetap dipertahankan.
                </div>
                @endif

                <div class="alert alert-info py-2 px-3 mb-4" style="font-size:.82rem">
                    <i class="bi bi-info-circle me-2"></i>
                    Member baru akan otomatis masuk tier <strong>Bronze</strong>. Tier akan naik secara otomatis berdasarkan akumulasi belanja sesuai aturan membership yang berlaku.
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-person-check me-2"></i>Daftarkan Member
                    </button>
                    <a href="{{ ($returnTo ?? null) === 'kasir' ? route('kasir.pos') : route('customers.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection