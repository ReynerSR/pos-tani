@extends('layouts.app')
@section('title','Edit Profil')
@section('page_title','Edit Profil')

@section('content')
<div class="page-hdr">
    <div class="page-hdr-left">
        <h1><i class="bi bi-person-circle me-2" style="color:var(--primary)"></i>Edit Profil</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">Perbarui informasi profil dan kata sandi Anda</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-header"><h6>Profil: {{ $user->name }}</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name',$user->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username"
                                   class="form-control @error('username') is-invalid @enderror"
                                   value="{{ old('username',$user->username) }}" required>
                            @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email',$user->email) }}">
                        </div>

                        <div class="col-12">
                            <div class="p-3" style="background:#fffbeb;border-radius:10px;border:1px solid #fde68a">
                                <div style="font-size:.82rem;font-weight:600;color:#854d0e;margin-bottom:10px">
                                    <i class="bi bi-lock me-1"></i>Ganti Password (kosongkan jika tidak ingin diubah)
                                </div>
                                <div class="mb-2" style="font-size:.8rem;color:#92400e"><strong>Isi password baru untuk mengganti password</strong></div>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <input type="password" name="password" id="password"
                                                   class="form-control @error('password') is-invalid @enderror"
                                                   placeholder="Password baru (min. 6 karakter)">
                                            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password', this)"><i class="bi bi-eye"></i></button>
                                        </div>
                                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <input type="password" name="password_confirmation" id="password_confirmation"
                                                    class="form-control" placeholder="Konfirmasi password baru">
                                            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password_confirmation', this)"><i class="bi bi-eye"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-4 text-end">
                            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-2"></i>Simpan Perubahan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePassword(inputId, btn) {
        let input = document.getElementById(inputId);
        let icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }
</script>
@endpush
