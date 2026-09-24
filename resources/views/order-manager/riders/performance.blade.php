@extends('order-manager.layouts.app')

@section('title', 'Rider Performance & Ratings')
@section('header', 'Rider Ratings')

@push('styles')
<style>
    .rider-card {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #ffffff;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
    }
    .rider-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
        transform: translateY(-2px);
    }
    .rider-header-btn {
        cursor: pointer;
        padding: 1.1rem 1.4rem;
        user-select: none;
    }
    .rider-avatar {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%);
        color: #ffffff;
        font-weight: 800;
        font-size: 1.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(2, 132, 199, 0.28);
        position: relative;
    }
    .status-dot {
        position: absolute;
        bottom: -2px;
        right: -2px;
        width: 13px;
        height: 13px;
        background: #10b981;
        border: 2px solid #ffffff;
        border-radius: 50%;
    }
    .chevron-rotate {
        transition: transform 0.25s ease;
    }
    .collapsed .chevron-rotate {
        transform: rotate(0deg);
    }
    :not(.collapsed) .chevron-rotate {
        transform: rotate(180deg);
    }
    .review-bubble {
        background: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        transition: all 0.2s ease;
    }
    .review-bubble:hover {
        background: #fafafa;
        border-color: #e2e8f0;
    }
    .tag-chip {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #334155;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.25rem 0.65rem;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
</style>
@endpush

@section('content')

<!-- Header Command Bar -->
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge rounded-pill px-2.5 py-1 fw-bold text-primary bg-primary bg-opacity-10" style="font-size: 0.7rem;">
                <i class="bi bi-bicycle me-1"></i> FLEET QUALITY
            </span>
            <span class="text-muted small">&bull; Hub #01</span>
        </div>
        <h4 class="fw-bolder text-dark mb-0" style="letter-spacing: -0.4px;">
            Rider Ratings & Feedback
        </h4>
    </div>

    <!-- Quick Stats -->
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <div class="d-flex align-items-center gap-2 bg-white px-3 py-1.5 rounded-pill border shadow-xs">
            <span class="text-secondary small fw-semibold">Riders:</span>
            <span class="fw-bold text-dark font-monospace">{{ $totalFleetRiders }}</span>
        </div>
        <div class="d-flex align-items-center gap-2 bg-white px-3 py-1.5 rounded-pill border shadow-xs">
            <span class="text-secondary small fw-semibold">Feedbacks:</span>
            <span class="fw-bold text-primary font-monospace">{{ $totalFleetFeedbacks }}</span>
        </div>
        <div class="d-flex align-items-center gap-2 bg-white px-3 py-1.5 rounded-pill border shadow-xs">
            <span class="text-secondary small fw-semibold">Avg Score:</span>
            <span class="fw-bold text-warning font-monospace"><i class="bi bi-star-fill me-1"></i>{{ number_format($overallAvgRating, 1) }}</span>
        </div>
    </div>
</div>

<!-- Interactive Rider Accordion Cards -->
<div class="accordion d-flex flex-column gap-3" id="ridersAccordion">
    @forelse($riders as $index => $r)
    <div class="rider-card overflow-hidden">
        
        {{-- Clickable Header Strip --}}
        <div class="rider-header-btn d-flex align-items-center justify-content-between flex-wrap gap-3 {{ $loop->first && $r['feedbacks']->isNotEmpty() ? '' : 'collapsed' }}"
             data-bs-toggle="collapse" 
             data-bs-target="#collapseRider{{ $r['id'] }}" 
             aria-expanded="{{ $loop->first && $r['feedbacks']->isNotEmpty() ? 'true' : 'false' }}" 
             aria-controls="collapseRider{{ $r['id'] }}">
            
            {{-- Left: Avatar + Name + Contact --}}
            <div class="d-flex align-items-center gap-3">
                <div class="rider-avatar">
                    {{ strtoupper(substr($r['name'], 0, 1)) }}
                    <span class="status-dot" title="Active Fleet Rider"></span>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 mb-0.5 flex-wrap">
                        <span class="fw-bold text-dark" style="font-size: 1rem; letter-spacing: -0.2px;">{{ $r['name'] }}</span>
                        <span class="badge rounded-pill bg-light border text-secondary px-2 py-0.5" style="font-size: 0.68rem; font-weight: 600;">
                            Rider #{{ $r['id'] }}
                        </span>
                        @if($r['avg_rating'] >= 4.5 && $r['total_feedbacks'] > 0)
                            <span class="badge rounded-pill bg-warning bg-opacity-15 text-dark border border-warning border-opacity-30 px-2 py-0.5" style="font-size: 0.65rem; font-weight: 700;">
                                🏆 Top Rated
                            </span>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 0.78rem;">
                        <a href="tel:{{ $r['phone'] }}" class="text-decoration-none text-muted" onclick="event.stopPropagation();">
                            <i class="bi bi-telephone-fill text-success me-1"></i>{{ $r['phone'] ?: 'No Mobile' }}
                        </a>
                    </div>
                </div>
            </div>

            {{-- Right: Ratings + Delivered Count + Dropdown Trigger --}}
            <div class="d-flex align-items-center gap-2.5 flex-wrap">
                <!-- Rating Pill -->
                <div class="badge rounded-pill px-3 py-2 fw-bold font-monospace d-flex align-items-center gap-1.5" 
                     style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-size: 0.82rem;">
                    <i class="bi bi-star-fill text-warning"></i>
                    <span>{{ $r['total_feedbacks'] > 0 ? number_format($r['avg_rating'], 1) : '0.0' }}</span>
                    <span class="text-muted fw-normal" style="font-size: 0.72rem;">({{ $r['total_feedbacks'] }})</span>
                </div>

                <!-- Delivered Count Pill -->
                <div class="badge rounded-pill bg-light border text-dark px-3 py-2 fw-bold font-monospace d-flex align-items-center gap-1.5" style="font-size: 0.8rem;">
                    <i class="bi bi-box-seam text-primary"></i>
                    <span>{{ $r['total_deliveries'] }} Delivered</span>
                </div>

                <!-- Click Trigger Button -->
                <div class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 fw-semibold text-primary d-flex align-items-center gap-2 shadow-xs" style="font-size: 0.78rem;">
                    <span>Feedbacks</span>
                    <i class="bi bi-chevron-down chevron-rotate"></i>
                </div>
            </div>
        </div>

        {{-- Expanded Feedback Drawer --}}
        <div id="collapseRider{{ $r['id'] }}" 
             class="collapse {{ $loop->first && $r['feedbacks']->isNotEmpty() ? 'show' : '' }}" 
             data-bs-parent="#ridersAccordion">
            <div class="p-4 pt-3 border-top" style="background: #f8fafc;">
                
                {{-- Highlight Chips --}}
                @if(!empty($r['top_tags']))
                <div class="d-flex align-items-center gap-1.5 flex-wrap mb-3">
                    <span class="text-secondary small fw-bold text-uppercase me-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">Highlights:</span>
                    @foreach($r['top_tags'] as $tag => $count)
                        <span class="tag-chip">
                            {{ $tag }} <strong class="text-primary">({{ $count }})</strong>
                        </span>
                    @endforeach
                </div>
                @endif

                {{-- Feedback Items List --}}
                @if($r['feedbacks']->isNotEmpty())
                    <div class="d-flex flex-column gap-2.5">
                        @foreach($r['feedbacks'] as $fb)
                        <div class="review-bubble">
                            <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-1.5">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <div class="rounded-circle bg-light border text-dark fw-bold d-flex align-items-center justify-content-center" style="width: 26px; height: 26px; font-size: 0.72rem;">
                                        {{ strtoupper(substr($fb->user->name ?? 'C', 0, 1)) }}
                                    </div>
                                    <strong class="text-dark" style="font-size: 0.88rem;">{{ $fb->user->name ?? 'Customer' }}</strong>
                                    <span class="text-muted small">&bull;</span>
                                    <a href="{{ route('order-manager.orders.show', $fb->order) }}" class="fw-bold text-primary small text-decoration-none">
                                        #{{ $fb->order->order_number }}
                                    </a>
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    <!-- Stars Rating -->
                                    <div class="text-warning small font-monospace fw-bold">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="bi {{ $i <= $fb->delivery_rating ? 'bi-star-fill' : 'bi-star' }}" style="font-size: 0.75rem;"></i>
                                        @endfor
                                        <span class="text-dark ms-1">({{ $fb->delivery_rating }}/5)</span>
                                    </div>

                                    <span class="text-muted small ms-2" style="font-size: 0.72rem;">
                                        <i class="bi bi-clock me-1"></i>{{ $fb->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>

                            <!-- Comment & Tags -->
                            @if(!empty($fb->tags) && is_array($fb->tags))
                                <div class="d-flex flex-wrap gap-1 mb-2 mt-1">
                                    @foreach($fb->tags as $tag)
                                        <span class="badge rounded-pill px-2 py-0.5 fw-semibold" style="background: #f1f5f9; color: #475569; font-size: 0.68rem; border: 1px solid #e2e8f0;">
                                            {{ $tag }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            @if($fb->comment)
                                <div class="p-2.5 rounded-3 mt-1" style="background: #f8fafc; border-left: 3px solid #0284c7;">
                                    <p class="text-dark small mb-0 fst-italic" style="font-size: 0.83rem; line-height: 1.45;">
                                        "{{ $fb->comment }}"
                                    </p>
                                </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-3 text-muted small">
                        <i class="bi bi-emoji-smile me-1 text-primary"></i> No customer reviews recorded yet for {{ $r['name'] }}.
                    </div>
                @endif

            </div>
        </div>

    </div>
    @empty
    <div class="card border-0 shadow-sm rounded-4 p-4 text-center text-muted bg-white">
        No delivery riders found.
    </div>
    @endforelse
</div>

@endsection
