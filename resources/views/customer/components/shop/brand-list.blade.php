<div class="filter-group">
    @foreach($brands as $brand)
    <div class="form-check mb-2">
        <input class="form-check-input filter-check" type="checkbox" name="brand[]" value="{{ $brand->id }}" id="brand_{{ $brand->id }}" {{ in_array($brand->id, request('brand', [])) ? 'checked' : '' }}>
        <label class="form-check-label small fw-medium" for="brand_{{ $brand->id }}">
            {{ $brand->name }}
        </label>
    </div>
    @endforeach

    @if($brands->isEmpty())
    <p class="text-muted small">No brands available for selected category.</p>
    @endif
</div>
