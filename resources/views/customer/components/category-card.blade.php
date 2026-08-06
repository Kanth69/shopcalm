<div class="card card-category">
    <a href="{{ route('category.products', $category->slug) }}">
        <div class="card-body text-center">
            <h5 class="card-title">{{ $category->name }}</h5>
            <p class="text-muted small">View Products</p>
        </div>
    </a>
</div>
