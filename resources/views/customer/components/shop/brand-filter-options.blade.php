@if($brands->isNotEmpty())
    <div class="d-flex flex-column" style="gap: 0.45rem !important;">
        @foreach($brands as $brand)
            @php
                $initials = strtoupper(substr($brand->name, 0, 2));
            @endphp
            <div class="form-check p-0 m-0">
                <input class="btn-check filter-check" type="checkbox" name="brand[]" value="{{ $brand->id }}" id="brand_{{ $prefix }}_{{ $brand->id }}" {{ in_array($brand->id, (array)request('brand', [])) ? 'checked' : '' }}>
                <label class="btn btn-outline-light text-dark text-start w-100 border rounded-pill py-2 px-3 small fw-medium transition-all brand-capsule-pill d-flex align-items-center justify-content-between" 
                       for="brand_{{ $prefix }}_{{ $brand->id }}" 
                       style="border-color: #e2e8f0 !important; background: #ffffff; transition: all 0.2s ease;">
                    <div class="d-flex align-items-center" style="gap: 0.75rem !important;">
                        @if(!empty($brand->logo))
                            <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" class="rounded-circle border flex-shrink-0" style="width: 26px; height: 26px; object-fit: contain; background: #fff;">
                        @else
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 brand-avatar-sphere" 
                                 style="width: 26px; height: 26px; background: #cffafe; color: #0891b2; font-size: 0.68rem; font-weight: 700;">
                                {{ $initials }}
                            </div>
                        @endif
                        <span class="fw-semibold text-dark brand-name-text" style="font-size: 0.84rem;">{{ $brand->name }}</span>
                    </div>
                    <div class="brand-check-icon d-none align-items-center justify-content-center rounded-circle shadow-xs" 
                         style="width: 20px; height: 20px; background: #ffffff; color: #0891b2; font-size: 0.72rem;">
                        <i class="bi bi-check-lg fw-bold"></i>
                    </div>
                </label>
            </div>
        @endforeach
    </div>
@else
    <div class="text-center py-3 text-muted small">
        <i class="bi bi-info-circle me-1 text-info"></i> No brands available for selected category.
    </div>
@endif
