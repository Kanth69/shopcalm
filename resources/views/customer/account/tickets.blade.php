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
<div class="rounded-4 mb-3.5 p-3.5 p-md-4 d-flex align-items-center justify-content-between flex-wrap gap-3 shadow-xs position-relative overflow-hidden"
     style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #312e81 100%);">
    <div class="position-absolute w-100 h-100"
         style="background-image: radial-gradient(circle, rgba(255,255,255,0.06) 1px, transparent 1px); background-size: 20px 20px; top: 0; left: 0; pointer-events: none;"></div>
    
    <div class="d-flex align-items-center gap-3 position-relative z-1">
        <div class="rounded-3 d-flex align-items-center justify-content-center text-white flex-shrink-0 shadow"
             style="width: 52px; height: 52px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
            <i class="bi bi-ticket-perforated-fill fs-4"></i>
        </div>
        <div>
            <h5 class="fw-bolder text-white mb-0.5" style="letter-spacing: -0.02em;">Support & Help Tickets</h5>
            <p class="text-white-50 small mb-0">Track your customer inquiries, replies & resolution status</p>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2 position-relative z-1">
        <button type="button" class="btn btn-warning rounded-pill px-3.5 py-2 fw-bold btn-sm shadow text-dark d-flex align-items-center gap-1.5"
                data-bs-toggle="modal" data-bs-target="#newTicketModal" style="background: #fbbf24; border: none; font-size: 0.82rem;">
            <i class="bi bi-plus-circle-fill"></i> Raise New Ticket
        </button>
    </div>
</div>

{{-- Filter Tabs --}}
<div class="card border-0 shadow-xs rounded-4 mb-3" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
    <div class="card-body p-2.5">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="nav nav-pills gap-1.5" role="tablist">
                <a href="{{ route('account.tickets') }}" 
                   class="nav-link rounded-pill px-3 py-1.5 fw-semibold small d-inline-flex align-items-center gap-1.5 {{ empty($filter) ? 'active bg-primary text-white' : 'text-secondary hover-pill' }}"
                   style="font-size: 0.8rem;">
                    <span>All Tickets</span>
                    <span class="badge rounded-pill {{ empty($filter) ? 'bg-white text-primary' : 'bg-light text-dark border' }}">{{ $totalTickets }}</span>
                </a>
                <a href="{{ route('account.tickets', ['status' => 'open']) }}" 
                   class="nav-link rounded-pill px-3 py-1.5 fw-semibold small d-inline-flex align-items-center gap-1.5 {{ $filter === 'open' ? 'active bg-warning text-dark' : 'text-secondary hover-pill' }}"
                   style="font-size: 0.8rem;">
                    <span>Open / In Progress</span>
                    <span class="badge rounded-pill {{ $filter === 'open' ? 'bg-dark text-white' : 'bg-warning-subtle text-dark border border-warning' }}">{{ $openTickets }}</span>
                </a>
                <a href="{{ route('account.tickets', ['status' => 'resolved']) }}" 
                   class="nav-link rounded-pill px-3 py-1.5 fw-semibold small d-inline-flex align-items-center gap-1.5 {{ $filter === 'resolved' ? 'active bg-success text-white' : 'text-secondary hover-pill' }}"
                   style="font-size: 0.8rem;">
                    <span>Resolved</span>
                    <span class="badge rounded-pill {{ $filter === 'resolved' ? 'bg-white text-success' : 'bg-success-subtle text-success border border-success' }}">{{ $resolvedTickets }}</span>
                </a>
            </div>

            <div class="text-muted small px-2 d-none d-sm-block" style="font-size: 0.74rem;">
                <i class="bi bi-clock-history me-1"></i> Responses usually within 2–4 hours
            </div>
        </div>
    </div>
</div>

{{-- Tickets List --}}
@if($tickets->isNotEmpty())
    <div class="d-flex flex-column gap-3 mb-4">
        @foreach($tickets as $ticket)
            @php
                $statusKey = $ticket->status ?? 'unread';
                if ($ticket->is_read && $statusKey === 'unread') {
                    $statusKey = 'in_progress';
                }

                $badgeBg = match ($statusKey) {
                    'resolved', 'closed' => 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
                    'in_progress' => 'linear-gradient(135deg, #0284c7 0%, #0369a1 100%)',
                    default => 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)',
                };

                $badgeLabel = match ($statusKey) {
                    'resolved', 'closed' => 'Resolved',
                    'in_progress' => 'In Progress',
                    default => 'Open / Under Review',
                };

                $badgeIcon = match ($statusKey) {
                    'resolved', 'closed' => 'bi-check-circle-fill',
                    'in_progress' => 'bi-arrow-repeat',
                    default => 'bi-hourglass-split',
                };
            @endphp

            <div class="card border-0 shadow-xs rounded-4 overflow-hidden ticket-card" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
                {{-- Ticket Header --}}
                <div class="card-header bg-white py-3 px-3.5 px-md-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge rounded-pill px-2.5 py-1 font-monospace fw-bold" style="background: #f1f5f9; color: #334155; font-size: 0.75rem; border: 1px solid #cbd5e1;">
                            #TKT-{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}
                        </span>
                        <span class="badge rounded-pill px-2.5 py-1 text-white fw-bold d-inline-flex align-items-center gap-1"
                              style="background: {{ $badgeBg }}; font-size: 0.72rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                            <i class="bi {{ $badgeIcon }}"></i> {{ $badgeLabel }}
                        </span>
                    </div>

                    <div class="text-muted small d-flex align-items-center gap-2" style="font-size: 0.75rem;">
                        <span><i class="bi bi-calendar3 me-1 opacity-75"></i>{{ $ticket->created_at ? $ticket->created_at->format('d M, Y • h:i A') : '—' }}</span>
                        <span>•</span>
                        <span class="text-secondary fw-semibold">{{ $ticket->created_at ? $ticket->created_at->diffForHumans() : '' }}</span>
                    </div>
                </div>

                <div class="card-body p-3.5 p-md-4">
                    {{-- Subject --}}
                    <h6 class="fw-bolder text-dark mb-2" style="font-size: 0.95rem; letter-spacing: -0.01em;">
                        {{ $ticket->subject }}
                    </h6>

                    {{-- User's Query Message --}}
                    <div class="p-3 rounded-3 mb-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                        <div class="d-flex align-items-center gap-2 mb-1 text-muted small fw-semibold" style="font-size: 0.72rem; text-uppercase; letter-spacing: 0.5px;">
                            <i class="bi bi-person-fill text-primary"></i> Your Query:
                        </div>
                        <p class="text-secondary mb-0" style="font-size: 0.85rem; line-height: 1.6; white-space: pre-wrap;">{{ $ticket->message }}</p>
                    </div>

                    {{-- Support Team Response / Reply Notes --}}
                    @if(!empty($ticket->reply_notes))
                        <div class="p-3.5 rounded-3 mb-2" style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); border: 1.5px solid #a7f3d0;">
                            <div class="d-flex align-items-center justify-content-between mb-1.5 flex-wrap gap-1">
                                <div class="d-flex align-items-center gap-1.5 text-success fw-bold small" style="font-size: 0.78rem;">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 20px; height: 20px; background: #10b981; font-size: 0.65rem;">
                                        <i class="bi bi-headset"></i>
                                    </div>
                                    <span>Support Team Response</span>
                                </div>
                                @if($ticket->resolved_at)
                                    <span class="text-muted small" style="font-size: 0.72rem;">
                                        <i class="bi bi-check2-circle text-success me-1"></i>Replied {{ $ticket->resolved_at->format('d M, Y • h:i A') }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-dark mb-0 fw-normal" style="font-size: 0.86rem; line-height: 1.6; white-space: pre-wrap;">{{ $ticket->reply_notes }}</p>
                        </div>
                    @else
                        <div class="d-flex align-items-center gap-2 text-muted small py-1" style="font-size: 0.78rem;">
                            <i class="bi bi-info-circle text-primary"></i>
                            <span>Our support agent is currently reviewing your ticket. Any updates will appear directly here.</span>
                        </div>
                    @endif
                </div>

                {{-- Footer info --}}
                <div class="card-footer bg-white border-top py-2 px-3.5 px-md-4 d-flex align-items-center justify-content-between text-muted small" style="font-size: 0.74rem;">
                    <span><i class="bi bi-envelope me-1"></i> {{ $ticket->email }}</span>
                    @if($ticket->mobile)
                        <span><i class="bi bi-phone me-1"></i> {{ $ticket->mobile }}</span>
                    @endif
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
                            <label class="form-label small fw-bold text-dark mb-1" style="font-size: 0.78rem;">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control rounded-3" value="{{ old('email', $user->email) }}" required style="font-size: 0.85rem;">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark mb-1" style="font-size: 0.78rem;">Mobile / WhatsApp Number</label>
                            <input type="tel" name="mobile" class="form-control rounded-3" value="{{ old('mobile', $user->mobile_number ?? $user->phone) }}" placeholder="10-digit mobile number" style="font-size: 0.85rem;">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark mb-1" style="font-size: 0.78rem;">Subject / Issue Category <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control rounded-3" value="{{ old('subject') }}" placeholder="e.g., Order Delivery Delay / Wrong Item / Refund status" required style="font-size: 0.85rem;">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark mb-1" style="font-size: 0.78rem;">Message Details <span class="text-danger">*</span></label>
                            <textarea name="message" class="form-control rounded-3" rows="4" placeholder="Please explain your question or issue with relevant order numbers, details, etc." required style="font-size: 0.85rem;">{{ old('message') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2.5 px-3.5 px-md-4 border-top d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-xs d-flex align-items-center gap-1.5"
                            style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border: none;">
                        <i class="bi bi-send-fill"></i> Submit Ticket
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

@endsection
