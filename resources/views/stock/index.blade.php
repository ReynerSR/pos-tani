@extends('layouts.app')
@section('title', 'Stock Opname')
@section('page_title', 'Stock Opname')

@section('content')
<!-- Header Halaman -->
<div class="page-hdr">
    <div class="page-hdr-left">
        <h1><i class="bi bi-clipboard2-check me-2" style="color:var(--primary)"></i>Stock Opname</h1>
    </div>
    <a href="{{ route('stock.create') }}" class="btn btn-primary"><i class="bi bi-clipboard-check me-2"></i>Input Stock Opname</a>
</div>

<!-- Kartu Filter dan Pencarian -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" id="stock-filter-form" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <div class="search-bar">
                    <i class="bi bi-search si-search"></i>
                    <input type="text" name="search" id="stock-search" class="form-control" placeholder="Cari produk/kode..." value="{{ request('search') }}" autocomplete="off">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <select name="warehouse_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Lokasi</option>
                    @foreach($warehouses as $wh)
                    <option value="{{ $wh->id }}" {{ request('warehouse_id')==$wh->id?'selected':'' }}>{{ $wh->code }}{{ $wh->is_store?' (Utama)':'' }}</option>
                    @endforeach
                </select>
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

                <select name="per_page" class="form-select" onchange="this.form.submit()">
                    @foreach([20,50,100] as $n)
                    <option value="{{ $n }}" {{ request('per_page',20)==$n?'selected':'' }}>{{ $n }} baris</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-1 d-flex gap-1">
                <a href="{{ route('stock.index') }}" class="btn btn-outline-secondary w-100" title="Reset"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Kontainer Hasil Riwayat Stock Opname -->
<div id="stock-results">
    <!-- Kartu Tabel Stock Opname -->
    <div class="card">
        <div class="table-wrapper">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Lokasi</th>
                        <th class="text-center">Total Item</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adjustments as $adj)
                    <tr>
                        <td><strong>{{ \Carbon\Carbon::parse($adj->adjustment_date)->format('d/m/Y') }}</strong></td>
                        <td><span class="badge bg-light text-dark border">{{ $adj->warehouse ? $adj->warehouse->code . ($adj->warehouse->is_store ? ' (Utama)' : '') : '-' }}</span></td>
                        <td class="text-center">{{ $adj->total_items }} Produk</td>
                        <td class="text-center">
                            @if($adj->pending_items > 0)
                            <span class="badge bg-warning text-dark">Draft ({{ $adj->pending_items }})</span>
                            @else
                            <span class="badge bg-success">Approved</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <a href="{{ route('stock.show', ['date' => $adj->adjustment_date->format('Y-m-d'), 'warehouse_id' => $adj->warehouse_id]) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i> Detail
                                </a>
                                @if($adj->pending_items > 0 && (auth()->user()->role === 'pemilik' || auth()->user()->role === 'admin'))
                                <form action="{{ route('stock.destroy', ['date' => $adj->adjustment_date->format('Y-m-d'), 'warehouse_id' => $adj->warehouse_id]) }}" method="POST" class="d-inline" onsubmit="event.preventDefault(); Swal.fire({title: 'Hapus Draft?', text: 'Apakah Anda yakin ingin menghapus draft stock opname ini?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal'}).then(r => { if(r.isConfirmed) this.submit(); })">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Draft">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @else
                                <button type="button" class="btn btn-sm btn-outline-danger invisible" disabled>
                                    <i class="bi bi-trash"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
                            <i class="bi bi-clipboard-check" style="font-size:2rem"></i>
                            <div class="mt-2">Belum ada data stock opname</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($adjustments->hasPages())
        <div class="card-body border-top">
            {{ $adjustments->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div><!-- Akhir Kontainer Hasil -->
@endsection

@push('scripts')
<script>
    // Fungsi inisialisasi pencarian AJAX untuk daftar stock opname
    (function() {
        const si = document.getElementById('stock-search');
        const f = document.getElementById('stock-filter-form');
        if (!si || !f) return;
        const base = "{{ route('stock.index') }}";

        function params(q) {
            const d = new FormData(f);
            d.set('search', q);
            return new URLSearchParams(d).toString();
        }
        async function go(q) {
            const url = base + '?' + params(q);
            history.replaceState(null, '', url);
            try {
                const r = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const html = await r.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const p = doc.getElementById('stock-results');
                if (p) document.getElementById('stock-results').innerHTML = p.innerHTML;
            } catch (e) {
                window.location.href = url;
            }
        }
        let t;
        si.addEventListener('input', function() {
            clearTimeout(t);
            const q = this.value;
            t = setTimeout(() => go(q), 380);
        });
    })();
</script>
@endpush