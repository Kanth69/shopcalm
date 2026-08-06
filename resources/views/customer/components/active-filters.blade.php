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

<div class="d-flex flex-wrap align-items-center gap-2">
    <strong class="text-dark small text-uppercase fw-bold me-1">Active Filters:</strong>

    @if(!empty($activeFilters))
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
                    <a href="{{ route('shop', $newQuery) }}" class="btn btn-sm btn-white border border-primary-subtle text-primary shadow-sm rounded-pill chip-link py-1 px-3">
                        <span class="small fw-bold">{{ $label }}</span> <i class="bi bi-x ms-1"></i>
                    </a>
                @endforeach
            @else
                @php
                    $label = ucwords(str_replace('_', ' ', $key)) . ': ' . $value;
                    $newQuery = request()->query();
                    unset($newQuery[$key]);
                @endphp
                 <a href="{{ route('shop', $newQuery) }}" class="btn btn-sm btn-white border border-primary-subtle text-primary shadow-sm rounded-pill chip-link py-1 px-3">
                    <span class="small fw-bold">{{ $label }}</span> <i class="bi bi-x ms-1"></i>
                 </a>
            @endif
        @endforeach

        <div class="ms-2 ps-2 border-start">
            <a href="{{ route('shop', request()->only(['search', 'sort'])) }}" class="btn btn-danger btn-sm shadow-sm rounded-pill chip-link py-1 px-3 fw-bold" id="clear-all-chips-btn">
                Clear All
            </a>
        </div>
    @else
        <span class="text-muted small italic">None applied</span>
    @endif
</div>
