<!-- Menggunakan template layout utama aplikasi -->
@extends('layouts.app')

<!-- Konfigurasi judul halaman -->
@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@push('styles')
<style>
.chart-wrap { position:relative; height:240px; }
.stock-row { display:flex; align-items:center; justify-content:space-between; padding:9px 0; border-bottom:1px solid #f3f4f6; }
.stock-row:last-child { border-bottom:none; }
.progress { height:5px; border-radius:5px; }
</style>
@endpush

@section('content')
<div class="page-hdr">
    <div class="page-hdr-left">
        <h1><i class="bi bi-speedometer2 me-2" style="color:var(--primary)"></i>Dashboard</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item active">Ringkasan hari ini</li></ol></nav>
    </div>
    @if(in_array(auth()->user()->role,['pemilik','admin','kasir']))
    <a href="{{ route('kasir.pos') }}" class="btn btn-primary px-4">
        <i class="bi bi-cart-plus me-2"></i>Transaksi Baru
    </a>
    @endif
</div>

<!-- Kartu Statistik Utama (Tampilan berbeda antara kasir dan pemilik/admin) -->
@if(auth()->user()->role === 'kasir')
<div class="row g-3 mb-4">
    <div class="col-12 col-md-6">
        <div class="stat-card bg-grad-green">
            <span class="si"><i class="bi bi-cash-stack"></i></span>
            <div class="sv">Rp {{ number_format($revenueToday,0,',','.') }}</div>
            <div class="sl">Total Kas Masuk (Shift Anda Hari Ini)</div>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="stat-card bg-grad-teal">
            <span class="si"><i class="bi bi-receipt"></i></span>
            <div class="sv">{{ $transactionsToday }}</div>
            <div class="sl">Total Transaksi (Shift Anda Hari Ini)</div>
        </div>
    </div>
</div>
@else
<div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-xl-3">
        <a href="{{ auth()->user()->role === 'pemilik' ? route('reports.sales', ['period' => 'day']) : route('kasir.history') }}" class="text-decoration-none">
        <div class="stat-card bg-grad-green" style="transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
            <span class="si"><i class="bi bi-cash-stack"></i></span>
            <div class="sv">Rp {{ number_format($revenueToday,0,',','.') }}</div>
            <div class="sl">Pendapatan Hari Ini</div>
        </div>
        </a>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <a href="{{ route('kasir.history') }}" class="text-decoration-none">
        <div class="stat-card bg-grad-teal" style="transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
            <span class="si"><i class="bi bi-receipt"></i></span>
            <div class="sv">{{ $transactionsToday }}</div>
            <div class="sl">Transaksi Hari Ini</div>
        </div>
        </a>
    </div>
    <div class="col-12 col-md-4 col-xl-2">
        <a href="{{ route('customers.index') }}" class="text-decoration-none">
        <div class="stat-card bg-grad-purple" style="transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
            <span class="si"><i class="bi bi-people"></i></span>
            <div class="sv">{{ number_format($totalCustomers) }}</div>
            <div class="sl">Total Member</div>
        </div>
        </a>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <a href="{{ route('products.index', ['status' => 'low']) }}" class="text-decoration-none">
        <div class="stat-card bg-grad-orange" style="transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
            <span class="si"><i class="bi bi-exclamation-triangle"></i></span>
            <div class="sv">{{ $lowStockCount }}</div>
            <div class="sl">Stok Kritis</div>
        </div>
        </a>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <a href="{{ route('products.index', ['status' => 'empty']) }}" class="text-decoration-none">
        <div class="stat-card bg-grad-red" style="transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
            <span class="si"><i class="bi bi-exclamation-octagon-fill"></i></span>
            <div class="sv">{{ $emptyStockCount }}</div>
            <div class="sl">Stok Habis</div>
        </div>
        </a>
    </div>
</div>
@endif

<!-- Menampilkan bagian pendapatan dan laba kotor jika data tersedia (biasanya hanya untuk pemilik) -->
@if($grossProfit !== null)
<div class="row g-3 mb-4">
    <div class="col-12 col-md-6">
        <a href="{{ route('reports.sales', ['period' => 'month']) }}" class="text-decoration-none">
        <div class="card p-4 h-100" style="border-left:4px solid var(--primary); transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
            <div class="d-flex align-items-center gap-3">
                <div style="width:48px;height:48px;background:var(--primary-pale);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;color:var(--primary)"><i class="bi bi-graph-up-arrow"></i></div>
                <div>
                    <div style="font-size:.72rem;color:#6b7280;font-weight:700;text-transform:uppercase;letter-spacing:.5px">Pendapatan Bulan Ini</div>
                    <div style="font-size:1.4rem;font-weight:700;color:var(--primary-dark)">Rp {{ number_format($revenueThisMonth,0,',','.') }}</div>
                </div>
            </div>
        </div>
        </a>
    </div>
    <div class="col-12 col-md-6">
        <a href="{{ route('reports.profit', ['period' => 'month']) }}" class="text-decoration-none">
        <div class="card p-4 h-100" style="border-left:4px solid #f39c12; transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
            <div class="d-flex align-items-center gap-3">
                <div style="width:48px;height:48px;background:#fffbeb;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;color:#f39c12"><i class="bi bi-cash-coin"></i></div>
                <div>
                    <div style="font-size:.72rem;color:#6b7280;font-weight:700;text-transform:uppercase;letter-spacing:.5px">Laba Kotor Bulan Ini</div>
                    <div style="font-size:1.4rem;font-weight:700;color:#92400e">Rp {{ number_format($grossProfit,0,',','.') }}</div>
                </div>
            </div>
        </div>
        </a>
    </div>
</div>
@endif

<!-- Grafik dan Analitik Khusus untuk Role Selain Kasir (Pemilik/Admin) -->
@if(auth()->user()->role !== 'kasir')
<div class="row g-3 mb-4">
    @if(auth()->user()->role === 'pemilik')
    <div class="col-12 col-xl-8">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6><i class="bi bi-bar-chart-line me-2" style="color:var(--primary)"></i>Penjualan 7 Hari Terakhir</h6>
                <a href="{{ route('reports.sales') }}" class="btn btn-sm btn-outline-primary">Lihat Laporan</a>
            </div>
            <div class="card-body"><div class="chart-wrap"><canvas id="revenueChart"></canvas></div></div>
        </div>
    </div>
    @endif
    <div class="col-12 {{ auth()->user()->role === 'pemilik' ? 'col-xl-4' : 'col-xl-6' }}">
        <div class="card h-100">
            <div class="card-header"><h6><i class="bi bi-pie-chart me-2" style="color:var(--primary)"></i>Komposisi Member</h6></div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <div style="height:180px;width:100%;max-width:200px"><canvas id="tierChart"></canvas></div>
                <div class="d-flex gap-3 mt-3 flex-wrap justify-content-center" style="font-size:.77rem">
                    <span><span style="display:inline-block;width:10px;height:10px;background:#f39c12;border-radius:50%;margin-right:4px"></span>Gold ({{ $tierStats['gold'] ?? 0 }})</span>
                    <span><span style="display:inline-block;width:10px;height:10px;background:#6b7280;border-radius:50%;margin-right:4px"></span>Silver ({{ $tierStats['silver'] ?? 0 }})</span>
                    <span><span style="display:inline-block;width:10px;height:10px;background:#e74c3c;border-radius:50%;margin-right:4px"></span>Bronze ({{ $tierStats['bronze'] ?? 0 }})</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 {{ auth()->user()->role === 'pemilik' ? 'col-xl-12' : 'col-xl-6' }}">
        <div class="card h-100">
            <div class="card-header"><h6><i class="bi bi-graph-up-arrow me-2" style="color:var(--primary)"></i>Barang Terlaris Bulan Ini</h6></div>
            <div class="card-body"><div class="chart-wrap" style="height: 250px;"><canvas id="trendingChart"></canvas></div></div>
        </div>
    </div>
</div>
@endif

<!-- Bagian Tabel Transaksi Terbaru dan Performa Kasir -->
<div class="row g-3">
    <div class="col-12 {{ auth()->user()->role === 'kasir' ? 'col-xl-12' : 'col-xl-7' }}">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6><i class="bi bi-clock-history me-2" style="color:var(--primary)"></i>{{ auth()->user()->role === 'kasir' ? 'Transaksi Shift Anda (Hari Ini)' : 'Transaksi Terbaru' }}</h6>
                <a href="{{ route('kasir.history') }}" class="btn btn-sm btn-outline-primary">Semua</a>
            </div>
            <div class="table-wrapper">
                <table class="table mb-0" style="font-size:.82rem">
                    <thead><tr><th>No. Transaksi</th><th>Member</th><th>Kasir</th><th>Total</th><th>Jam</th></tr></thead>
                    <tbody>
                        @forelse($recentTransactions as $trx)
                        <tr>
                            <td><a href="{{ route('kasir.show',$trx) }}" style="color:var(--primary);font-weight:600;text-decoration:none">{{ $trx->transaction_number }}</a></td>
                            <td>
                                @if($trx->customer)
                                    <span class="badge-tier badge-{{ $trx->customer->tier }}">{{ strtoupper(substr($trx->customer->tier,0,1)) }}</span>
                                    {{ Str::limit($trx->customer->full_name,16) }}
                                @else
                                    <span style="color:#9ca3af;font-size:.76rem">Umum</span>
                                @endif
                            </td>
                            <td>{{ $trx->cashier->name ?? '-' }}</td>
                            <td style="font-weight:600">Rp {{ number_format($trx->total_price,0,',','.') }}</td>
                            <td style="color:#9ca3af">{{ $trx->transaction_date->format('H:i') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-4" style="color:#9ca3af"><i class="bi bi-receipt" style="font-size:1.8rem;display:block;margin-bottom:6px"></i>Belum ada transaksi</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    @if(auth()->user()->role !== 'kasir')
    <div class="col-12 col-xl-5">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6><i class="bi bi-person-badge me-2" style="color:var(--primary)"></i>Performa Kasir Hari Ini</h6>
                <a href="{{ route('kasir.history', ['date_from' => \Carbon\Carbon::today()->toDateString(), 'date_to' => \Carbon\Carbon::today()->toDateString()]) }}" class="btn btn-sm btn-outline-primary">Riwayat Kasir</a>
            </div>
            <div class="card-body p-0">
                @forelse($cashierPerformance as $perf)
                <div class="d-flex align-items-center justify-content-between p-3" style="border-bottom:1px solid #f3f4f6;">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:40px;height:40px;background:var(--primary-pale);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--primary);font-weight:700;font-size:1.1rem">
                            {{ strtoupper(substr($perf->name, 0, 1)) }}
                        </div>
                        <div>
                            <div style="font-size:.85rem;font-weight:700">{{ $perf->name }}</div>
                            <div style="font-size:.72rem;color:#6b7280">{{ $perf->total_transactions }} Transaksi Selesai</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-end">
                            <div style="font-size:.9rem;font-weight:800;color:var(--primary-dark)">Rp {{ number_format($perf->total_revenue,0,',','.') }}</div>
                            <div style="font-size:.7rem;color:#10b981;font-weight:600"><i class="bi bi-graph-up"></i> Kas Masuk</div>
                        </div>
                        <a href="{{ route('kasir.history', ['cashier_id' => $perf->id, 'date_from' => \Carbon\Carbon::today()->toDateString(), 'date_to' => \Carbon\Carbon::today()->toDateString()]) }}" class="btn btn-sm btn-outline-primary" style="font-size:.72rem; padding: 4px 10px; border-radius: 6px; font-weight: 600;">Lihat Detail</a>
                    </div>
                </div>
                @empty
                <div class="text-center py-5" style="color:#9ca3af">
                    <i class="bi bi-person-x" style="font-size:2.2rem;display:block;margin-bottom:8px"></i>
                    <span style="font-size:.85rem;font-weight:500">Belum ada transaksi kasir hari ini</span>
                </div>
                @endforelse
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

<!-- Script untuk inisialisasi Chart.js (Grafik Penjualan, Member, Barang Terlaris) -->
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function(){
    const labels = @json($chartLabels ?? []);
    const values = @json($chartValues ?? []);
    if(document.getElementById('revenueChart')) {
        const revDates = @json($chartDates ?? []);
        new Chart(document.getElementById('revenueChart'),{
            type:'bar',
            data:{labels,datasets:[{label:'Pendapatan',data:values,backgroundColor:'rgba(30,132,73,.75)',borderColor:'#1e8449',borderWidth:1.5,borderRadius:6}]},
            options:{
                responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},
                scales:{y:{beginAtZero:true,ticks:{font:{size:10},callback:v=>'Rp '+(v>=1000000?(v/1000000).toFixed(1)+'jt':(v>=1000?(v/1000).toFixed(0)+'rb':v))},grid:{color:'#f3f4f6'}},x:{ticks:{font:{size:10}},grid:{display:false}}},
                onClick: (e, activeElements, chart) => {
                    if (activeElements.length > 0) {
                        const index = activeElements[0].index;
                        window.location.href = '/reports/sales?period=day&date_from=' + revDates[index];
                    }
                },
                onHover: (e, activeElements, chart) => {
                    chart.canvas.style.cursor = activeElements.length ? 'pointer' : 'default';
                }
            }
        });
    }
    
    if(document.getElementById('tierChart')) {
        const tierNames = ['gold','silver','bronze'];
        new Chart(document.getElementById('tierChart'),{
            type:'doughnut',
            data:{labels:['Gold','Silver','Bronze'],datasets:[{data:[{{ $tierStats['gold'] ?? 0 }},{{ $tierStats['silver'] ?? 0 }},{{ $tierStats['bronze'] ?? 0 }}],backgroundColor:['#f39c12','#6b7280','#e74c3c'],borderWidth:2,borderColor:'#fff'}]},
            options:{
                responsive:true,maintainAspectRatio:false,cutout:'65%',plugins:{legend:{display:false}},
                onClick: (e, activeElements, chart) => {
                    if (activeElements.length > 0) {
                        const index = activeElements[0].index;
                        window.location.href = '/customers?tier=' + tierNames[index];
                    }
                },
                onHover: (e, activeElements, chart) => {
                    chart.canvas.style.cursor = activeElements.length ? 'pointer' : 'default';
                }
            }
        });
    }

    if(document.getElementById('trendingChart')) {
        const trendLabels = @json($trendingLabels ?? []);
        const trendValues = @json($trendingValues ?? []);
        const trendIds = @json($trendingIds ?? []);
        new Chart(document.getElementById('trendingChart'),{
            type:'bar',
            data:{labels:trendLabels,datasets:[{label:'Terjual (Qty)',data:trendValues,backgroundColor:'rgba(41,128,185,.75)',borderColor:'#2980b9',borderWidth:1.5,borderRadius:6}]},
            options:{
                responsive:true,
                maintainAspectRatio:false,
                plugins:{legend:{display:false}},
                scales:{y:{beginAtZero:true,ticks:{font:{size:10}},grid:{color:'#f3f4f6'}},x:{ticks:{font:{size:10}},grid:{display:false}}},
                onClick: (e, activeElements, chart) => {
                    if (activeElements.length > 0) {
                        const index = activeElements[0].index;
                        window.location.href = '/products/' + trendIds[index];
                    }
                },
                onHover: (e, activeElements, chart) => {
                    chart.canvas.style.cursor = activeElements.length ? 'pointer' : 'default';
                }
            }
        });
    }
})();
</script>
@endpush
