<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') &mdash; POS UD. Tani Agung Ngawi</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        :root {
            --primary:        #1e8449;
            --primary-dark:   #145a32;
            --primary-light:  #27ae60;
            --primary-pale:   #eafaf1;
            --accent:         #f39c12;
            --sidebar-w:      262px;
            --topbar-h:       62px;
            --radius:         12px;
            --shadow:         0 2px 8px rgba(0,0,0,.08);
        }
        *, *::before, *::after { box-sizing: border-box; }
        html { overflow-y: scroll; }
        body { font-family: 'Inter', sans-serif; background: #f3f6f9; color: #1a202c; margin: 0; min-height: 100vh; overflow-x: hidden; }

        /* BILAH SAMPING (SIDEBAR) */
        #sidebar {
            position: fixed; top: 0; left: 0;
            width: var(--sidebar-w); height: 100vh;
            background: linear-gradient(175deg, #145a32 0%, #1e8449 55%, #239b56 100%);
            display: flex; flex-direction: column;
            z-index: 1050; overflow-y: auto; overflow-x: hidden;
            transition: transform .28s cubic-bezier(.4,0,.2,1);
        }
        #sidebar::-webkit-scrollbar { width: 3px; }
        #sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.2); border-radius: 3px; }
        .sb-brand { padding: 22px 20px 18px; border-bottom: 1px solid rgba(255,255,255,.13); flex-shrink: 0; }
        .sb-brand-logo { width: 44px; height: 44px; background: rgba(255,255,255,.18); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; color: #fff; margin-bottom: 11px; }
        .sb-brand-name { font-size: .82rem; font-weight: 700; color: #fff; line-height: 1.35; }
        .sb-brand-sub { font-size: .67rem; color: rgba(255,255,255,.6); margin-top: 2px; }
        .sb-nav { padding: 10px 0; flex: 1; }
        .sb-section { font-size: .6rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1.3px; color: rgba(255,255,255,.42); padding: 16px 20px 6px; }
        .sb-link { display: flex; align-items: center; gap: 11px; padding: 10px 20px; min-height: 48px; width: 100%; color: rgba(255,255,255,.78); text-decoration: none; font-size: .86rem; font-weight: 500; border-left: 3px solid transparent; transition: all .18s ease; margin: 2px 0; }
        .sb-link i { font-size: 1.1rem; width: 20px; text-align: center; flex-shrink: 0; }
        .sb-link:hover { background: rgba(255,255,255,.10); color: #fff; border-left-color: rgba(255,255,255,.35); }
        .sb-link.active { background: rgba(255,255,255,.16); color: #fff; border-left-color: var(--accent); font-weight: 600; }
        .sb-footer { padding: 14px 20px; border-top: 1px solid rgba(255,255,255,.13); flex-shrink: 0; }
        .sb-user { display: flex; align-items: center; gap: 10px; }
        .sb-avatar { width: 36px; height: 36px; background: rgba(255,255,255,.22); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .9rem; color: #fff; font-weight: 700; flex-shrink: 0; }
        .sb-uname { font-size: .8rem; font-weight: 600; color: #fff; line-height: 1.2; }
        .sb-urole { font-size: .67rem; color: rgba(255,255,255,.58); }
        .sb-logout { margin-left: auto; background: none; border: none; color: rgba(255,255,255,.58); font-size: 1.05rem; cursor: pointer; padding: 5px 7px; border-radius: 7px; transition: .15s; }
        .sb-logout:hover { background: rgba(255,255,255,.13); color: #fff; }

        /* BILAH ATAS (TOPBAR) */
        #topbar { position: fixed; top: 0; left: var(--sidebar-w); right: 0; height: var(--topbar-h); background: #fff; border-bottom: 1px solid #e5eaea; display: flex; align-items: center; padding: 0 24px; gap: 12px; z-index: 900; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
        .topbar-toggle { display: none; background: none; border: none; font-size: 1.3rem; color: #555; cursor: pointer; padding: 6px 8px; border-radius: 8px; }
        .topbar-toggle:hover { background: #f0f0f0; }
        .topbar-title { font-size: 1rem; font-weight: 600; color: var(--primary-dark); flex: 1; }
        .topbar-right { display: flex; align-items: center; gap: 10px; }
        .topbar-chip { display: flex; align-items: center; gap: 5px; padding: 5px 12px; border-radius: 20px; font-size: .76rem; font-weight: 600; background: #fff3cd; color: #856404; text-decoration: none; border: 1px solid #fde68a; transition: .15s; }
        .topbar-chip:hover { background: #fde68a; color: #78350f; }
        .topbar-chip-danger { display: flex; align-items: center; gap: 5px; padding: 5px 12px; border-radius: 20px; font-size: .76rem; font-weight: 600; background: #fee2e2; color: #991b1b; text-decoration: none; border: 1px solid #fecaca; transition: .15s; }
        .topbar-chip-danger:hover { background: #fecaca; color: #7f1d1d; }
        .topbar-date { font-size: .76rem; color: #6b7280; }

        /* KONTEN UTAMA (MAIN) */
        #main { margin-left: var(--sidebar-w); margin-top: var(--topbar-h); padding: 26px; min-height: calc(100vh - var(--topbar-h)); }

        /* KARTU (CARDS) */
        .card { border: none; border-radius: var(--radius); box-shadow: var(--shadow); background: #fff; }
        .card-header { background: #fff; border-bottom: 1px solid #f0f2f5; border-radius: var(--radius) var(--radius) 0 0 !important; padding: 16px 20px; }
        .card-header h6 { font-size: .9rem; font-weight: 700; color: var(--primary-dark); margin: 0; }

        /* KARTU STATISTIK (STAT CARDS) */
        .stat-card { border-radius: var(--radius); padding: 22px 20px; color: #fff; position: relative; overflow: hidden; box-shadow: 0 4px 18px rgba(0,0,0,.13); }
        .stat-card::after { content: ''; position: absolute; top: -18px; right: -18px; width: 88px; height: 88px; border-radius: 50%; background: rgba(255,255,255,.11); }
        .stat-card .si { font-size: 2.1rem; opacity: .88; display: block; margin-bottom: 10px; }
        .stat-card .sv { font-size: 1.55rem; font-weight: 700; line-height: 1.1; }
        .stat-card .sl { font-size: .74rem; opacity: .85; margin-top: 4px; }
        .bg-grad-green  { background: linear-gradient(135deg, #145a32, #27ae60); }
        .bg-grad-teal   { background: linear-gradient(135deg, #0d7a72, #17a589); }
        .bg-grad-orange { background: linear-gradient(135deg, #c87000, #f39c12); }
        .bg-grad-purple { background: linear-gradient(135deg, #5b2192, #8e44ad); }
        .bg-grad-red    { background: linear-gradient(135deg, #922b21, #e74c3c); }

        /* TABEL (TABLE) */
        .table-wrapper { overflow-x: auto; border-radius: 0 0 var(--radius) var(--radius); }
        .table th { font-size: .73rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: #6b7280; background: #f9fafb; padding: 11px 14px; border-top: none; white-space: nowrap; }
        .table td { padding: 11px 14px; font-size: .855rem; vertical-align: middle; border-color: #f3f4f6; }
        .table tbody tr:hover { background: #f7fef9; }

        /* LABEL STATUS (BADGES) */
        .badge-tier { display: inline-flex; align-items: center; gap: 4px; font-size: .7rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: .5px; }
        .badge-gold   { background: #fef3c7; color: #92400e; }
        .badge-silver { background: #e5e7eb; color: #374151; }
        .badge-bronze { background: #fee2e2; color: #991b1b; }
        .badge-stock-ok    { background: #d1fae5; color: #065f46; font-size:.72rem; font-weight:700; padding:3px 9px; border-radius:20px; }
        .badge-stock-low   { background: #fef3c7; color: #92400e; font-size:.72rem; font-weight:700; padding:3px 9px; border-radius:20px; }
        .badge-stock-empty { background: #fee2e2; color: #991b1b; font-size:.72rem; font-weight:700; padding:3px 9px; border-radius:20px; }

        /* TOMBOL (BUTTONS) */
        .btn-primary { background: var(--primary); border-color: var(--primary); font-weight: 600; }
        .btn-primary:hover, .btn-primary:focus { background: var(--primary-dark); border-color: var(--primary-dark); }
        .btn-outline-primary { color: var(--primary); border-color: var(--primary); font-weight: 600; }
        .btn-outline-primary:hover { background: var(--primary); border-color: var(--primary); }
        .btn-sm { font-size: .78rem; }
        .btn-icon { width: 30px; height: 30px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 7px; }

        /* FORMULIR (FORM) */
        .form-control:focus, .form-select:focus { border-color: var(--primary-light); box-shadow: 0 0 0 3px rgba(30,132,73,.15); }
        .form-label { font-size: .82rem; font-weight: 600; color: #374151; margin-bottom: 5px; }

        /* HEADER HALAMAN (PAGE HEADER) */
        .page-hdr { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 22px; flex-wrap: wrap; gap: 12px; }
        .page-hdr-left h1 { font-size: 1.3rem; font-weight: 700; color: var(--primary-dark); margin: 0 0 4px; }
        .page-hdr-left .breadcrumb { font-size: .77rem; margin: 0; }
        .breadcrumb-item a { color: var(--primary); text-decoration: none; }
        .breadcrumb-item.active { color: #6b7280; }

        /* PERINGATAN (ALERTS) */
        .alert { border: none; border-radius: 10px; font-size: .855rem; border-left: 4px solid; }
        .alert-success { border-left-color: var(--primary); background: #f0fdf4; color: #166534; }
        .alert-danger  { border-left-color: #dc2626; background: #fef2f2; color: #991b1b; }
        .alert-warning { border-left-color: var(--accent); background: #fffbeb; color: #854d0e; }
        .alert-info    { border-left-color: #0891b2; background: #f0f9ff; color: #075985; }

        /* PAGINASI (PAGINATION) */
        .pagination .page-link { color: var(--primary); border-radius: 7px !important; margin: 0 2px; font-size: .82rem; }
        .pagination .page-item.active .page-link { background: var(--primary); border-color: var(--primary); }

        /* PENCARIAN (SEARCH) */
        .search-bar { position: relative; }
        .search-bar input { padding-left: 36px; border-radius: 8px; }
        .search-bar .si-search { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: .9rem; }

        /* TAMPILAN SELULER (MOBILE) */
        #sb-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 1040; }
        @media (max-width: 992px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.open { transform: translateX(0); }
            #sb-overlay.show { display: block; }
            #topbar { left: 0; }
            #main { margin-left: 0; }
            .topbar-toggle { display: inline-flex; align-items: center; }
        }
        @media (max-width: 576px) { #main { padding: 14px; } }
    </style>
    @stack('styles')
</head>
<body>

<div id="sb-overlay" onclick="sbClose()"></div>

<!-- BILAH SAMPING (SIDEBAR) -->
<aside id="sidebar">
    <div class="sb-brand">
        <div class="sb-brand-logo"><img src="{{ asset('images/logo.png') }}" alt="Logo" style="width:100%; height:100%; object-fit:contain; border-radius:12px;"></div>
        <div class="sb-brand-name">UD. Tani Agung Ngawi</div>
        <div class="sb-brand-sub">Sistem Informasi POS &amp; Membership</div>
    </div>

    <nav class="sb-nav">
        <a href="{{ route('dashboard') }}" class="sb-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        @if(in_array(auth()->user()->role, ['pemilik','admin','kasir']))
        <div class="sb-section">Transaksi Kasir</div>
        <a href="{{ route('kasir.pos') }}" class="sb-link {{ request()->routeIs('kasir.pos') ? 'active' : '' }}">
            <i class="bi bi-cart3"></i> Kasir / POS
        </a>
        <a href="{{ route('kasir.history') }}" class="sb-link {{ request()->routeIs('kasir.history','kasir.show') ? 'active' : '' }}">
            <i class="bi bi-clock-history"></i> Riwayat Transaksi
        </a>
        @endif

        @if(in_array(auth()->user()->role, ['pemilik','admin','kasir']))
        <div class="sb-section">Data Master</div>
        <a href="{{ route('products.index') }}" class="sb-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
            <i class="bi bi-box-seam"></i> Data Produk
        </a>
        <a href="{{ route('customers.index') }}" class="sb-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Data Pelanggan
        </a>
        @endif

        @if(in_array(auth()->user()->role, ['pemilik','admin']))
        <a href="{{ route('suppliers.index') }}" class="sb-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
            <i class="bi bi-truck"></i> Data Supplier
        </a>
        <a href="{{ route('warehouses.index') }}" class="sb-link {{ request()->routeIs('warehouses.*') ? 'active' : '' }}">
            <i class="bi bi-building"></i> Data Gudang
        </a>

        <div class="sb-section">Manajemen Stok</div>
        <a href="{{ route('purchases.index') }}" class="sb-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}">
            <i class="bi bi-receipt-cutoff"></i> Pembelian & Restok
        </a>
        <a href="{{ route('stock-transfers.index') }}" class="sb-link {{ request()->routeIs('stock-transfers.*') ? 'active' : '' }}">
            <i class="bi bi-arrow-left-right"></i> Transfer Stok
        </a>
        <a href="{{ route('stock.index') }}" class="sb-link {{ request()->routeIs('stock.*') ? 'active' : '' }}">
            <i class="bi bi-clipboard2-check"></i> Stock Opname
        </a>

        <div class="sb-section">Marketing & Promosi</div>
        <a href="{{ route('promotions.index') }}" class="sb-link {{ request()->routeIs('promotions.*') ? 'active' : '' }}">
            <i class="bi bi-tag"></i> Diskon & Promo
        </a>
        @endif

        @if(auth()->user()->isPemilik())
        <a href="{{ route('membership.index') }}" class="sb-link {{ request()->routeIs('membership.*') ? 'active' : '' }}">
            <i class="bi bi-award"></i> Pengaturan Membership
        </a>

        <div class="sb-section">Laporan & Analitik</div>
        <a href="{{ route('reports.sales') }}" class="sb-link {{ request()->routeIs('reports.sales') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line"></i> Laporan Penjualan
        </a>
        <a href="{{ route('reports.profit') }}" class="sb-link {{ request()->routeIs('reports.profit') ? 'active' : '' }}">
            <i class="bi bi-graph-up-arrow"></i> Laporan Laba Kotor
        </a>

        <div class="sb-section">Pengaturan Sistem</div>
        <a href="{{ route('users.index') }}" class="sb-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <i class="bi bi-person-gear"></i> Pengguna Sistem
        </a>
        <a href="{{ route('reports.activity') }}" class="sb-link {{ request()->routeIs('reports.activity') ? 'active' : '' }}">
            <i class="bi bi-shield-check"></i> Log Aktivitas Sistem
        </a>
        @endif
    </nav>

    <div class="sb-footer">
        <div class="sb-user">
            <div class="sb-avatar">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</div>
            <div>
                <div class="sb-uname">{{ auth()->user()->name }}</div>
                <div class="sb-urole">{{ auth()->user()->role_label }}</div>
            </div>
            <div style="margin-left: auto; display: flex; gap: 2px;">
                <a href="{{ route('profile.edit') }}" class="sb-logout" style="margin-left: 0; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;" title="Edit Profil">
                    <i class="bi bi-pencil-square"></i>
                </a>
                <!-- Tombol logout dengan konfirmasi -->
                <button type="button" class="sb-logout" style="margin-left: 0; display: inline-flex; align-items: center; justify-content: center;" title="Keluar" onclick="confirmLogout()">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </div>
            <form id="logout-form" method="POST" action="{{ route('logout') }}" class="d-none">@csrf</form>
        </div>
    </div>
</aside>

<!-- BILAH ATAS (TOPBAR) -->
<header id="topbar">
    <button class="topbar-toggle" onclick="sbToggle()"><i class="bi bi-list"></i></button>
    <div class="topbar-title">@yield('page_title', 'Dashboard')</div>
    <div class="topbar-right">
        @php
            $emptyStockCount = \App\Models\Product::where('is_active', true)
                ->where('stock', '<=', 0)->count();
            $lowStockCount = \App\Models\Product::where('is_active', true)
                ->where('stock', '>', 0)
                ->whereColumn('stock','<=','minimum_stock')->count();
        @endphp
        @if($lowStockCount > 0)
            <a href="{{ route('products.index', ['status'=>'low']) }}" class="topbar-chip">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ $lowStockCount }} Stok Kritis
            </a>
        @endif
        @if($emptyStockCount > 0)
            <a href="{{ route('products.index', ['status'=>'empty']) }}" class="topbar-chip-danger">
                <i class="bi bi-exclamation-octagon-fill"></i> {{ $emptyStockCount }} Stok Habis
            </a>
        @endif
        <span class="topbar-date d-none d-md-block">{{ now()->translatedFormat('l, d F Y') }}</span>
    </div>
</header>

<!-- KONTEN UTAMA (MAIN) -->
<main id="main">
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: `{{ session('success') }}`,
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            });
        </script>
    @endif
    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: `{{ session('error') }}`,
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true
                });
            });
        </script>
    @endif
    @if(session('warning'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'warning',
                    title: `{{ session('warning') }}`,
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true
                });
            });
        </script>
    @endif

    @yield('content')
</main>

<!-- Modal Konfirmasi Logout -->
<div class="modal fade" id="logoutModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border-radius:14px;border:none">
            <div class="modal-body text-center p-4">
                <div style="width:56px;height:56px;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:24px;color:#dc2626;margin:0 auto 16px">
                    <i class="bi bi-box-arrow-right"></i>
                </div>
                <h6 style="font-weight:700;margin-bottom:6px">Konfirmasi Logout</h6>
                <p style="font-size:.84rem;color:#6b7280;margin-bottom:20px">Apakah Anda yakin ingin keluar dari sistem?</p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger px-4" onclick="localStorage.removeItem('pos_tani_draft_v2'); document.getElementById('logout-form').submit()">
                        <i class="bi bi-box-arrow-right me-1"></i>Ya, Keluar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function sbToggle() {
        document.getElementById('sidebar').classList.toggle('open');
        document.getElementById('sb-overlay').classList.toggle('show');
    }
    function sbClose() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sb-overlay').classList.remove('show');
    }
    function confirmLogout() {
        new bootstrap.Modal(document.getElementById('logoutModal')).show();
    }

    // Format otomatis input mata uang rupiah
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.rupiah-input').forEach(input => {
            let isNegative = input.value.startsWith('-');
            let val = input.value.replace(/[^0-9]/g, '');
            if(val !== '') {
                input.value = (isNegative ? '-' : '') + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }
        });
    });

    document.addEventListener('input', function(e) {
        if (e.target && e.target.classList.contains('rupiah-input')) {
            let isNegative = e.target.value.startsWith('-');
            let val = e.target.value.replace(/[^0-9]/g, '');
            if(val !== '') {
                e.target.value = (isNegative ? '-' : '') + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            } else {
                e.target.value = '';
            }
        }
    });

    document.addEventListener('submit', function(e) {
        if(e.target && e.target.nodeName === 'FORM') {
            let inputs = e.target.querySelectorAll('.rupiah-input');
            inputs.forEach(input => {
                input.value = input.value.replace(/\./g, '');
            });
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const dateFromInputs = document.querySelectorAll('input[type="date"][name="date_from"], input[type="date"][name="start_date"]');
        dateFromInputs.forEach(dateFrom => {
            const form = dateFrom.closest('form');
            if (form) {
                const dateTo = form.querySelector('input[type="date"][name="date_to"], input[type="date"][name="end_date"]');
                if (dateTo) {
                    const updateMinMax = () => {
                        if (dateFrom.value) dateTo.min = dateFrom.value;
                        if (dateTo.value) dateFrom.max = dateTo.value;
                        if (dateFrom.value && dateTo.value && dateTo.value < dateFrom.value) {
                            dateTo.value = dateFrom.value;
                        }
                    };
                    dateFrom.addEventListener('change', updateMinMax);
                    dateTo.addEventListener('change', updateMinMax);
                    updateMinMax(); // Pengaturan awal saat halaman dimuat
                }
            }
        });
    });
</script>
@stack('scripts')
</body>
</html>
