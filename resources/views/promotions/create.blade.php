@extends('layouts.app')
@section('title','Tambah Promo')
@section('page_title','Tambah Promo')
@push('styles')
<style>
    .autocomplete-wrap { position:relative; }
    .autocomplete-menu { position:absolute; top:100%; left:0; right:0; z-index:1050; background:#fff; border:1px solid #d1d5db; border-radius:0 0 10px 10px; box-shadow:0 12px 24px rgba(15,23,42,.12); max-height:260px; overflow:auto; display:none; }
    .autocomplete-item { padding:10px 12px; cursor:pointer; border-bottom:1px solid #f3f4f6; }
    .autocomplete-item:hover { background:#ecfdf5; }
    .autocomplete-title { font-weight:700; color:#0f172a; }
    .autocomplete-meta { font-size:.78rem; color:#64748b; margin-top:2px; }
    .autocomplete-empty { padding:10px 12px; color:#64748b; }
</style>
@endpush

@section('content')
<div class="page-hdr">
    <div class="page-hdr-left">
        <h1><i class="bi bi-tag me-2" style="color:var(--primary)"></i>Tambah Promo</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('promotions.index') }}">Promo Produk</a></li>
            <li class="breadcrumb-item active">Tambah</li>
        </ol></nav>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-header"><h6>Form Promo Baru</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('promotions.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Nama Promo <span class="text-danger">*</span></label>
                    <input type="text" name="promo_name"
                           class="form-control @error('promo_name') is-invalid @enderror"
                           value="{{ old('promo_name') }}"
                           placeholder="Contoh: Promo Jagung Mei 2026" required>
                    @error('promo_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Produk <span class="text-danger">*</span></label>
                    <div class="autocomplete-wrap">
                        <input type="text" id="productSearch" class="form-control @error('product_id') is-invalid @enderror" placeholder="Ketik/click nama atau kode produk..." autocomplete="off" onfocus="renderProductDropdown()" oninput="clearSelectedProduct(); renderProductDropdown()">
                        <input type="hidden" name="product_id" id="product_id" value="{{ old('product_id') }}">
                        <div class="autocomplete-menu" id="productDropdown"></div>
                    </div>
                    <div class="form-text">Klik field produk untuk melihat dropdown, atau ketik untuk memfilter produk.</div>
                    @error('product_id')<div class="text-danger" style="font-size:.78rem">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Potongan Harga (Nominal Rp) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="discount_amount"
                               class="form-control @error('discount_amount') is-invalid @enderror"
                               value="{{ old('discount_amount') }}"
                               min="1" step="any" placeholder="Contoh: 5000" required>
                    </div>
                    <div class="form-text">Potongan nominal langsung dari harga jual. Diskon member tidak berlaku jika ada promo.</div>
                    @error('discount_amount')<div class="text-danger" style="font-size:.78rem">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label d-block">Promo berlaku untuk member</label>
                    @php $oldTiers = old('eligible_tiers', isset($promotion) ? ($promotion->eligible_tiers ?: []) : []); @endphp
                    @foreach(['bronze'=>'Bronze','silver'=>'Silver','gold'=>'Gold'] as $tierVal => $tierLabel)
                    <label class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="eligible_tiers[]" value="{{ $tierVal }}" {{ in_array($tierVal, $oldTiers) ? 'checked' : '' }}>
                        <span class="form-check-label">{{ $tierLabel }}</span>
                    </label>
                    @endforeach
                    <div class="form-text">Kosongkan semua jika promo berlaku untuk semua pelanggan/member.</div>
                </div>

                <div class="p-3 mb-3" style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px">
                    <label class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="can_redeem_with_points" value="1" {{ old('can_redeem_with_points', isset($promotion) ? $promotion->can_redeem_with_points : false) ? 'checked' : '' }}>
                        <span class="form-check-label fw-bold">Promo ini bisa diredeem memakai poin</span>
                    </label>
                    <div class="row g-2">
                        <div class="col-md-6"><label class="form-label">Poin dibutuhkan</label><input type="number" name="redeem_points_required" class="form-control" min="0" step="1" value="{{ old('redeem_points_required', isset($promotion) ? $promotion->redeem_points_required : 0) }}"></div>
                        <div class="col-md-6"><label class="form-label">Tambahan potongan redeem</label><div class="input-group"><span class="input-group-text">Rp</span><input type="number" name="redeem_discount_amount" class="form-control" min="0" step="any" value="{{ old('redeem_discount_amount', isset($promotion) ? $promotion->redeem_discount_amount : 0) }}"></div></div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                        <input type="date" name="start_date"
                               class="form-control @error('start_date') is-invalid @enderror"
                               value="{{ old('start_date', now()->toDateString()) }}" required>
                        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Berakhir <span class="text-danger">*</span></label>
                        <input type="date" name="end_date"
                               class="form-control @error('end_date') is-invalid @enderror"
                               value="{{ old('end_date') }}" required>
                        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Opsional — keterangan tambahan promo">{{ old('notes') }}</textarea>
                </div>

                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                           {{ old('is_active',1) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Promo Aktif</label>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-2"></i>Simpan Promo</button>
                    <a href="{{ route('promotions.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@php
$promoProductOptions = $products->map(function ($p) {
    return [
        'id' => $p->id,
        'name' => $p->product_name,
        'code' => $p->product_code,
        'price' => (float) $p->selling_price,
    ];
})->values();
@endphp
<script>
const promoProducts = @json($promoProductOptions);
const selectedProduct = @json(old('product_id'));
function formatRupiah(value){ return 'Rp ' + Math.round(Number(value||0)).toLocaleString('id-ID'); }
function matchesProduct(keyword){
    const q = String(keyword || '').toLowerCase().trim();
    return promoProducts.filter(p => !q || p.name.toLowerCase().includes(q) || p.code.toLowerCase().includes(q)).slice(0, 30);
}
function renderProductDropdown(){
    const input = document.getElementById('productSearch');
    const menu = document.getElementById('productDropdown');
    const matches = matchesProduct(input.value);
    if(matches.length === 0){
        menu.innerHTML = '<div class="autocomplete-empty">Produk tidak ditemukan</div>';
    }else{
        menu.innerHTML = matches.map(p => `<div class="autocomplete-item" onclick="selectPromoProduct(${p.id})">
            <div class="autocomplete-title">${p.name}</div>
            <div class="autocomplete-meta">${p.code} • Harga ${formatRupiah(p.price)}</div>
        </div>`).join('');
    }
    menu.style.display = 'block';
}
function clearSelectedProduct(){
    document.getElementById('product_id').value = '';
}
function selectPromoProduct(productId){
    const product = promoProducts.find(p => String(p.id) === String(productId));
    if(!product) return;
    document.getElementById('product_id').value = product.id;
    document.getElementById('productSearch').value = `${product.name} (${product.code})`;
    document.getElementById('productDropdown').style.display = 'none';
}
document.addEventListener('click', function(e){ if(!e.target.closest('.autocomplete-wrap')) document.getElementById('productDropdown').style.display='none'; });
if(selectedProduct){ selectPromoProduct(selectedProduct); }
</script>
@endpush