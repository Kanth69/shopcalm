@extends('order-manager.layouts.app')

@section('title', 'Delivery Alerts & Exceptions')
@section('header', 'Delivery Alerts & Exceptions')

@section('content')

@php
    $deliveryPartners = \App\Models\User::deliveryPartners()->get();
@endphp

<!-- Header Command Box -->
<div class="card border-0 shadow-sm rounded-4 mb-4 p-4 bg-white" style="border-top: 4px solid #e11d48 !important;">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1.5">
                <span class="badge rounded-pill px-3 py-1 fw-bold text-white shadow-xs" style="background: #e11d48; font-size: 0.72rem;">
                    LIVE FLEET EXCEPTIONS
                </span>
                <span class="text-secondary small fw-semibold">&bull; Central Hub #01</span>
            </div>
            <h4 class="fw-bolder text-dark mb-1" style="letter-spacing: -0.4px;">
                Rider Exception Logs & Resolution Center
            </h4>
            <p class="text-secondary small mb-0" style="max-width: 600px;">
                Real-time delivery issues reported by Bengaluru local fleet partners. Resolving an issue automatically dismisses active alerts.
            </p>
        </div>

        <div class="d-flex align-items-center gap-3">
            <div class="p-3 rounded-4 bg-light border text-center">
                <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.68rem;">Pending Action</div>
                <div class="h3 fw-bolder {{ $pendingResolutionsCount > 0 ? 'text-danger' : 'text-success' }} mb-0 mt-0.5">
                    {{ $pendingResolutionsCount }}
                </div>
            </div>
            <div class="p-3 rounded-4 bg-light border text-center">
                <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.68rem;">Total Resolved</div>
                <div class="h3 fw-bolder text-success mb-0 mt-0.5">
                    {{ $resolvedCount }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-pills mb-3 gap-2" id="alertsTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active rounded-pill px-4 py-2 fw-bold shadow-xs d-flex align-items-center gap-2" 
                id="active-tab" data-bs-toggle="tab" data-bs-target="#active-pane" type="button" role="tab">
            <i class="bi bi-exclamation-octagon-fill text-danger"></i>
            <span>Pending Action</span>
            <span class="badge rounded-pill bg-danger text-white ms-1">{{ $pendingResolutionsCount }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link rounded-pill px-4 py-2 fw-bold shadow-xs d-flex align-items-center gap-2 bg-white text-dark border" 
                id="resolved-tab" data-bs-toggle="tab" data-bs-target="#resolved-pane" type="button" role="tab">
            <i class="bi bi-check2-circle text-success"></i>
            <span>Resolved Incidents History</span>
            <span class="badge rounded-pill bg-light text-secondary border ms-1">{{ $resolvedCount }}</span>
        </button>
    </li>
</ul>

<div class="tab-content" id="alertsTabContent">
    <!-- Tab 1: Active Unresolved Exceptions -->
    <div class="tab-pane fade show active" id="active-pane" role="tabpanel">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.92rem;">
                    <i class="bi bi-bell-fill text-danger me-1.5"></i> Active Exceptions Requiring Resolution
                </h6>
                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger rounded-pill px-2.5 py-1 small fw-bold">
                    {{ $pendingResolutionsCount }} Active
                </span>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.84rem;">
                        <thead class="bg-light text-secondary text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                            <tr>
                                <th class="ps-4">Order Ref</th>
                                <th>Customer Details</th>
                                <th>Reported Exception</th>
                                <th>Delivery Partner</th>
                                <th>Reported At</th>
                                <th class="text-end pe-4">Resolution</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($activeExceptions as $fulf)
                                <tr>
                                    <td class="ps-4">
                                        @if($fulf->order)
                                            <a href="{{ route('order-manager.orders.show', $fulf->order) }}" class="fw-bolder text-primary text-decoration-none font-monospace">
                                                #{{ $fulf->order->order_number }}
                                            </a>
                                            <div class="text-secondary small" style="font-size: 0.7rem;">
                                                {{ $fulf->order->shipping_city }} - {{ $fulf->order->shipping_zip }}
                                            </div>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($fulf->order)
                                            <div class="fw-bold text-dark">{{ $fulf->order->shipping_name }}</div>
                                            <div class="d-flex align-items-center gap-2 mt-0.5">
                                                <a href="tel:{{ $fulf->order->shipping_phone }}" class="text-decoration-none text-secondary small" title="Call Customer">
                                                    <i class="bi bi-telephone-fill text-success me-1"></i>{{ $fulf->order->shipping_phone }}
                                                </a>
                                                @php
                                                    $cleanPh = preg_replace('/[^0-9]/', '', $fulf->order->shipping_phone);
                                                    if (strlen($cleanPh) === 10) $cleanPh = '91' . $cleanPh;
                                                @endphp
                                                <a href="https://wa.me/{{ $cleanPh }}" target="_blank" class="text-success small" title="WhatsApp Customer">
                                                    <i class="bi bi-whatsapp"></i>
                                                </a>
                                            </div>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.75rem;">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $fulf->delivery_issue }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark small">
                                            🛵 {{ $fulf->rider?->name ?? ($fulf->rider_name ?? 'In-House Rider') }}
                                        </div>
                                        <div class="text-secondary small font-monospace" style="font-size: 0.68rem;">
                                            {{ $fulf->rider?->mobile_number ?? ($fulf->rider_phone ?? 'Fleet #01') }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-secondary small">{{ $fulf->delivery_issue_at?->diffForHumans() ?? 'Just now' }}</span>
                                        <div class="text-muted small font-monospace" style="font-size: 0.68rem;">{{ $fulf->delivery_issue_at?->format('d M, h:i A') }}</div>
                                    </td>
                                    <td class="text-end pe-4">
                                        @if($fulf->order)
                                            <button type="button" class="btn btn-sm btn-danger rounded-pill px-3 py-1 fw-bold shadow-xs" 
                                                    data-bs-toggle="modal" data-bs-target="#resolveModal{{ $fulf->id }}" style="font-size: 0.75rem;">
                                                <i class="bi bi-wrench-adjustable me-1"></i> Resolve
                                            </button>

                                            <!-- Resolve Modal -->
                                            <div class="modal fade text-start" id="resolveModal{{ $fulf->id }}" tabindex="-1" aria-labelledby="resolveModalLabel{{ $fulf->id }}" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                                                        <div class="modal-header bg-light py-3 px-4 border-bottom">
                                                            <div>
                                                                <span class="badge bg-danger rounded-pill px-2 py-0.5 text-white small fw-bold">Exception Resolution</span>
                                                                <h6 class="modal-title fw-bolder text-dark mb-0 mt-1" id="resolveModalLabel{{ $fulf->id }}">
                                                                    Order #{{ $fulf->order->order_number }}
                                                                </h6>
                                                            </div>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>

                                                        <form action="{{ route('order-manager.orders.resolve-exception', $fulf->order) }}" method="POST">
                                                            @csrf
                                                            <div class="modal-body p-4">
                                                                <!-- Customer Summary -->
                                                                <div class="p-3 rounded-3 bg-light border mb-3">
                                                                    <div class="d-flex align-items-center justify-content-between">
                                                                        <div class="fw-bold text-dark">{{ $fulf->order->shipping_name }}</div>
                                                                        <a href="tel:{{ $fulf->order->shipping_phone }}" class="btn btn-sm btn-success rounded-pill px-2.5 py-0.5 fw-bold" style="font-size: 0.72rem;">
                                                                            <i class="bi bi-telephone-fill me-1"></i> {{ $fulf->order->shipping_phone }}
                                                                        </a>
                                                                    </div>
                                                                    <div class="small text-danger fw-bold mt-1.5">
                                                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Issue: {{ $fulf->delivery_issue }}
                                                                    </div>
                                                                </div>

                                                                <!-- Resolution Action Selector -->
                                                                <label class="form-label fw-bold text-dark small">Select Resolution Action <span class="text-danger">*</span></label>
                                                                <div class="mb-3">
                                                                    <div class="form-check p-3 rounded-3 border mb-2 bg-white">
                                                                        <input class="form-check-input" type="radio" name="resolution_action" id="actionReattempt{{ $fulf->id }}" value="re-attempt" checked onchange="toggleResolveOptions('{{ $fulf->id }}', 'reattempt')">
                                                                        <label class="form-check-label fw-bold text-dark" for="actionReattempt{{ $fulf->id }}">
                                                                            🚀 1. Customer Available Now — Re-attempt Delivery Today
                                                                            <div class="text-secondary small fw-normal">Keeps order on rider's active run-sheet for immediate handover.</div>
                                                                        </label>
                                                                    </div>

                                                                    <div class="form-check p-3 rounded-3 border mb-2 bg-white">
                                                                        <input class="form-check-input" type="radio" name="resolution_action" id="actionReschedule{{ $fulf->id }}" value="reschedule" onchange="toggleResolveOptions('{{ $fulf->id }}', 'reschedule')">
                                                                        <label class="form-check-label fw-bold text-dark" for="actionReschedule{{ $fulf->id }}">
                                                                            📅 2. Reschedule Delivery Slot / Reassign Rider
                                                                            <div class="text-secondary small fw-normal">Change delivery window to tomorrow or evening slot.</div>
                                                                        </label>
                                                                    </div>

                                                                    <div class="form-check p-3 rounded-3 border mb-2 bg-white">
                                                                        <input class="form-check-input" type="radio" name="resolution_action" id="actionReturn{{ $fulf->id }}" value="return_warehouse" onchange="toggleResolveOptions('{{ $fulf->id }}', 'return')">
                                                                        <label class="form-check-label fw-bold text-dark" for="actionReturn{{ $fulf->id }}">
                                                                            🔄 3. Undeliverable — Return to Warehouse Inventory
                                                                            <div class="text-secondary small fw-normal">Marks order returned and restocks parcel products.</div>
                                                                        </label>
                                                                    </div>

                                                                    <div class="form-check p-3 rounded-3 border bg-white">
                                                                        <input class="form-check-input" type="radio" name="resolution_action" id="actionCancel{{ $fulf->id }}" value="cancel" onchange="toggleResolveOptions('{{ $fulf->id }}', 'cancel')">
                                                                        <label class="form-check-label fw-bold text-dark" for="actionCancel{{ $fulf->id }}">
                                                                            ❌ 4. Customer Refused — Cancel Order
                                                                            <div class="text-secondary small fw-normal">Cancels order immediately.</div>
                                                                        </label>
                                                                    </div>
                                                                </div>

                                                                <!-- Reschedule Options (Hidden by default) -->
                                                                <div id="rescheduleOptions{{ $fulf->id }}" class="p-3 bg-light rounded-3 border mb-3 d-none">
                                                                    <div class="mb-2">
                                                                        <label class="form-label small fw-bold text-dark">New Delivery Slot</label>
                                                                        <select name="delivery_slot" class="form-select form-select-sm">
                                                                            <option value="Next-Day Express Delivery (1-2 Days)">⚡ Next-Day Express Delivery (Tomorrow)</option>
                                                                            <option value="Morning Slot (10:00 AM - 02:00 PM)">🌅 Morning Slot (10:00 AM - 02:00 PM)</option>
                                                                            <option value="Evening Slot (04:00 PM - 08:00 PM)">🌆 Evening Slot (04:00 PM - 08:00 PM)</option>
                                                                        </select>
                                                                    </div>
                                                                    <div>
                                                                        <label class="form-label small fw-bold text-dark">Reassign Delivery Partner (Optional)</label>
                                                                        <select name="rider_id" class="form-select form-select-sm">
                                                                            <option value="">-- Keep Current Rider ({{ $fulf->rider?->name ?? $fulf->rider_name }}) --</option>
                                                                            @foreach($deliveryPartners as $dp)
                                                                                <option value="{{ $dp->id }}">🛵 {{ $dp->name }} ({{ $dp->mobile_number }})</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <!-- Resolution Notes -->
                                                                <div class="mb-2">
                                                                    <label class="form-label fw-bold text-dark small">Resolution / Communication Notes</label>
                                                                    <input type="text" name="resolution_notes" class="form-control form-control-sm" placeholder="e.g. Spoke with customer, opening door now">
                                                                </div>
                                                            </div>

                                                            <div class="modal-footer bg-light py-2.5 px-4 border-top">
                                                                <button type="button" class="btn btn-light border bg-white fw-bold" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-primary fw-bold px-3">
                                                                    <i class="bi bi-check2-circle me-1"></i> Apply Resolution
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-success mb-2" style="width: 48px; height: 48px; background: #ecfdf5; font-size: 1.4rem;">
                                            <i class="bi bi-check-circle-fill"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1">0 Pending Exceptions</h6>
                                        <p class="text-secondary small mb-0">All active deliveries are proceeding normally without reported issues.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($activeExceptions->hasPages())
                <div class="card-footer bg-white py-3 px-4 border-top">
                    {{ $activeExceptions->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Tab 2: Resolved Exceptions History -->
    <div class="tab-pane fade" id="resolved-pane" role="tabpanel">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.92rem;">
                    <i class="bi bi-clock-history text-success me-1.5"></i> Resolved Exceptions Archive
                </h6>
                <span class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-2.5 py-1 small fw-bold">
                    {{ $resolvedCount }} Resolved
                </span>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.84rem;">
                        <thead class="bg-light text-secondary text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                            <tr>
                                <th class="ps-4">Order Ref</th>
                                <th>Customer Details</th>
                                <th>Original Issue</th>
                                <th>Resolution Applied</th>
                                <th>Resolved At</th>
                                <th class="text-end pe-4">Order Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($resolvedExceptions as $fulf)
                                <tr>
                                    <td class="ps-4">
                                        @if($fulf->order)
                                            <a href="{{ route('order-manager.orders.show', $fulf->order) }}" class="fw-bolder text-primary text-decoration-none font-monospace">
                                                #{{ $fulf->order->order_number }}
                                            </a>
                                            <div class="text-secondary small" style="font-size: 0.7rem;">
                                                {{ $fulf->order->shipping_city }} - {{ $fulf->order->shipping_zip }}
                                            </div>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($fulf->order)
                                            <div class="fw-bold text-dark">{{ $fulf->order->shipping_name }}</div>
                                            <div class="text-secondary small">{{ $fulf->order->shipping_phone }}</div>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1 font-monospace" style="font-size: 0.72rem;">
                                            {{ $fulf->delivery_issue }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-success small" style="font-size: 0.8rem;">
                                            <i class="bi bi-check-circle-fill me-1"></i> {{ $fulf->delivery_issue_resolution ?? 'Resolved by Order Manager' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-secondary small">{{ $fulf->delivery_issue_resolved_at?->diffForHumans() }}</span>
                                        <div class="text-muted small font-monospace" style="font-size: 0.68rem;">{{ $fulf->delivery_issue_resolved_at?->format('d M, h:i A') }}</div>
                                    </td>
                                    <td class="text-end pe-4">
                                        @if($fulf->order)
                                            <span class="badge rounded-pill px-2.5 py-1 text-white fw-bold" style="background: {{ $fulf->order->status === 'delivered' ? '#10b981' : ($fulf->order->status === 'returned' || $fulf->order->status === 'cancelled' ? '#64748b' : '#0284c7') }}; font-size: 0.7rem;">
                                                {{ ucfirst($fulf->order->status) }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        No resolved exceptions archive yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($resolvedExceptions->hasPages())
                <div class="card-footer bg-white py-3 px-4 border-top">
                    {{ $resolvedExceptions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleResolveOptions(issueId, action) {
    const reschedBox = document.getElementById('rescheduleOptions' + issueId);
    if (reschedBox) {
        if (action === 'reschedule') {
            reschedBox.classList.remove('d-none');
        } else {
            reschedBox.classList.add('d-none');
        }
    }
}
</script>
@endpush

@endsection
