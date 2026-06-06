@php
    $isSorted = $sortBy === $column;
    $nextDir = $isSorted && $sortDir === 'asc' ? 'desc' : 'asc';
    $url = request()->fullUrlWithQuery([
        'sort_by' => $column,
        'sort_dir' => $nextDir
    ]);
    $alignClass = match($align) {
        'center' => 'text-center',
        'right' => 'text-end',
        default => 'text-start'
    };
    $flexClass = match($align) {
        'center' => 'justify-content-center',
        'right' => 'justify-content-end',
        default => 'justify-content-start'
    };
@endphp

<th class="{{ $alignClass }}">
    <a href="{{ $url }}" class="d-flex align-items-center {{ $flexClass }} text-decoration-none text-dark" style="gap: 4px; font-weight: inherit; color: inherit; width: 100%;">
        {{ $label }}
        @if($isSorted)
            @if($sortDir === 'asc')
                <i class="bi bi-arrow-up-short" style="font-size: 1.1em; color: var(--primary);"></i>
            @else
                <i class="bi bi-arrow-down-short" style="font-size: 1.1em; color: var(--primary);"></i>
            @endif
        @else
            <i class="bi bi-arrow-down-up text-muted" style="font-size: 0.8em; opacity: 0.4;"></i>
        @endif
    </a>
</th>