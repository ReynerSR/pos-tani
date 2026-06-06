<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SortableColumn extends Component
{
    public string $column;
    public string $label;
    public string $align;
    public string $sortBy;
    public string $sortDir;

    public function __construct(string $column, string $label, string $align = 'left')
    {
        $this->column = $column;
        $this->label = $label;
        $this->align = $align;
        $this->sortBy = request('sort_by', 'created_at'); // Fallback can be overridden by specific controller
        $this->sortDir = request('sort_dir', 'desc');
    }

    public function render(): View|Closure|string
    {
        return view('components.sortable-column');
    }
}
