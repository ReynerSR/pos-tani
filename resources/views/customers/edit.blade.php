@extends('layouts.app')
@section('title','Edit Member')
@section('page_title','Edit Member')

@section('content')
<div class="page-hdr">
    <div class="page-hdr-left">
        <h1><i class="bi bi-pencil-square me-2" style="color:var(--primary)"></i>Edit Data Member</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Data Member</a></li>
            <li class="breadcrumb-item"><a href="{{ route('customers.show',$customer) }}">{{ $customer->full_name }}</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol></nav>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-header"><h6>Edit Member: {{ $customer->full_name }}</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('customers.update',$customer) }}">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="full_name"
                           class="form-control @error('full_name') is-invalid @enderror"
                           value="{{ old('full_name',$customer->full_name) }}" required>
                    @error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Nomor WhatsApp <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-whatsapp"></i></span>
                        <input type="text" name="whatsapp_number" class="form-control @error('whatsapp_number') is-invalid @enderror"
                               value="{{ old('whatsapp_number',$customer->whatsapp_number) }}" placeholder="08xxxxxxxxxx" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label">Alamat</label>
                    <textarea name="address" class="form-control" rows="3">{{ old('address',$customer->address) }}</textarea>
                </div>

                {{-- Info readonly --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Tier Saat Ini</label>
                        @if(auth()->user()->isPemilik())
                            <select name="tier" class="form-select">
                                <option value="bronze" {{ old('tier',$customer->tier)==='bronze'?'selected':'' }}>Bronze</option>
                                <option value="silver" {{ old('tier',$customer->tier)==='silver'?'selected':'' }}>Silver</option>
                                <option value="gold" {{ old('tier',$customer->tier)==='gold'?'selected':'' }}>Gold</option>
                            </select>
                            <div class="form-text">Khusus owner dapat mengubah tier manual.</div>
                        @else
                            <div class="form-control bg-light" style="cursor:not-allowed">
                                <span class="badge-tier badge-{{ $customer->tier }}">{{ ucfirst($customer->tier) }}</span>
                            </div>
                            <div class="form-text">Hanya owner yang dapat mengubah tier.</div>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Total Akumulasi</label>
                        <div class="form-control bg-light" style="cursor:not-allowed;font-weight:600">
                            Rp {{ number_format($customer->total_accumulation,0,',','.') }}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Saldo Poin</label>
                        <div class="form-control bg-light" style="cursor:not-allowed;font-weight:600">
                            {{ number_format($customer->point_balance,0,',','.') }} poin
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-lg me-2"></i>Simpan Perubahan
                    </button>
                    <a href="{{ route('customers.show',$customer) }}" class="btn btn-outline-secondary">Batal</a>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection