@if(isset($testimonials) && $testimonials->isNotEmpty())
<section class="testimonials-section py-4 py-md-5 my-2 my-md-4" style="background: #f8fafc;">
    <div class="container px-3 px-md-4">
        {{-- Section Heading --}}
        <div class="text-center mb-4">
            <div class="mb-2">
                <span class="badge rounded-pill px-3 py-1.5 fw-bold text-uppercase d-inline-flex align-items-center gap-1 shadow-xs" 
                      style="background: rgba(99,102,241,0.12); color: #6366f1; font-size: 0.75rem; letter-spacing: 0.05em;">
                    <i class="bi bi-star-fill text-warning me-1"></i> Verified Reviews
                </span>
            </div>
            <h3 class="fw-bolder mb-1 text-dark" style="letter-spacing: -0.02em; font-size: clamp(1.25rem, 2.5vw, 1.75rem);">What Our Customers Say</h3>
            <p class="text-muted small mb-0" style="font-size: 0.86rem; max-width: 500px; margin: 0 auto;">Real verified shopping experiences from our customers</p>
        </div>

        {{-- 📱 MOBILE VIEW: Smooth Horizontal Side-by-Side Swipe (< 768px) --}}
        <div class="d-flex d-md-none overflow-x-auto gap-3 pb-3 pt-1 px-1 no-scrollbar" style="scroll-snap-type: x mandatory;">
            @foreach($testimonials as $index => $review)
            @php
                $initials = collect(explode(' ', $review->user->name ?? 'Customer'))
                    ->map(fn($w) => strtoupper(substr($w, 0, 1)))
                    ->take(2)
                    ->join('');
                $gradients = [
                    'linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%)',
                    'linear-gradient(135deg, #10b981 0%, #059669 100%)',
                    'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)',
                    'linear-gradient(135deg, #ec4899 0%, #be185d 100%)',
                    'linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%)',
                ];
                $bgGrad = $gradients[$index % count($gradients)];
            @endphp
            <div class="flex-shrink-0" style="width: 290px; scroll-snap-align: start;">
                <div class="card h-100 border-0 rounded-4 p-4 shadow-xs d-flex flex-column justify-content-between bg-white" 
                     style="border: 1px solid #e2e8f0 !important;">
                    <div>
                        <div class="text-warning mb-2.5 d-flex gap-1" style="font-size: 0.82rem;">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi bi-star-{{ $i <= $review->rating ? 'fill' : 'half' }}"></i>
                            @endfor
                        </div>
                        @if($review->title)
                            <h6 class="fw-bold text-dark mb-1" style="font-size: 0.88rem;">{{ $review->title }}</h6>
                        @endif
                        <p class="text-secondary mb-3" style="line-height: 1.55; font-size: 0.84rem; color: #475569;">
                            "{{ Str::limit($review->review, 140) }}"
                        </p>
                    </div>
                    <div class="pt-3 border-top" style="border-color: #f1f5f9 !important;">
                        <div class="d-flex align-items-center gap-2.5">
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-xs flex-shrink-0" 
                                 style="width: 36px; height: 36px; background: {{ $bgGrad }}; font-size: 0.82rem;">
                                {{ $initials ?: 'WK' }}
                            </div>
                            <div class="overflow-hidden">
                                <div class="fw-bold text-dark text-truncate" style="font-size: 0.85rem;">{{ $review->user->name ?? 'Verified Buyer' }}</div>
                                <div class="text-success fw-semibold" style="font-size: 0.7rem;"><i class="bi bi-patch-check-fill me-0.5"></i>Verified Buyer</div>
                            </div>
                        </div>
                        @if($review->product)
                            <div class="mt-2 text-truncate small text-muted" style="font-size: 0.72rem;">
                                <i class="bi bi-bag-check me-1"></i>{{ $review->product->name }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- 💻 DESKTOP & TABLET VIEW: Grid (>= 768px) --}}
        <div class="row g-4 d-none d-md-flex">
            @foreach($testimonials->take(6) as $index => $review)
            @php
                $initials = collect(explode(' ', $review->user->name ?? 'Customer'))
                    ->map(fn($w) => strtoupper(substr($w, 0, 1)))
                    ->take(2)
                    ->join('');
                $gradients = [
                    'linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%)',
                    'linear-gradient(135deg, #10b981 0%, #059669 100%)',
                    'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)',
                    'linear-gradient(135deg, #ec4899 0%, #be185d 100%)',
                    'linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%)',
                ];
                $bgGrad = $gradients[$index % count($gradients)];
            @endphp
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 testimonial-card bg-white d-flex flex-column justify-content-between" 
                     style="border: 1px solid #e2e8f0 !important; transition: all 0.2s ease;">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2.5">
                            <div class="text-warning d-flex gap-1" style="font-size: 0.9rem;">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star-{{ $i <= $review->rating ? 'fill' : 'half' }}"></i>
                                @endfor
                            </div>
                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">
                                <i class="bi bi-patch-check-fill me-0.5"></i> Verified Purchase
                            </span>
                        </div>
                        @if($review->title)
                            <h6 class="fw-bold text-dark mb-1" style="font-size: 0.92rem;">{{ $review->title }}</h6>
                        @endif
                        <p class="text-secondary mb-3 small" style="line-height: 1.65; font-size: 0.88rem; color: #475569;">
                            "{{ $review->review }}"
                        </p>
                    </div>
                    <div class="pt-3 border-top mt-auto" style="border-color: #f1f5f9 !important;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-xs flex-shrink-0" 
                                 style="width: 42px; height: 42px; background: {{ $bgGrad }}; font-size: 0.9rem;">
                                {{ $initials ?: 'WK' }}
                            </div>
                            <div class="overflow-hidden flex-grow-1">
                                <h6 class="fw-bold mb-0 text-dark text-truncate" style="font-size: 0.9rem;">{{ $review->user->name ?? 'Verified Buyer' }}</h6>
                                <small class="text-muted" style="font-size: 0.72rem;">{{ $review->created_at ? $review->created_at->diffForHumans() : 'Recently' }}</small>
                            </div>
                        </div>
                        @if($review->product)
                            <div class="mt-2 text-truncate small text-muted bg-light p-1.5 rounded-2" style="font-size: 0.72rem;">
                                <a href="{{ route('product.show', $review->product->slug) }}" class="text-decoration-none text-dark fw-semibold">
                                    <i class="bi bi-bag-check text-primary me-1"></i>{{ $review->product->name }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<style>
.testimonial-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08) !important;
    border-color: #cbd5e1 !important;
}
</style>
@endif
