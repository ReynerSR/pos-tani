@extends('layouts.app')
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

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card bg-grad-green">
            <span class="si"><i class="bi bi-cash-stack"></i></span>
            <div class="sv">Rp {{ number_format($revenueToday,0,',','.') }}</div>
            <div class="sl">Pendapatan Hari Ini</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card bg-grad-teal">
            <span class="si"><i class="bi bi-receipt"></i></span>
            <div class="sv">{{ $transactionsToday }}</div>
            <div class="sl">Transaksi Hari Ini</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card bg-grad-orange">
            <span class="si"><i class="bi bi-people"></i></span>
            <div class="sv">{{ number_format($totalCustomers) }}</div>
            <div class="sl">Total Member</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        @if($lowStockCount > 0)
        <div class="stat-card bg-grad-red">
            <span class="si"><i class="bi bi-exclamation-triangle"></i></span>
            <div class="sv">{{ $lowStockCount }}</div>
            <div class="sl">Stok Kritis</div>
        </div>
        @else
        <div class="stat-card bg-grad-purple">
            <span class="si"><i class="bi bi-graph-up"></i></span>
            <div class="sv">Rp {{ number_format($revenueThisMonth,0,',','.') }}</div>
            <div class="sl">Pendapatan Bulan Ini</div>
        </div>
        @endif
    </div>
</div>

@if($grossProfit !== null)
<div class="row g-3 mb-4">
    <div class="col-12 col-md-6">
        <div class="card p-4" style="border-left:4px solid var(--primary)">
            <div class="d-flex align-items-center gap-3">
                <div style="width:48px;height:48px;background:var(--primary-pale);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;color:var(--primary)"><i class="bi bi-graph-up-arrow"></i></div>
                <div>
                    <div style="font-size:.72rem;color:#6b7280;font-weight:700;text-transform:uppercase;letter-spacing:.5px">Laba Kotor Bulan Ini</div>
                    <div style="font-size:1.4rem;font-weight:700;color:var(--primary-dark)">Rp {{ number_format($grossProfit,0,',','.') }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="card p-4" style="border-left:4px solid #f39c12">
            <div class="d-flex align-items-center gap-3">
                <div style="width:48px;height:48px;background:#fffbeb;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;color:#f39c12"><i class="bi bi-cash-coin"></i></div>
                <div>
                    <div style="font-size:.72rem;color:#6b7280;font-weight:700;text-transform:uppercase;letter-spacing:.5px">Pendapatan Bulan Ini</div>
                    <div style="font-size:1.4rem;font-weight:700;color:#92400e">Rp {{ number_format($revenueThisMonth,0,',','.') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-12 col-xl-8">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6><i class="bi bi-bar-chart-line me-2" style="color:var(--primary)"></i>Penjualan 7 Hari Terakhir</h6>
                <a href="{{ route('reports.sales') }}" class="btn btn-sm btn-outline-primary">Lihat Laporan</a>
            </div>
            <div class="card-body"><div class="chart-wrap"><canvas id="revenueChart"></canvas></div></div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
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
</div>

<div class="row g-3">
    <div class="col-12 col-xl-7">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6><i class="bi bi-clock-history me-2" style="color:var(--primary)"></i>Transaksi Terbaru</h6>
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
    <div class="col-12 col-xl-5">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6><i class="bi bi-exclamation-triangle me-2 text-warning"></i>Stok Kritis</h6>
                @if(in_array(auth()->user()->role,['pemilik','admin']))
                <a href="{{ route('stock.create') }}" class="btn btn-sm btn-outline-primary">Opname</a>
                @endif
            </div>
            <div class="card-body py-2">
                @forelse($lowStockProducts as $p)
                <div class="stock-row">
                    <div style="flex:1;min-width:0">
                        <div style="font-size:.83rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $p->product_name }}</div>
                        <div style="font-size:.71rem;color:#9ca3af">{{ $p->category }}</div>
                        @php $pct = $p->minimum_stock > 0 ? min(100,round(($p->stock/$p->minimum_stock)*100)) : 0; @endphp
                        <div class="progress mt-1" style="max-width:130px">
                            <div class="progress-bar" style="width:{{ $pct }}%;background:{{ $p->stock<=0?'#e74c3c':'#f39c12' }}"></div>
                        </div>
                    </div>
                    <div class="ms-3 text-end">
                        <span class="{{ $p->stock<=0?'badge-stock-empty':'badge-stock-low' }}">{{ $p->stock }} {{ $p->unit }}</span>
                        <div style="font-size:.69rem;color:#9ca3af;margin-top:3px">Min: {{ $p->minimum_stock }}</div>
                    </div>
                </div>
                @empty
                <div class="text-center py-4" style="color:#27ae60">
                    <i class="bi bi-check-circle-fill" style="font-size:1.8rem;display:block;margin-bottom:6px"></i>
                    <span style="font-size:.83rem;font-weight:600">Semua stok aman</span>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function(){
    const labels = @json($chartLabels);
    const values = @json($chartValues);
    new Chart(document.getElementById('revenueChart'),{
        type:'bar',
        data:{labels,datasets:[{label:'Pendapatan',data:values,backgroundColor:'rgba(30,132,73,.75)',borderColor:'#1e8449',borderWidth:1.5,borderRadius:6}]},
        options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{font:{size:10},callback:v=>'Rp '+(v>=1000000?(v/1000000).toFixed(1)+'jt':(v>=1000?(v/1000).toFixed(0)+'rb':v))},grid:{color:'#f3f4f6'}},x:{ticks:{font:{size:10}},grid:{display:false}}}}
    });
    new Chart(document.getElementById('tierChart'),{
        type:'doughnut',
        data:{labels:['Gold','Silver','Bronze'],datasets:[{data:[{{ $tierStats['gold'] ?? 0 }},{{ $tierStats['silver'] ?? 0 }},{{ $tierStats['bronze'] ?? 0 }}],backgroundColor:['#f39c12','#6b7280','#e74c3c'],borderWidth:2,borderColor:'#fff'}]},
        options:{responsive:true,maintainAspectRatio:false,cutout:'65%',plugins:{legend:{display:false}}}
    });
})();
</script>
@endpush
