<form action="{{ route('shop') }}" method="GET" class="search-header">
    <div class="input-group w-100">
        <input type="text" class="form-control" name="search" placeholder="Search products..." value="{{ request('search') }}">
        <button class="btn btn-primary" type="submit">
            <i class="bi bi-search"></i>
        </button>
    </div>
</form>
