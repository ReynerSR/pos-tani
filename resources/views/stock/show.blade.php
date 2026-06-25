@extends('layouts.app')
@section('title', 'Detail & Persetujuan Stock Opname')
@section('page_title', 'Detail & Persetujuan Stock Opname')

@section('content')
<!-- Header Halaman -->
<div class="page-hdr">
    <div class="page-hdr-left">
        <h1><i class="bi bi-file-earmark-text me-2" style="color:var(--primary)"></i>Persetujuan Stock Opname</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('stock.index') }}">Stock Opname</a></li><li class="breadcrumb-item active">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }} - {{ $warehouse->name }}</li></ol></nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('stock.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Kembali</a>
        @if($hasDraft)
            @if(auth()->user()->role === 'pemilik' || auth()->user()->role === 'admin')
                <button type="button" class="btn btn-danger" onclick="Swal.fire({title: 'Hapus Draft?', text: 'Apakah Anda yakin ingin menghapus semua draft stock opname pada tanggal ini?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal'}).then(r => { if(r.isConfirmed) document.getElementById('deleteDraftForm').submit(); })">
                    <i class="bi bi-trash me-2"></i>Hapus Draft
                </button>
            @endif
            @if(auth()->user()->role === 'pemilik')
                <button type="button" class="btn btn-success" onclick="document.getElementById('approveForm').submit()">
                    <i class="bi bi-check-circle me-2"></i>Simpan & Setujui Terpilih
                </button>
            @else
                <button type="button" class="btn btn-primary" onclick="document.getElementById('approveForm').submit()">
                    <i class="bi bi-save me-2"></i>Perbarui Draft Terpilih
                </button>
            @endif
        @endif
    </div>
</div>

<!-- Form Persetujuan Stock Opname -->
<form method="POST" action="{{ route('stock.approve', ['date' => $date, 'warehouse_id' => $warehouse->id]) }}" id="approveForm">
    @csrf
    <!-- Kartu Detail dan Persetujuan Stock Opname -->
    <div class="card">
        <!-- Tabel Detail Produk Stock Opname -->
        <div class="table-wrapper">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        @if($hasDraft)
                        <th width="40"><input type="checkbox" class="form-check-input" id="checkAll" checked></th>
                        @else
                        <th>#</th>
                        @endif
                        <th>Produk</th>
                        <th>Petugas Input</th>
                        <th class="text-center">Sebelum</th>
                        <th class="text-center" width="150">Fisik (Hitungan)</th>
                        <th class="text-center">Selisih</th>
                        <th class="text-center">Status</th>
                        <th width="250">Keterangan / Alasan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($adjustments as $adj)
                    <tr>
                        @if($hasDraft)
                            @if($adj->status === 'draft')
                                <td><input type="checkbox" class="form-check-input item-check" name="items[{{ $adj->id }}][approve]" value="1" checked></td>
                            @else
                                <td><i class="bi bi-check2 text-success"></i></td>
                            @endif
                        @else
                            <td class="text-muted small">{{ $loop->iteration }}</td>
                        @endif
                        
                        <td>
                            <strong>{{ $adj->product->product_name }}</strong>
                            <div class="small text-muted">{{ $adj->product->product_code }}</div>
                        </td>
                        <td>{{ $adj->user->name }}</td>
                        <td class="text-center" id="before_{{ $adj->id }}">{{ $adj->stock_before }}</td>
                        
                        @if($adj->status === 'draft')
                            <td>
                                <input type="number" class="form-control form-control-sm text-center" name="items[{{ $adj->id }}][stock_after]" value="{{ $adj->stock_after }}" oninput="updateDiff({{ $adj->id }}, {{ $adj->stock_before }})">
                            </td>
                        @else
                            <td class="text-center fw-bold text-primary">{{ $adj->stock_after }}</td>
                        @endif
                        
                        <td class="text-center fw-bold diff-cell" id="diff_{{ $adj->id }}">
                            @if($adj->difference>0)
                                <span class="text-success">+{{ $adj->difference }}</span>
                            @elseif($adj->difference<0)
                                <span class="text-danger">{{ $adj->difference }}</span>
                            @else
                                <span class="text-muted">0</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($adj->status === 'draft')
                                <span class="badge bg-warning text-dark">Draft</span>
                            @else
                                <span class="badge bg-success">Approved</span>
                                <div class="small text-muted mt-1" style="font-size: 0.7em;">oleh {{ $adj->approver->name ?? '-' }}</div>
                            @endif
                        </td>
                        
                        @if($adj->status === 'draft')
                            <td>
                                <input type="text" class="form-control form-control-sm" name="items[{{ $adj->id }}][notes]" value="{{ $adj->notes }}" placeholder="Wajib jika selisih != 0">
                            </td>
                        @else
                            <td class="text-muted">{{ $adj->notes ?? '-' }}</td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</form>
@endsection

@if($hasDraft && (auth()->user()->role === 'pemilik' || auth()->user()->role === 'admin'))
<form method="POST" action="{{ route('stock.destroy', ['date' => $date, 'warehouse_id' => $warehouse->id]) }}" id="deleteDraftForm" class="d-none">
    @csrf
    @method('DELETE')
</form>
@endif

@push('scripts')
<script>
    const checkAll = document.getElementById('checkAll');
    // Event listener untuk checkbox "Pilih Semua"
    if (checkAll) {
        checkAll.addEventListener('change', function() {
            document.querySelectorAll('.item-check').forEach(cb => cb.checked = this.checked);
        });
    }

    // Fungsi untuk memperbarui tampilan selisih secara dinamis saat input fisik diubah
    function updateDiff(id, before) {
        const input = document.querySelector(`input[name="items[${id}][stock_after]"]`);
        if (!input) return;
        
        const after = Number(input.value || 0);
        const diff = after - before;
        const cell = document.getElementById(`diff_${id}`);
        
        if (diff > 0) {
            cell.innerHTML = `<span class="text-success">+${diff}</span>`;
        } else if (diff < 0) {
            cell.innerHTML = `<span class="text-danger">${diff}</span>`;
        } else {
            cell.innerHTML = `<span class="text-muted">0</span>`;
        }
    }
</script>
@endpush
