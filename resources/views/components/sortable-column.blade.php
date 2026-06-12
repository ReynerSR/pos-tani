<!-- Komponen Blade untuk membuat header tabel yang bisa di-sort (diurutkan) -->
@php
    // Mengecek apakah kolom ini sedang aktif diurutkan
    $isSorted = $sortBy === $column;
    // Menentukan arah urutan selanjutnya (jika sedang asc, maka klik selanjutnya jadi desc)
    $nextDir = $isSorted && $sortDir === 'asc' ? 'desc' : 'asc';
    
    // Membuat URL baru dengan parameter query untuk sorting
    $url = request()->fullUrlWithQuery([
        'sort_by' => $column,
        'sort_dir' => $nextDir
    ]);
    
    // Menentukan class CSS text-align berdasarkan parameter 'align'
    $alignClass = match($align) {
        'center' => 'text-center',
        'right' => 'text-end',
        default => 'text-start'
    };
    
    // Menentukan class flexbox untuk memposisikan konten di dalam header
    $flexClass = match($align) {
        'center' => 'justify-content-center',
        'right' => 'justify-content-end',
        default => 'justify-content-start'
    };
@endphp

<!-- Elemen header tabel (th) dengan class perataan -->
<th class="{{ $alignClass }}">
    <!-- Tautan interaktif yang ketika diklik akan memuat ulang halaman dengan parameter sorting baru -->
    <a href="{{ $url }}" class="d-flex align-items-center {{ $flexClass }} text-decoration-none text-dark" style="gap: 4px; font-weight: inherit; color: inherit; width: 100%;">
        <!-- Label atau nama kolom -->
        {{ $label }}
        
        <!-- Menampilkan ikon panah indikator sorting -->
        @if($isSorted)
            @if($sortDir === 'asc')
                <!-- Panah ke atas jika urutan ascending (A-Z / 0-9) -->
                <i class="bi bi-arrow-up-short" style="font-size: 1.1em; color: var(--primary);"></i>
            @else
                <!-- Panah ke bawah jika urutan descending (Z-A / 9-0) -->
                <i class="bi bi-arrow-down-short" style="font-size: 1.1em; color: var(--primary);"></i>
            @endif
        @else
            <!-- Ikon panah default saat kolom belum disorting -->
            <i class="bi bi-arrow-down-up text-muted" style="font-size: 0.8em; opacity: 0.4;"></i>
        @endif
    </a>
</th>