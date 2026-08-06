@foreach($brands as $brand)
<div class="form-check mb-2">
    <input class="btn-check filter-check" type="checkbox" name="brand[]" value="{{ $brand->id }}" id="brand_{{ $prefix }}_{{ $brand->id }}" {{ in_array($brand->id, (array)request('brand', [])) ? 'checked' : '' }}>
    <label class="btn btn-outline-light text-dark text-start w-100 border rounded-3 py-2 px-3 ps-3 small fw-medium transition-all" for="brand_{{ $prefix }}_{{ $brand->id }}">
        {{ $brand->name }}
    </label>
</div>
@endforeach
