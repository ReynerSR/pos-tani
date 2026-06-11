<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login &mdash; POS UD. Tani Agung Ngawi</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1e8449; /* Hijau Pertanian */
            --primary-light: #27ae60;
            --primary-dark: #0d4f26;
            --text-main: #1a202c;
            --text-muted: #6b7280;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-400: #9ca3af;
        }

        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            min-height: 100vh;
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--primary-dark);
        }

        /* Animated Rich Gradient Background (Hijau Pertanian) */
        .bg-animated {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: -2;
            background: linear-gradient(-45deg, #0d4f26, #1e8449, #239b56, #0b5345);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Background Pattern Overlay */
        .bg-pattern {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: -1;
            background-image: radial-gradient(rgba(255, 255, 255, 0.15) 1px, transparent 1px);
            background-size: 30px 30px;
            opacity: 0.8;
        }

        /* Floating Light Orbs */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: -1;
            animation: float 10s infinite ease-in-out alternate;
        }
        .orb-1 {
            width: 400px; height: 400px;
            background: rgba(39, 174, 96, 0.4);
            top: -100px; left: -100px;
        }
        .orb-2 {
            width: 300px; height: 300px;
            background: rgba(253, 224, 71, 0.2); /* Sentuhan kuning matahari */
            bottom: -50px; right: -50px;
            animation-delay: -5s;
        }

        @keyframes float {
            0% { transform: translateY(0px) scale(1); }
            100% { transform: translateY(40px) scale(1.1); }
        }

        /* Premium Bright Glass Card */
        .login-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 480px;
            padding: 0 20px;
            perspective: 1000px;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 1);
            border-bottom: 1px solid rgba(229, 231, 235, 1);
            border-radius: 28px;
            padding: 45px 40px;
            box-shadow: 0 30px 60px rgba(13, 79, 38, 0.4),
                        0 10px 20px rgba(0, 0, 0, 0.1);
            transform-style: preserve-3d;
            animation: cardEntrance 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
            opacity: 0;
            transform: translateY(40px) rotateX(-5deg);
            position: relative;
            overflow: hidden;
        }


        @keyframes cardEntrance {
            to { opacity: 1; transform: translateY(0) rotateX(0); }
        }

        /* Header Section */
        .card-header-custom {
            text-align: center;
            margin-bottom: 35px;
        }

        .logo-container {
            width: 85px; height: 85px;
            margin: 0 auto 20px;
            background: #ffffff;
            border: 1px solid var(--gray-200);
            border-radius: 22px;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            position: relative;
            overflow: hidden;
        }

        .logo-container img {
            width: 55px; height: 55px; object-fit: contain;
        }

        .app-title {
            color: var(--text-main);
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 8px;
        }

        .app-subtitle {
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 500;
            line-height: 1.5;
        }

        /* Form Inputs */
        .input-group-custom {
            position: relative;
            margin-bottom: 22px;
        }

        .input-group-custom i {
            position: absolute;
            left: 18px; top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            font-size: 1.1rem;
            transition: color 0.3s ease;
            z-index: 2;
        }

        .form-control-custom {
            width: 100%;
            background: #ffffff;
            border: 1.5px solid var(--gray-200);
            color: var(--text-main);
            padding: 14px 16px 14px 50px;
            border-radius: 14px;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }

        .form-control-custom::placeholder {
            color: var(--gray-400);
            font-weight: 400;
        }

        .form-control-custom:focus {
            outline: none;
            background: #ffffff;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(30, 132, 73, 0.15);
        }

        .form-control-custom:focus + i,
        .input-group-custom:focus-within i.bi-person,
        .input-group-custom:focus-within i.bi-lock {
            color: var(--primary);
        }

        /* Toggle Password Button */
        .toggle-password {
            position: absolute;
            right: 14px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; padding: 0;
            color: var(--gray-400);
            cursor: pointer;
            z-index: 2;
            transition: color 0.2s;
        }
        .toggle-password:hover { color: var(--text-main); }

        /* Login Button */
        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: #fff;
            border: none;
            padding: 15px;
            border-radius: 14px;
            font-size: 1.05rem;
            font-weight: 700;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 8px 15px rgba(30, 132, 73, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0; left: -100%; width: 100%; height: 100%;
            background: linear-gradient(90deg, rgba(255,255,255,0), rgba(255,255,255,0.2), rgba(255,255,255,0));
            transition: left 0.5s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 20px rgba(30, 132, 73, 0.4);
            background: linear-gradient(135deg, var(--primary-light), var(--primary));
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:active {
            transform: translateY(1px);
        }

        /* Error Box */
        .err-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 14px 18px;
            border-radius: 12px;
            border-left: 4px solid #dc2626;
            font-size: 0.85rem;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
        }
        
        @keyframes shake {
            10%, 90% { transform: translate3d(-1px, 0, 0); }
            20%, 80% { transform: translate3d(2px, 0, 0); }
            30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
            40%, 60% { transform: translate3d(4px, 0, 0); }
        }

        /* Footer */
        .card-footer-custom {
            margin-top: 30px;
            text-align: center;
            color: var(--gray-400);
            font-size: 0.75rem;
            font-weight: 500;
        }

        /* System Info Badges */
        .sys-badges {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 25px;
        }
        .sys-badge {
            background: var(--gray-100);
            border: 1px solid var(--gray-200);
            color: var(--text-muted);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .sys-badge i { color: var(--primary); font-size: 1rem; }

        @media (max-width: 480px) {
            .glass-card { padding: 35px 25px; }
            .app-title { font-size: 1.35rem; }
        }
    </style>
</head>
<body>

<!-- Animated Background -->
<div class="bg-animated"></div>
<div class="bg-pattern"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>

<div class="login-wrapper">
    <div class="glass-card">
        
        <div class="card-header-custom">
            <div class="logo-container">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" onerror="this.src='https://cdn-icons-png.flaticon.com/512/8258/8258514.png'">
            </div>
            <h1 class="app-title">UD. Tani Agung Ngawi</h1>
            <p class="app-subtitle">Sistem Kasir Terpadu & Manajemen Pertanian Cerdas</p>
        </div>

        @if($errors->any())
            <div class="err-box">
                <i class="bi bi-shield-exclamation fs-5"></i>
                <div>{{ $errors->first() }}</div>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="input-group-custom">
                <input type="text" id="username" name="username"
                       class="form-control-custom @error('username') is-invalid @enderror"
                       value="{{ old('username') }}"
                       placeholder="Masukkan Username" autocomplete="username" autofocus required>
                <i class="bi bi-person"></i>
            </div>
            
            <div class="input-group-custom">
                <input type="password" id="password" name="password"
                       class="form-control-custom" placeholder="Masukkan Password"
                       autocomplete="current-password" required>
                <i class="bi bi-lock"></i>
                <button type="button" class="toggle-password" onclick="togglePass()">
                    <i class="bi bi-eye" id="eyeIcon"></i>
                </button>
            </div>

            <button type="submit" class="btn-login">
                Masuk ke Sistem <i class="bi bi-arrow-right-circle-fill ms-1"></i>
            </button>
        </form>

        <div class="sys-badges">
            <div class="sys-badge"><i class="bi bi-shield-check"></i> Aman & Terenkripsi</div>
            <div class="sys-badge"><i class="bi bi-lightning-charge"></i> Kasir Cepat</div>
        </div>

        <div class="card-footer-custom">
            &copy; {{ date('Y') }} UD. Tani Agung Ngawi &bull; Versi 2.0
        </div>
    </div>
</div>

<script>
    function togglePass() {
        const input = document.getElementById('password');
        const icon = document.getElementById('eyeIcon');
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

</body>
</html>
