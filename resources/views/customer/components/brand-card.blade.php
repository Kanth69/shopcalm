<div class="card card-brand">
    <a href="{{ route('brand.products', $brand->slug) }}">
        <div class="card-body text-center">
            @if($brand->logo)
                <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" class="brand-logo">
            @else
                <h5 class="card-title">{{ $brand->name }}</h5>
            @endif
        </div>
    </a>
</div>
