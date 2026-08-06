<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
        @if(isset($category))
            <li class="breadcrumb-item"><a href="{{ route('category.products', $category->slug) }}">{{ $category->name }}</a></li>
        @endif
        @if(isset($brand))
            <li class="breadcrumb-item"><a href="{{ route('brand.products', $brand->slug) }}">{{ $brand->name }}</a></li>
        @endif
        <li class="breadcrumb-item active" aria-current="page">{{ $currentPage }}</li>
    </ol>
</nav>
