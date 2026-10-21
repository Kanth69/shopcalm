@extends('customer.account.layout')

@section('title', 'My Support Tickets')

@section('account_content')

{{-- Success / Error Alerts --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-xs mb-3 d-flex align-items-center gap-2 p-3" role="alert" style="background: #ecfdf5; color: #065f46; border-left: 4px solid #10b981 !important;">
        <i class="bi bi-check-circle-fill fs-5 text-success"></i>
        <div class="small fw-semibold flex-grow-1">{{ session('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-xs mb-3 d-flex align-items-center gap-2 p-3" role="alert" style="background: #fef2f2; color: #991b1b; border-left: 4px solid #ef4444 !important;">
        <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
        <div class="small fw-semibold flex-grow-1">{{ session('error') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- Hero Banner --}}
<div class="rounded-4 mb-3.5 mb-md-4 p-3.5 p-sm-4 p-md-4 shadow-sm position-relative overflow-hidden ticket-hero"
     style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #312e81 100%);">
    <div class="position-absolute w-100 h-100"
         style="background-image: radial-gradient(circle, rgba(255,255,255,0.06) 1px, transparent 1px); background-size: 20px 20px; top: 0; left: 0; pointer-events: none;"></div>
    
    <div class="position-relative z-1 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 gap-md-4">
        {{-- Title & Icon --}}
        <div class="d-flex align-items-center gap-2.5 gap-sm-3">
            <div class="rounded-3 d-flex align-items-center justify-content-center text-white flex-shrink-0 shadow-xs"
                 style="width: 46px; height: 46px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                <i class="bi bi-ticket-perforated-fill fs-5"></i>
            </div>
            <div>
                <h5 class="fw-bolder text-white mb-0.5" style="letter-spacing: -0.02em; font-size: clamp(1rem, 3.5vw, 1.2rem);">Support & Help Tickets</h5>
                <p class="text-white-50 small mb-0" style="font-size: 0.78rem; line-height: 1.35;">Track inquiries, replies & resolution status</p>
            </div>
        </div>

        {{-- Raise New Ticket Button (Compact on laptop, full-width touch friendly on mobile) --}}
        <div class="ticket-hero-cta flex-shrink-0">
            <button type="button" class="btn btn-warning rounded-pill px-3.5 py-2 fw-bold shadow-sm text-dark d-inline-flex align-items-center gap-2 text-nowrap"
                    data-bs-toggle="modal" data-bs-target="#newTicketModal" 
                    style="background: #fbbf24; border: none; font-size: 0.84rem; transition: transform 0.2s ease, box-shadow 0.2s ease;">
                <i class="bi bi-plus-circle-fill text-dark"></i>
                <span>Raise New Ticket</span>
            </button>
        </div>
    </div>
</div>

{{-- ── Filter Chips (Guaranteed Single Clean Line with Smooth Touch Swipe) ── --}}
<div class="ticket-filter-bar mb-3 mb-md-4">
    <div class="d-flex align-items-center justify-content-between gap-3">
        
        {{-- Tabs row: Strictly flex-nowrap in one single line --}}
        <div class="d-flex align-items-center gap-2 overflow-x-auto text-nowrap no-scrollbar flex-nowrap py-1.5 px-0.5" 
             style="scrollbar-width: none; -ms-overflow-style: none; -webkit-overflow-scrolling: touch;">
            
            {{-- All Tickets --}}
            <a href="{{ route('account.tickets') }}" 
               class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-2 text-decoration-none flex-shrink-0 {{ empty($filter) ? 'text-white shadow-xs' : 'bg-white border text-dark shadow-2xs hover-pill' }}"
               style="font-size: 0.82rem; white-space: nowrap; {{ empty($filter) ? 'background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none;' : 'border-color: #cbd5e1 !important;' }}">
                <i class="bi bi-collection-fill {{ empty($filter) ? 'text-white' : 'text-primary' }}"></i>
                <span>All Tickets</span>
                <span class="badge rounded-pill ms-1 {{ empty($filter) ? 'bg-white text-primary' : 'bg-light text-secondary border' }}" 
                      style="font-size: 0.72rem; padding: 0.22em 0.55em;">{{ $totalTickets }}</span>
            </a>

            {{-- Open / In Progress --}}
            <a href="{{ route('account.tickets', ['status' => 'open']) }}" 
               class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-2 text-decoration-none flex-shrink-0 {{ $filter === 'open' ? 'text-dark shadow-xs' : 'bg-white border text-dark shadow-2xs hover-pill' }}"
               style="font-size: 0.82rem; white-space: nowrap; {{ $filter === 'open' ? 'background: #fbbf24; border: none;' : 'border-color: #cbd5e1 !important;' }}">
                <i class="bi bi-hourglass-split {{ $filter === 'open' ? 'text-dark' : 'text-warning' }}"></i>
                <span>Open / In Progress</span>
                <span class="badge rounded-pill ms-1 {{ $filter === 'open' ? 'bg-dark text-white' : 'bg-light text-secondary border' }}" 
                      style="font-size: 0.72rem; padding: 0.22em 0.55em;">{{ $openTickets }}</span>
            </a>

            {{-- Resolved --}}
            <a href="{{ route('account.tickets', ['status' => 'resolved']) }}" 
               class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-2 text-decoration-none flex-shrink-0 {{ $filter === 'resolved' ? 'text-white shadow-xs' : 'bg-white border text-dark shadow-2xs hover-pill' }}"
               style="font-size: 0.82rem; white-space: nowrap; {{ $filter === 'resolved' ? 'background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none;' : 'border-color: #cbd5e1 !important;' }}">
                <i class="bi bi-check-circle-fill {{ $filter === 'resolved' ? 'text-white' : 'text-success' }}"></i>
                <span>Resolved</span>
                <span class="badge rounded-pill ms-1 {{ $filter === 'resolved' ? 'bg-white text-success' : 'bg-light text-secondary border' }}" 
                      style="font-size: 0.72rem; padding: 0.22em 0.55em;">{{ $resolvedTickets }}</span>
            </a>
        </div>

        {{-- Response SLA indicator (desktop only) --}}
        <div class="text-muted small text-nowrap d-none d-lg-flex align-items-center flex-shrink-0 px-3 py-1.5 rounded-pill bg-white border shadow-2xs" style="font-size: 0.76rem; border-color: #cbd5e1 !important;">
            <i class="bi bi-clock-history text-primary me-2"></i>
            <span>Replies usually in 2–4 hrs</span>
        </div>

    </div>
</div>

{{-- ── Tickets Grid (Clean 2-Column Grid on Desktop/Tablet, Responsive Cards on Mobile) ── --}}
@if($tickets->isNotEmpty())
    <div class="row g-3 g-md-4 mb-4 ticket-grid">
        @foreach($tickets as $ticket)
            @php
                $statusKey = $ticket->status ?? 'unread';
                if ($ticket->is_read && $statusKey === 'unread') {
                    $statusKey = 'in_progress';
                }

                $badgeClass = match ($statusKey) {
                    'resolved', 'closed' => 'bg-success-subtle text-success border border-success-subtle',
                    'in_progress' => 'bg-primary-subtle text-primary border border-primary-subtle',
                    default => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                };

                $badgeLabel = match ($statusKey) {
                    'resolved', 'closed' => 'Resolved',
                    'in_progress' => 'In Progress',
                    default => 'Under Review',
                };

                $badgeIcon = match ($statusKey) {
                    'resolved', 'closed' => 'bi-check-circle-fill',
                    'in_progress' => 'bi-arrow-repeat',
                    default => 'bi-hourglass-split',
                };
            @endphp

            <div class="col-12 col-lg-6 d-flex">
                <div class="card border-0 shadow-xs rounded-4 ticket-card w-100 d-flex flex-column" 
                     style="background: #ffffff; border: 1.5px solid #e2e8f0 !important; border-radius: 16px !important;">
                    <div class="p-3.5 p-sm-4 d-flex flex-column h-100">
                        
                        {{-- Top Meta: ID + Status Badge + Date --}}
                        <div class="d-flex align-items-center justify-content-between gap-2 pb-2.5 border-bottom mb-3" style="border-color: #f1f5f9 !important;">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge rounded-pill px-2.5 py-1 font-monospace fw-bold" 
                                      style="background: #f1f5f9; color: #334155; font-size: 0.78rem; border: 1px solid #cbd5e1;">
                                    #TKT-{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}
                                </span>
                                <span class="badge rounded-pill px-2.5 py-1 fw-bold d-inline-flex align-items-center gap-1.5 {{ $badgeClass }}" 
                                      style="font-size: 0.75rem;">
                                    <i class="bi {{ $badgeIcon }}"></i> <span>{{ $badgeLabel }}</span>
                                </span>
                            </div>

                            <div class="text-muted small d-inline-flex align-items-center gap-1 flex-shrink-0" style="font-size: 0.75rem;">
                                <i class="bi bi-clock opacity-75"></i>
                                <span>{{ $ticket->created_at ? $ticket->created_at->format('d M, Y') : '—' }}</span>
                            </div>
                        </div>

                        {{-- Subject (Clean bold heading without redundant category label) --}}
                        <h6 class="fw-bold text-dark mb-2.5 lh-sm text-truncate-2" style="font-size: 0.96rem; letter-spacing: -0.01em;">
                            {{ $ticket->subject }}
                        </h6>

                        {{-- Customer Query Box (Direct & spacious) --}}
                        <div class="p-3 rounded-3 mb-3 flex-grow-1" style="background: #f8fafc; border: 1px solid #edf2f7; border-left: 3.5px solid #6366f1 !important;">
                            <div class="text-secondary small lh-base" style="font-size: 0.86rem; line-height: 1.55; white-space: pre-wrap;">{{ $ticket->message }}</div>
                        </div>

                        {{-- Support Response OR Clean Minimal Status Indicator --}}
                        @if(!empty($ticket->reply_notes))
                            <div class="p-3 rounded-3 mb-3" style="background: #f0fdf4; border: 1px solid #bbf7d0; border-left: 3.5px solid #10b981 !important;">
                                <div class="d-flex align-items-center justify-content-between mb-1.5 flex-wrap gap-1">
                                    <span class="text-success small fw-bold d-inline-flex align-items-center gap-1" style="font-size: 0.74rem;">
                                        <i class="bi bi-headset"></i> <span>Support Reply:</span>
                                    </span>
                                    @if($ticket->resolved_at)
                                        <span class="text-muted small" style="font-size: 0.7rem;">
                                            {{ $ticket->resolved_at->format('d M, h:i A') }}
                                        </span>
                                    @endif
                                </div>
                                <div class="text-dark small lh-base" style="font-size: 0.86rem; line-height: 1.55; white-space: pre-wrap;">{{ $ticket->reply_notes }}</div>
                            </div>
                        @else
                            {{-- Compact 1-line status indicator (No bloated unwanted text paragraphs!) --}}
                            <div class="py-2 px-2.5 rounded-3 mb-3 d-flex align-items-center gap-2" 
                                 style="font-size: 0.78rem; {{ $statusKey === 'resolved' || $statusKey === 'closed' ? 'background: #f0fdf4; color: #166534;' : ($statusKey === 'in_progress' ? 'background: #eff6ff; color: #1e40af;' : 'background: #fffbeb; color: #92400e;') }}">
                                @if($statusKey === 'resolved' || $statusKey === 'closed')
                                    <i class="bi bi-check-circle-fill text-success flex-shrink-0"></i>
                                    <span>Ticket marked resolved</span>
                                    @if($ticket->resolved_at)
                                        <span class="ms-auto text-muted small">{{ $ticket->resolved_at->format('d M, Y') }}</span>
                                    @endif
                                @elseif($statusKey === 'in_progress')
                                    <div class="spinner-border spinner-border-sm text-primary flex-shrink-0" style="width: 12px; height: 12px;" role="status"></div>
                                    <span>Agent working on ticket</span>
                                @else
                                    <i class="bi bi-hourglass-split text-warning flex-shrink-0"></i>
                                    <span>Queued for review (2–4 hrs)</span>
                                @endif
                            </div>
                        @endif

                        {{-- Footer: Contact Info & Time --}}
                        <div class="pt-2.5 mt-auto border-top d-flex align-items-center justify-content-between flex-wrap gap-2" 
                             style="border-color: #f1f5f9 !important;">
                            <div class="d-flex align-items-center gap-2.5 flex-wrap min-w-0">
                                @if($ticket->email)
                                    <span class="text-muted small text-truncate d-inline-flex align-items-center gap-1.5" style="font-size: 0.76rem;" title="{{ $ticket->email }}">
                                        <i class="bi bi-envelope text-primary flex-shrink-0"></i>
                                        <span class="text-truncate" style="max-width: 160px;">{{ $ticket->email }}</span>
                                    </span>
                                @endif
                                @if($ticket->mobile)
                                    <span class="text-muted small d-inline-flex align-items-center gap-1.5" style="font-size: 0.76rem;">
                                        <i class="bi bi-telephone text-success flex-shrink-0"></i>
                                        <span>{{ $ticket->mobile }}</span>
                                    </span>
                                @endif
                            </div>
                            <span class="text-secondary opacity-75 small fw-medium ms-auto" style="font-size: 0.72rem;">
                                {{ $ticket->created_at ? $ticket->created_at->diffForHumans() : '' }}
                            </span>
                        </div>

                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($tickets->hasPages())
        <div class="mt-3">
            {{ $tickets->links('pagination::bootstrap-5') }}
        </div>
    @endif
@else
    <div class="card border-0 shadow-xs rounded-4 p-4 p-md-5 text-center mb-4" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
        <div class="d-inline-flex align-items-center justify-content-center mb-3 rounded-circle text-warning shadow-xs mx-auto" 
             style="width: 64px; height: 64px; background: #fef3c7;">
            <i class="bi bi-ticket-perforated fs-2"></i>
        </div>
        <h5 class="fw-bold text-dark mb-1">No Support Tickets Found</h5>
        <p class="text-muted small mb-3.5 mx-auto" style="max-width: 420px; font-size: 0.84rem; line-height: 1.5;">
            @if(!empty($filter))
                You don't have any tickets under the "{{ ucfirst($filter) }}" status.
            @else
                Have a question about an order, shipment, refund, or anything else? Raise a support ticket and our team will assist you immediately.
            @endif
        </p>
        <div class="d-flex align-items-center justify-content-center gap-2">
            @if(!empty($filter))
                <a href="{{ route('account.tickets') }}" class="btn btn-outline-secondary rounded-pill px-3.5 py-2 fw-semibold btn-sm">
                    View All Tickets
                </a>
            @endif
            <button type="button" class="btn btn-warning rounded-pill px-4 py-2 fw-bold btn-sm shadow-xs text-dark"
                    data-bs-toggle="modal" data-bs-target="#newTicketModal" style="background: #fbbf24; border: none;">
                <i class="bi bi-plus-circle-fill me-1"></i> Raise A Ticket
            </button>
        </div>
    </div>
@endif

{{-- Raise New Ticket Modal --}}
<div class="modal fade" id="newTicketModal" tabindex="-1" aria-labelledby="newTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header py-3 px-3.5 px-md-4 border-bottom" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff;">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 d-flex align-items-center justify-content-center text-white" style="width: 32px; height: 32px; background: #f59e0b;">
                        <i class="bi bi-headset"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bolder mb-0 text-white" id="newTicketModalLabel" style="font-size: 0.95rem;">Raise Support Ticket</h6>
                        <span class="text-white-50 small" style="font-size: 0.72rem;">We respond within 2–4 hours</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('page.contact.submit') }}" method="POST">
                @csrf
                <div class="modal-body p-3.5 p-md-4">
                    <div class="row g-2.5">
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold text-dark mb-1" style="font-size: 0.78rem;">Your Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control rounded-3" value="{{ old('name', $user->name) }}" required style="font-size: 0.85rem;">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold text-dark mb-1" style="font-size: 0.78rem;">Mobile Number <span class="text-danger">*</span></label>
                            <input type="tel" name="mobile" class="form-control rounded-3" value="{{ old('mobile', $user->mobile_number ?? ($user->phone ?? '')) }}" required pattern="[0-9]{10}" maxlength="10" minlength="10" inputmode="numeric" placeholder="10-digit mobile number" style="font-size: 0.85rem;">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark mb-1" style="font-size: 0.78rem;">Email Address <small class="text-muted fw-normal">(Optional)</small></label>
                            <input type="email" name="email" class="form-control rounded-3" value="{{ old('email', $user->email) }}" placeholder="name@example.com (Optional)" style="font-size: 0.85rem;">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark mb-1" style="font-size: 0.78rem;">Subject / Issue Category <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control rounded-3" value="{{ old('subject') }}" placeholder="e.g., Order Delivery Delay / Wrong Item / Refund status" required style="font-size: 0.85rem;">
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold text-dark mb-0" style="font-size: 0.78rem;">Message Details <span class="text-danger">*</span></label>
                                <span id="modalWordCountBadge" class="badge bg-light text-muted border font-monospace" style="font-size: 0.7rem;">
                                    <span id="modalWordCount">0</span> / 250 words
                                </span>
                            </div>
                            <textarea id="modalTicketMessage" name="message" class="form-control rounded-3" rows="4" placeholder="Please explain your question or issue (Maximum 250 words)..." required style="font-size: 0.85rem;">{{ old('message') }}</textarea>
                            <div class="d-flex justify-content-end mt-1">
                                <small id="modalWordCountStatus" class="small" style="font-size: 0.72rem;"></small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2.5 px-3.5 px-md-4 border-top d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-xs d-inline-flex align-items-center gap-2"
                            style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border: none;">
                        <i class="bi bi-send-fill me-1"></i> <span>Submit Ticket</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.ticket-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.ticket-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08) !important;
}
.hover-pill:hover {
    background-color: #f1f5f9;
    color: #0f172a !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalMsg = document.getElementById('modalTicketMessage');
    const modalWordSpan = document.getElementById('modalWordCount');
    const modalBadge = document.getElementById('modalWordCountBadge');
    const modalStatus = document.getElementById('modalWordCountStatus');
    const maxWords = 250;

    if (!modalMsg) return;

    function updateModalWordCount() {
        const text = modalMsg.value;
        const words = text.trim() ? text.trim().split(/\s+/).filter(Boolean) : [];
        let count = words.length;

        if (count > maxWords) {
            modalMsg.value = words.slice(0, maxWords).join(' ');
            count = maxWords;
            if (modalStatus) {
                modalStatus.textContent = 'Maximum 250 words reached';
                modalStatus.className = 'text-danger small fw-semibold';
            }
        } else if (count >= maxWords - 20) {
            if (modalStatus) {
                modalStatus.textContent = `${maxWords - count} words remaining`;
                modalStatus.className = 'text-warning small fw-semibold';
            }
        } else {
            if (modalStatus) {
                modalStatus.textContent = '';
                modalStatus.className = 'text-muted small';
            }
        }

        if (modalWordSpan) modalWordSpan.textContent = count;

        if (modalBadge) {
            if (count >= maxWords) {
                modalBadge.className = 'badge bg-danger text-white border font-monospace';
            } else if (count >= maxWords - 20) {
                modalBadge.className = 'badge bg-warning text-dark border font-monospace';
            } else if (count > 0) {
                modalBadge.className = 'badge bg-primary-subtle text-primary border border-primary-subtle font-monospace';
            } else {
                modalBadge.className = 'badge bg-light text-muted border font-monospace';
            }
        }
    }

    modalMsg.addEventListener('input', updateModalWordCount);
    modalMsg.addEventListener('paste', function() {
        setTimeout(updateModalWordCount, 20);
    });
    updateModalWordCount();
});
</script>

@endsection
