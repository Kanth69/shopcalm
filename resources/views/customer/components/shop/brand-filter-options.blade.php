@if($brands->isNotEmpty())
    <div class="d-flex flex-column" style="gap: 0.35rem !important;">
        @foreach($brands as $brand)
            @php
                $initials = strtoupper(substr($brand->name, 0, 2));
            @endphp
            <div class="form-check py-0.5 mb-0 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2 overflow-hidden">
                    <input class="form-check-input filter-check m-0 flex-shrink-0" type="checkbox" name="brand[]" value="{{ $brand->id }}" id="brand_{{ $prefix }}_{{ $brand->id }}" {{ in_array($brand->id, (array)request('brand', [])) ? 'checked' : '' }}>
                    <label class="form-check-label small text-dark fw-medium text-truncate cursor-pointer d-flex align-items-center gap-1.5" for="brand_{{ $prefix }}_{{ $brand->id }}" style="font-size: 0.82rem;">
                        @if(!empty($brand->logo))
                            <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" class="rounded flex-shrink-0" style="width: 16px; height: 16px; object-fit: contain;">
                        @endif
                        <span class="text-truncate">{{ $brand->name }}</span>
                    </label>
                </div>
                @if(isset($brand->products_count) && $brand->products_count > 0)
                    <span class="text-muted small ms-1 flex-shrink-0" style="font-size: 0.72rem;">({{ $brand->products_count }})</span>
                @endif
            </div>
        @endforeach
    </div>
@else
    <div class="text-center py-2.5 text-muted small" style="font-size: 0.78rem;">
        <i class="bi bi-info-circle me-1 text-info"></i> No brands available
    </div>
@endif
