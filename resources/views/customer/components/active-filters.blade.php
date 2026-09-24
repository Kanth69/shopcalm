@php
    $filters = request()->query();
    $nonFilterKeys = ['sort', 'page', 'search', 'q', 'ajax', 'suggestion'];

    // Create a filtered list of parameters that are actual "filters"
    $activeFilters = [];
    foreach($filters as $key => $value) {
        $cleanKey = str_replace('[]', '', $key);
        if(!in_array($cleanKey, $nonFilterKeys) && !empty($value)) {
            if(is_array($value)) {
                foreach($value as $val) {
                    if(!empty($val)) $activeFilters[$key][] = $val;
                }
            } else {
                $activeFilters[$key] = $value;
            }
        }
    }
@endphp

@if(!empty($activeFilters))
<div class="d-flex flex-wrap align-items-center gap-2 p-2.5 px-3.5 bg-light rounded-4 border mb-2" style="border-color: #e2e8f0 !important;">
    <div class="d-flex align-items-center gap-1.5 text-muted small text-uppercase fw-bold me-1" style="font-size: 0.74rem; letter-spacing: 0.05em;">
        <i class="bi bi-funnel-fill text-primary"></i> Active Filters:
    </div>

    @foreach($activeFilters as $key => $value)
        @if(is_array($value))
            @foreach($value as $item)
                @php
                    $label = '';
                    $cleanKey = str_replace('[]', '', $key);
                    if($cleanKey == 'category') $label = \App\Models\Category::find($item)->name ?? '';
                    if($cleanKey == 'brand') $label = \App\Models\Brand::find($item)->name ?? '';
                    if(empty($label)) $label = ucwords($cleanKey) . ': ' . $item;

                    // Build removal URL
                    $newQuery = request()->query();
                    if(isset($newQuery[$key]) && is_array($newQuery[$key])) {
                        $idx = array_search($item, $newQuery[$key]);
                        if($idx !== false) unset($newQuery[$key][$idx]);
                    }
                @endphp
                <a href="{{ route('shop', $newQuery) }}" class="btn btn-sm btn-white border border-primary-subtle text-primary shadow-xs rounded-pill chip-link py-1 px-3 fw-semibold d-inline-flex align-items-center gap-1" style="background: #ffffff; font-size: 0.8rem;">
                    <span>{{ $label }}</span> <i class="bi bi-x-circle-fill text-muted opacity-75 ms-0.5"></i>
                </a>
            @endforeach
        @else
            @php
                $label = ucwords(str_replace('_', ' ', $key)) . ': ' . $value;
                if($key == 'only_discounted') $label = 'On Sale Deals';
                if($key == 'featured') $label = 'Featured Only';
                $newQuery = request()->query();
                unset($newQuery[$key]);
            @endphp
             <a href="{{ route('shop', $newQuery) }}" class="btn btn-sm btn-white border border-primary-subtle text-primary shadow-xs rounded-pill chip-link py-1 px-3 fw-semibold d-inline-flex align-items-center gap-1" style="background: #ffffff; font-size: 0.8rem;">
                <span>{{ $label }}</span> <i class="bi bi-x-circle-fill text-muted opacity-75 ms-0.5"></i>
             </a>
        @endif
    @endforeach

    <div class="ms-auto ps-2">
        <a href="{{ route('shop', request()->only(['q', 'search', 'sort'])) }}" class="btn btn-outline-danger btn-sm rounded-pill chip-link py-1 px-3 fw-bold d-inline-flex align-items-center gap-1" style="font-size: 0.78rem;" id="clear-all-chips-btn">
            <i class="bi bi-trash3 me-1"></i> Clear All
        </a>
    </div>
</div>
@endif
