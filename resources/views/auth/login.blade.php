<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login &mdash; POS UD. Tani Agung Ngawi</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            margin: 0;
            display: flex;
            background: linear-gradient(145deg, #0d4f26 0%, #1e8449 50%, #239b56 100%);
        }
        .login-left {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            color: #fff;
        }
        .brand-icon {
            width: 72px; height: 72px;
            background: rgba(255,255,255,.18); border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            font-size: 34px; margin-bottom: 22px;
        }
        .login-left h1 { font-size: 1.7rem; font-weight: 700; text-align: center; margin-bottom: 10px; }
        .login-left p { font-size: .9rem; opacity: .78; text-align: center; max-width: 300px; line-height: 1.6; }
        .login-features { margin-top: 36px; display: flex; flex-direction: column; gap: 14px; }
        .fi { display: flex; align-items: center; gap: 13px; }
        .fi-icon {
            width: 36px; height: 36px;
            background: rgba(255,255,255,.15); border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; flex-shrink: 0;
        }
        .fi-text { font-size: .82rem; opacity: .88; }
        .login-right {
            width: 440px; background: #fff;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            padding: 50px 44px;
            box-shadow: -8px 0 32px rgba(0,0,0,.18);
        }
        .login-right h2 { font-size: 1.4rem; font-weight: 700; color: #1a202c; margin-bottom: 6px; }
        .login-right .sub { font-size: .84rem; color: #6b7280; margin-bottom: 30px; }
        .form-label { font-size: .82rem; font-weight: 600; color: #374151; }
        .form-control { border-radius: 9px; padding: 11px 14px; border: 1.5px solid #e5e7eb; font-size: .88rem; }
        .form-control:focus { border-color: #27ae60; box-shadow: 0 0 0 3px rgba(39,174,96,.15); }
        .input-group-text { border-radius: 9px 0 0 9px; background: #f9fafb; border: 1.5px solid #e5e7eb; border-right: none; color: #6b7280; }
        .input-group .form-control { border-radius: 0 9px 9px 0; border-left: none; }
        .input-group .form-control:focus { border-left: 1.5px solid #27ae60; }
        .btn-login {
            width: 100%; padding: 12px;
            background: linear-gradient(135deg, #1e8449, #27ae60);
            border: none; border-radius: 10px;
            color: #fff; font-weight: 700; font-size: .9rem;
            cursor: pointer; transition: .2s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-login:hover { background: linear-gradient(135deg, #145a32, #1e8449); transform: translateY(-1px); box-shadow: 0 4px 14px rgba(30,132,73,.35); }
        .err-box { border-radius: 9px; font-size: .83rem; border: none; background: #fef2f2; color: #991b1b; border-left: 4px solid #dc2626; padding: 10px 14px; width: 100%; margin-bottom: 16px; }
        .login-footer { font-size: .75rem; color: #9ca3af; text-align: center; margin-top: 28px; }
        @media (max-width: 768px) {
            body { flex-direction: column; }
            .login-left { padding: 30px 24px; min-height: 200px; }
            .login-features { display: none; }
            .login-right { width: 100%; padding: 32px 24px; box-shadow: none; }
        }
    </style>
</head>
<body>
<div class="login-left">
    <div class="brand-icon"><i class="bi bi-shop-window"></i></div>
    <h1>Testing Push Github</h1>
    <p>Sistem Informasi Point of Sales terintegrasi dengan Manajemen Keanggotaan</p>
    <div class="login-features">
        <div class="fi"><div class="fi-icon"><i class="bi bi-cart3"></i></div><div class="fi-text">Transaksi kasir cepat dengan pencarian produk live</div></div>
        <div class="fi"><div class="fi-icon"><i class="bi bi-award"></i></div><div class="fi-text">Sistem membership Bronze / Silver / Gold otomatis</div></div>
        <div class="fi"><div class="fi-icon"><i class="bi bi-bar-chart-line"></i></div><div class="fi-text">Dashboard laporan penjualan &amp; laba kotor real-time</div></div>
        <div class="fi"><div class="fi-icon"><i class="bi bi-box-seam"></i></div><div class="fi-text">Manajemen stok &amp; HPP rata-rata tertimbang otomatis</div></div>
    </div>
</div>

<div class="login-right">
    <h2>Selamat Datang</h2>
    <p class="sub">Masuk dengan akun yang telah diberikan</p>

    @if($errors->any())
        <div class="err-box"><i class="bi bi-exclamation-circle-fill me-2"></i>{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="w-100">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="username">Username</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person"></i></span>
                <input type="text" id="username" name="username"
                       class="form-control @error('username') is-invalid @enderror"
                       value="{{ old('username') }}"
                       placeholder="Masukkan username" autocomplete="username" autofocus required>
            </div>
        </div>
        <div class="mb-4">
            <label class="form-label" for="password">Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" id="password" name="password"
                       class="form-control" placeholder="Masukkan password"
                       autocomplete="current-password" required>
            </div>
        </div>
        <button type="submit" class="btn-login">
            <i class="bi bi-box-arrow-in-right"></i> Masuk ke Sistem
        </button>
    </form>

    <div class="login-footer">&copy; {{ date('Y') }} UD. Tani Agung Ngawi &mdash; POS System v1.0</div>
</div>
</body>
</html>
