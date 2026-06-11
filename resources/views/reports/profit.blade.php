@extends('layouts.app')
@section('title','Laporan Laba Kotor')
@section('page_title','Laporan Laba Kotor')
@section('content')
@php
$sortLink=function($column,$label) use($sort,$dir){$next=($sort===$column&&$dir==='asc')?'desc':'asc';$icon=$sort===$column?($dir==='asc'?'bi-sort-up':'bi-sort-down'):'bi-arrow-down-up';return '<a class="text-decoration-none text-muted" href="'.request()->fullUrlWithQuery(['sort'=>$column,'dir'=>$next]).'">'.$label.' <i class="bi '.$icon.'"></i></a>';};
$profitMargin=($totalProfit->total_revenue??0)>0?round((($totalProfit->gross_profit??0)/($totalProfit->total_revenue??1))*100,1):0;
@endphp
<div class="page-hdr"><div class="page-hdr-left"><h1><i class="bi bi-graph-up-arrow me-2" style="color:var(--primary)"></i>Laporan Laba Kotor</h1></div></div>
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
<div class="row g-3 mb-4"><div class="col-md-4"><div class="stat-card bg-grad-green"><span class="si"><i class="bi bi-graph-up-arrow"></i></span><div class="sv">Rp {{ number_format($totalProfit->gross_profit ?? 0,0,',','.') }}</div><div class="sl">Total Laba Kotor</div></div></div><div class="col-md-4"><div class="stat-card bg-grad-teal"><span class="si"><i class="bi bi-cash-stack"></i></span><div class="sv">Rp {{ number_format($totalProfit->total_revenue ?? 0,0,',','.') }}</div><div class="sl">Total Pendapatan</div></div></div><div class="col-md-4"><div class="stat-card bg-grad-orange"><span class="si"><i class="bi bi-box-seam"></i></span><div class="sv">Rp {{ number_format($totalProfit->total_hpp ?? 0,0,',','.') }}</div><div class="sl">Total HPP</div></div></div></div>
<div class="card mb-4 p-4" style="border-left:4px solid var(--primary)"><div class="small text-muted text-uppercase fw-bold">Margin Laba Kotor</div><div style="font-size:2rem;font-weight:800;color:{{ $profitMargin>=0?'var(--primary-dark)':'#dc2626' }}">{{ $profitMargin }}%</div><div class="small text-muted">{{ $startAt->format('d/m/Y H:i') }} s/d {{ $endAt->format('d/m/Y H:i') }}</div></div>
<div class="card"><div class="card-header"><h6 class="mb-0">Laba Kotor per Produk</h6></div><div class="table-wrapper"><table class="table mb-0"><thead><tr><th>#</th><th>{!! $sortLink('product_name','Produk') !!}</th><th>{!! $sortLink('total_qty','Qty Terjual') !!}</th><th>{!! $sortLink('total_revenue','Pendapatan') !!}</th><th>{!! $sortLink('total_hpp','Total HPP') !!}</th><th>{!! $sortLink('gross_profit','Laba Kotor') !!}</th><th>Margin</th></tr></thead><tbody>@forelse($profitData as $i=>$row)@php $margin=$row->total_revenue>0?round(($row->gross_profit/$row->total_revenue)*100,1):0;@endphp<tr><td class="text-muted small">{{ $profitData->firstItem()+$i }}</td><td><strong>{{ $row->product_name }}</strong><div class="small text-muted">{{ $row->product_code }}</div></td><td>{{ number_format($row->total_qty) }}</td><td>Rp {{ number_format($row->total_revenue,0,',','.') }}</td><td>Rp {{ number_format($row->total_hpp,0,',','.') }}</td><td><strong>Rp {{ number_format($row->gross_profit,0,',','.') }}</strong></td><td><span class="badge bg-{{ $margin>=20?'success':($margin>=0?'warning':'danger') }}">{{ $margin }}%</span></td></tr>@empty<tr><td colspan="7" class="text-center py-5 text-muted">Tidak ada data pada periode ini</td></tr>@endforelse</tbody></table></div>@if($profitData->hasPages())<div class="card-body border-top">{{ $profitData->withQueryString()->links() }}</div>@endif</div>
@endsection
@push('scripts')
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

        if (periodSelect && dateFromLabel && dateToContainer) {
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
        }
    });
</script>
@endpush
