@props(['title', 'value', 'icon', 'color'])
<div class="card border-0 shadow-sm h-100">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h6 class="text-muted small text-uppercase mb-1">{{ $title }}</h6>
                <h4 class="mb-0 fw-bold text-{{ $color }}">{{ $value }}</h4>
            </div>
            <div class="bg-{{ $color }} bg-opacity-10 text-{{ $color }} rounded-circle p-3">
                <i class="bi {{ $icon }} fs-4"></i>
            </div>
        </div>
    </div>
</div>
