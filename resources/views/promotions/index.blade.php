@extends('layouts.app')
@section('title','Diskon & Promo')
@section('page_title','Diskon & Promo')

@section('content')
<div class="page-hdr">
    <div class="page-hdr-left">
        <h1><i class="bi bi-tag me-2" style="color:var(--primary)"></i>Diskon & Promo</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item active">Daftar Promo & Diskon</li></ol></nav>
    </div>
    @if(auth()->user()->role === 'pemilik')
    <a href="{{ route('promotions.create') }}" class="btn btn-primary px-4">
        <i class="bi bi-plus-lg me-2"></i>Tambah Promo
    </a>
    @endif
</div>

<!-- Kartu Filter dan Pencarian -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" id="promos-filter-form" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <div class="search-bar">
                    <i class="bi bi-search si-search"></i>
                    <input type="text" name="search" id="promos-search" class="form-control" placeholder="Cari nama promo / produk..." value="{{ request('search') }}" autocomplete="off">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-1 small">Dari Tanggal</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" onchange="this.form.submit()">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-1 small">Sampai Tanggal</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" onchange="this.form.submit()">
            </div>
            <div class="col-6 col-md-2">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="active"   {{ request('status')=='active'  ?'selected':'' }}>Aktif Sekarang</option>
                    <option value="upcoming" {{ request('status')=='upcoming'?'selected':'' }}>Belum Mulai</option>
                    <option value="expired"  {{ request('status')=='expired' ?'selected':'' }}>Kedaluwarsa</option>
                    <option value="inactive" {{ request('status')=='inactive'?'selected':'' }}>Dinonaktifkan</option>
                </select>
            </div>
            <div class="col-6 col-md-2"><select name="per_page" class="form-select" onchange="this.form.submit()"><option value="20"{{ request('per_page', 20)==20?'selected':'' }}>20 Baris</option><option value="50" {{ request('per_page')==50?'selected':'' }}>50 Baris</option><option value="100" {{ request('per_page')==100?'selected':'' }}>100 Baris</option></select></div>
            <div class="col-6 col-md-1">
                <a href="{{ route('promotions.index') }}" class="btn btn-outline-secondary w-100" title="Reset"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div id="promos-results">
<!-- Kartu Daftar Promo & Diskon -->
<div class="card">
    <div class="card-header">
        <h6 class="mb-0">Daftar Promo & Diskon <span class="badge bg-success ms-1">{{ $promotions->total() }}</span></h6>
    </div>
    <div class="table-wrapper">
        <table class="table mb-0">
            <thead>
                <tr>
                    <x-sortable-column column="id" label="#" />
                    <x-sortable-column column="promo_name" label="Nama Promo" />
                    <x-sortable-column column="product_name" label="Produk" />
                    <x-sortable-column column="discount_amount" label="Potongan" />
                    <x-sortable-column column="start_date" label="Periode" />
                    <x-sortable-column column="is_active" label="Status" />
                    <th style="width:90px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($promotions as $i => $promo)
                @php [$bg,$col] = $promo->status_color; @endphp
                <tr>
                    <td style="color:#9ca3af;font-size:.76rem">{{ $promo->id }}</td>
                    <td style="font-weight:600;font-size:.87rem">{{ $promo->promo_name }}</td>
                    <td>
                        <div style="font-weight:600;font-size:.84rem">{{ $promo->product->product_name ?? '-' }}</div>
                        <div style="font-size:.72rem;color:#9ca3af">{{ $promo->product->product_code ?? '' }}</div>
                    </td>
                    <td><span style="font-weight:700;font-size:.95rem;color:#dc2626">-Rp {{ number_format($promo->discount_amount,0,',','.') }}</span></td>
                    <td style="font-size:.81rem">
                        <div><i class="bi bi-calendar-event me-1" style="color:#9ca3af"></i>{{ $promo->start_date->format('d/m/Y') }}</div>
                        <div><i class="bi bi-calendar-x me-1" style="color:#9ca3af"></i>{{ $promo->end_date->format('d/m/Y') }}</div>
                    </td>
                    <td><span style="background:{{ $bg }};color:{{ $col }};font-size:.72rem;font-weight:700;padding:3px 10px;border-radius:10px">{{ $promo->status_label }}</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('promotions.show',$promo) }}" class="btn btn-sm btn-icon btn-outline-secondary" title="Detail"><i class="bi bi-eye"></i></a>
                            @if(auth()->user()->role === 'pemilik')
                            <a href="{{ route('promotions.edit',$promo) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('promotions.destroy',$promo) }}" onsubmit="event.preventDefault(); Swal.fire({title: 'Hapus Promo?', text: 'Hapus promo ini?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal'}).then(r => { if(r.isConfirmed) this.submit(); })">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-icon btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-5" style="color:#9ca3af">
                    <i class="bi bi-tag" style="font-size:2.5rem;display:block;margin-bottom:10px"></i>
                    Belum ada promo
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($promotions->hasPages())
    <div class="card-body border-top py-3">{{ $promotions->withQueryString()->links() }}</div>
    @endif
</div>
</div><!-- Akhir Kontainer Hasil Promo -->
@endsection

@push('scripts')
<script>
// Fungsi inisialisasi pencarian AJAX untuk promo
(function(){
    const si=document.getElementById('promos-search');
    const f=document.getElementById('promos-filter-form');
    if(!si||!f) return;
    const base='{{ route('promotions.index') }}';
    function params(q){ const d=new FormData(f); d.set('search',q); return new URLSearchParams(d).toString(); }
    async function go(q){ const url=base+'?'+params(q); history.replaceState(null,'',url); try{ const r=await fetch(url,{headers:{'X-Requested-With':'XMLHttpRequest'}}); const html=await r.text(); const doc=new DOMParser().parseFromString(html,'text/html'); const p=doc.getElementById('promos-results'); if(p) document.getElementById('promos-results').innerHTML=p.innerHTML; }catch(e){ window.location.href=url; } }
    let t; si.addEventListener('input',function(){ clearTimeout(t); const q=this.value; t=setTimeout(()=>go(q),380); });
})();
</script>
@endpush
