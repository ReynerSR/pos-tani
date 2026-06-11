@extends('layouts.app')
@section('title','Laporan Penjualan')
@section('page_title','Laporan Penjualan')
@push('styles')<style>.chart-wrap{position:relative;height:220px}</style>@endpush
@section('content')
@php
$sortLink=function($column,$label) use($sort,$dir){$next=($sort===$column&&$dir==='asc')?'desc':'asc';$icon=$sort===$column?($dir==='asc'?'bi-sort-up':'bi-sort-down'):'bi-arrow-down-up';return '<a class="text-decoration-none text-muted" href="'.request()->fullUrlWithQuery(['sort'=>$column,'dir'=>$next]).'">'.$label.' <i class="bi '.$icon.'"></i></a>';};
@endphp
<div class="page-hdr"><div class="page-hdr-left"><h1><i class="bi bi-bar-chart-line me-2" style="color:var(--primary)"></i>Laporan Penjualan</h1></div></div>
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-6 col-md-2">
                <label class="form-label mb-1 small">Periode</label>
                <select name="period" class="form-select">
                    <option value="day" {{ $period==='day'?'selected':'' }}>Hari</option>
                    <option value="week" {{ $period==='week'?'selected':'' }}>Minggu</option>
                    <option value="month" {{ $period==='month'?'selected':'' }}>Bulan</option>
                    <option value="year" {{ $period==='year'?'selected':'' }}>Tahun</option>
                    <option value="custom" {{ $period==='custom'?'selected':'' }}>Custom</option>
                </select>
            </div>
            <div class="col-6 col-md-2" id="date-from-container">
                <label class="form-label mb-1 small" id="date-from-label">Dari Tanggal</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from',$startAt->toDateString()) }}">
            </div>
            <div class="col-6 col-md-2" id="date-to-container">
                <label class="form-label mb-1 small">Sampai Tanggal</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to',$endAt->toDateString()) }}">
            </div>
            <div class="col-6 col-md-2">
                
                <select name="per_page" class="form-select">
                    <option value="20" {{ request('per_page',20)==20?'selected':'' }}>20 Baris</option>
                    <option value="50" {{ request('per_page')==50?'selected':'' }}>50 Baris</option>
                    <option value="100" {{ request('per_page')==100?'selected':'' }}>100 Baris</option>
                </select>
            </div>
            <div class="col-6 col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100" title="Tampilkan"><i class="bi bi-search"></i></button>
            </div>
        </form>
        <div class="form-text mt-2 mb-0" style="font-size: .8rem">Pilih periode yang sesuai untuk melihat grafik yang tepat.</div>
    </div>
</div>
<div class="row g-3 mb-4"><div class="col-6 col-md-3"><div class="stat-card bg-grad-green"><span class="si"><i class="bi bi-cash-stack"></i></span><div class="sv">Rp {{ number_format($summary->total_revenue ?? 0,0,',','.') }}</div><div class="sl">Total Pendapatan</div></div></div><div class="col-6 col-md-3"><div class="stat-card bg-grad-teal"><span class="si"><i class="bi bi-receipt"></i></span><div class="sv">{{ number_format($summary->total_transactions ?? 0) }}</div><div class="sl">Total Transaksi</div></div></div><div class="col-6 col-md-3"><div class="stat-card bg-grad-orange"><span class="si"><i class="bi bi-graph-down-arrow"></i></span><div class="sv">Rp {{ number_format(($summary->total_discount ?? 0) + ($summary->total_redeem ?? 0),0,',','.') }}</div><div class="sl">Total Diskon + Redeem</div></div></div><div class="col-6 col-md-3"><div class="stat-card bg-grad-purple"><span class="si"><i class="bi bi-calculator"></i></span><div class="sv">Rp {{ number_format($summary->avg_transaction ?? 0,0,',','.') }}</div><div class="sl">Rata-rata Transaksi</div></div></div></div>
<div class="card mb-4"><div class="card-header"><h6>Grafik Penjualan Harian</h6></div><div class="card-body"><div class="chart-wrap"><canvas id="salesChart"></canvas></div></div></div>
<div class="card"><div class="card-header d-flex justify-content-between"><h6 class="mb-0">Detail Transaksi <span class="badge bg-success ms-1">{{ $transactions->total() }}</span></h6><small class="text-muted">{{ $startAt->format('d/m/Y H:i') }} s/d {{ $endAt->format('d/m/Y H:i') }}</small></div><div class="table-wrapper"><table class="table mb-0"><thead><tr><th>{!! $sortLink('transaction_number','No. Transaksi') !!}</th><th>{!! $sortLink('transaction_date','Tanggal') !!}</th><th>Member</th><th>Kasir</th><th>{!! $sortLink('subtotal','Subtotal') !!}</th><th>{!! $sortLink('discount_amount','Diskon') !!}</th><th>{!! $sortLink('point_redeem_amount','Redeem') !!}</th><th>{!! $sortLink('total_price','Total') !!}</th></tr></thead><tbody>@forelse($transactions as $trx)<tr><td><a href="{{ route('kasir.show', ['transaction' => $trx, 'back_url' => request()->fullUrl()]) }}" style="color:var(--primary);font-weight:600;text-decoration:none">{{ $trx->transaction_number }}</a></td><td>{{ $trx->transaction_date->format('d/m/Y H:i') }}</td><td>{{ $trx->customer->full_name ?? 'Umum' }}</td><td>{{ $trx->cashier->name ?? '-' }}</td><td>Rp {{ number_format($trx->subtotal,0,',','.') }}</td><td>{{ $trx->discount_amount>0?'-Rp '.number_format($trx->discount_amount,0,',','.'):'-' }}</td><td>{{ ($trx->point_redeem_amount ?? 0)>0?'-Rp '.number_format($trx->point_redeem_amount,0,',','.'):'-' }}</td><td><strong>Rp {{ number_format($trx->total_price,0,',','.') }}</strong></td></tr>@empty<tr><td colspan="8" class="text-center py-5 text-muted">Tidak ada transaksi pada periode ini</td></tr>@endforelse</tbody></table></div>@if($transactions->hasPages())<div class="card-body border-top">{{ $transactions->withQueryString()->links() }}</div>@endif</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script><script>const dailyData=@json($dailyChart);new Chart(document.getElementById('salesChart'),{type:'line',data:{labels:dailyData.map(d=>d.date),datasets:[{label:'Pendapatan',data:dailyData.map(d=>d.total),borderColor:'#1e8449',backgroundColor:'rgba(30,132,73,.1)',borderWidth:2,fill:true,tension:.35}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{callback:v=>'Rp '+Number(v).toLocaleString('id-ID')}},x:{grid:{display:false}}}}});</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const periodSelect = document.querySelector('select[name="period"]');
        const dateFromLabel = document.getElementById('date-from-label');
        const dateToContainer = document.getElementById('date-to-container');
        const dateFromInput = document.querySelector('input[name="date_from"]');
        const dateToInput = document.querySelector('input[name="date_to"]');

        function toggleDateFields() {
            if (periodSelect.value === 'day') {
                dateFromLabel.textContent = 'Tanggal';
                dateToContainer.classList.add('d-none');
            } else {
                dateFromLabel.textContent = 'Dari Tanggal';
                dateToContainer.classList.remove('d-none');
            }
        }

        periodSelect.addEventListener('change', function() {
            toggleDateFields();
            
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');
            const todayStr = `${yyyy}-${mm}-${dd}`;
            
            if (this.value === 'day') {
                dateFromInput.value = todayStr;
            } else if (this.value === 'week') {
                const lastWeek = new Date(today);
                lastWeek.setDate(lastWeek.getDate() - 6);
                const lw_yyyy = lastWeek.getFullYear();
                const lw_mm = String(lastWeek.getMonth() + 1).padStart(2, '0');
                const lw_dd = String(lastWeek.getDate()).padStart(2, '0');
                dateFromInput.value = `${lw_yyyy}-${lw_mm}-${lw_dd}`;
                dateToInput.value = todayStr;
            } else if (this.value === 'month') {
                const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                const fd_mm = String(firstDay.getMonth() + 1).padStart(2, '0');
                const fd_dd = String(firstDay.getDate()).padStart(2, '0');
                dateFromInput.value = `${firstDay.getFullYear()}-${fd_mm}-${fd_dd}`;
                
                const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                const ld_mm = String(lastDay.getMonth() + 1).padStart(2, '0');
                const ld_dd = String(lastDay.getDate()).padStart(2, '0');
                dateToInput.value = `${lastDay.getFullYear()}-${ld_mm}-${ld_dd}`;
            } else if (this.value === 'year') {
                dateFromInput.value = `${yyyy}-01-01`;
                dateToInput.value = `${yyyy}-12-31`;
            }
        });
        toggleDateFields();
    });
</script>
@endpush
