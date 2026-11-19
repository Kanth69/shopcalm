@extends('marketing.layouts.app')

@section('title', 'Automations Studio')
@section('header', 'Automations Studio')
@section('subheader', 'Configure real-time event triggers, lifecycle alerts, and automated wallet rewards.')

@section('actions')
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fw-medium d-inline-flex align-items-center gap-1.5" style="font-size: 0.78rem;">
            <i class="bi bi-shield-check"></i> FCM v1 Active
        </span>
        <a href="{{ route('marketing.notifications.index') }}" class="btn btn-outline-secondary btn-sm px-3.5 py-1.5 rounded-pill fw-semibold d-inline-flex align-items-center gap-1.5" style="font-size: 0.8rem;">
            <i class="bi bi-broadcast"></i> Manual Broadcast
        </a>
    </div>
@endsection

@push('styles')
<style>
    /* Metric Scorecards */
    .kpi-stat-card {
        background: var(--surface, #ffffff);
        border: 1px solid var(--border, #e2e8f0);
        border-radius: var(--radius-lg, 16px);
        padding: 22px 24px;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .kpi-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.07);
    }

    /* Automation Table */
    .table-automations tbody tr {
        transition: background-color 0.15s ease;
    }
    .table-automations tbody tr:hover {
        background-color: #f8fafc;
    }
    .table-automations td {
        vertical-align: middle;
        padding-top: 1.15rem;
        padding-bottom: 1.15rem;
    }

    /* Filter Pills */
    .filter-pill {
        border: 1px solid transparent;
        padding: 0.45rem 1rem;
        border-radius: 9999px;
        font-size: 0.82rem;
        font-weight: 600;
        color: #64748b;
        background: transparent;
        transition: all 0.18s ease;
        text-decoration: none;
    }
    .filter-pill:hover {
        color: #0f172a;
        background: #f1f5f9;
    }
    .filter-pill.active {
        color: #ffffff !important;
        background: #0f172a !important;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.2);
    }

    /* Modal Styling */
    #editAutomationModal .modal-dialog {
        max-height: calc(100vh - 3.5rem);
        margin: 1.75rem auto;
    }
    #editAutomationModal .modal-content {
        max-height: calc(100vh - 3.5rem);
        display: flex !important;
        flex-direction: column !important;
        border-radius: 16px !important;
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25) !important;
        background: #ffffff;
    }
    #editAutomationModal .modal-header {
        flex-shrink: 0;
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
        padding: 1.25rem 1.75rem;
    }
    #editAutomationModal .modal-body {
        flex: 1 1 auto;
        overflow-y: auto !important;
        overscroll-behavior: contain;
        padding: 1.75rem;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }
    #editAutomationModal .modal-body::-webkit-scrollbar {
        width: 6px;
    }
    #editAutomationModal .modal-body::-webkit-scrollbar-track {
        background: transparent;
    }
    #editAutomationModal .modal-body::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 6px;
    }
    #editAutomationModal .modal-footer {
        flex-shrink: 0;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        padding: 1rem 1.75rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center gap-2 py-3 px-4" role="alert">
            <i class="bi bi-check-circle-fill fs-5 text-success"></i>
            <div class="fw-medium">{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- ── 1. EXECUTIVE METRICS SCORECARDS ── -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-stat-card h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Active Playbooks</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background: #ecfdf5; color: #059669;">
                        <i class="bi bi-toggle-on fs-5"></i>
                    </div>
                </div>
                <div class="fs-3 fw-bold text-dark mb-1 d-flex align-items-baseline gap-2" style="letter-spacing: -0.5px;">
                    <span>{{ $activeCount }}</span>
                    <span class="text-muted fs-6 fw-normal">/ {{ $automations->count() }} enabled</span>
                </div>
                <div class="text-muted small" style="font-size: 0.78rem;">Zero hardcoded logic</div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-stat-card h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Auto Dispatched</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background: #eff6ff; color: #2563eb;">
                        <i class="bi bi-send-check-fill fs-5"></i>
                    </div>
                </div>
                <div class="fs-3 fw-bold text-dark mb-1" style="letter-spacing: -0.5px;">{{ number_format($totalSent) }}</div>
                <div class="text-muted small" style="font-size: 0.78rem;">Automated push alerts</div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-stat-card h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Customer Retention</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background: #fdf2f8; color: #db2777;">
                        <i class="bi bi-stars fs-5"></i>
                    </div>
                </div>
                <div class="fs-3 fw-bold text-dark mb-1 d-flex align-items-baseline gap-2" style="letter-spacing: -0.5px;">
                    <span>{{ $growthActiveCount }}</span>
                    <span class="text-muted fs-6 fw-normal">/ {{ $growthAutomations->count() }} active</span>
                </div>
                <div class="text-muted small" style="font-size: 0.78rem;">Cart &amp; birthday alerts</div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-stat-card h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Delivery Channel</span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background: #fffbeb; color: #d97706;">
                        <i class="bi bi-bell-fill fs-5"></i>
                    </div>
                </div>
                <div class="fs-4 fw-bold text-dark mb-1 d-flex align-items-center gap-1.5">
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill" style="font-size: 0.76rem;">FCM HTTP v1</span>
                </div>
                <div class="text-muted small" style="font-size: 0.78rem;">Direct phone lock-screen</div>
            </div>
        </div>
    </div>

    <!-- ── 2. SEGMENTED FILTER TABS & SEARCH ── -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div class="d-flex align-items-center gap-2 p-1 bg-white border rounded-pill shadow-xs">
            <button type="button" class="filter-pill active" onclick="filterCategory('all', this)">
                All Playbooks <span class="badge bg-light text-dark ms-1 rounded-pill">{{ $automations->count() }}</span>
            </button>
            <button type="button" class="filter-pill" onclick="filterCategory('order', this)">
                Order Lifecycle <span class="badge bg-light text-dark ms-1 rounded-pill">{{ $orderAutomations->count() }}</span>
            </button>
            <button type="button" class="filter-pill" onclick="filterCategory('growth', this)">
                Customer Retention <span class="badge bg-light text-dark ms-1 rounded-pill">{{ $growthAutomations->count() }}</span>
            </button>
        </div>

        <div class="text-muted small">
            Click <strong class="text-dark">Configure</strong> on any playbook to edit copy, timing, or wallet rewards.
        </div>
    </div>

    <!-- ── 3. ENTERPRISE PLAYBOOK DIRECTORY TABLE ── -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-5">
        <div class="table-responsive">
            <table class="table table-automations align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4 py-3" style="font-size: 0.72rem; letter-spacing: 0.5px;">Playbook &amp; Event Trigger</th>
                        <th class="py-3" style="font-size: 0.72rem; letter-spacing: 0.5px;">Trigger Timing</th>
                        <th class="py-3 text-center" style="font-size: 0.72rem; letter-spacing: 0.5px;">Dispatched</th>
                        <th class="py-3 text-center" style="font-size: 0.72rem; letter-spacing: 0.5px;">Status</th>
                        <th class="pe-4 py-3 text-end" style="font-size: 0.72rem; letter-spacing: 0.5px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $iconMap = [
                            'order_placed'           => ['icon' => 'bi-bag-check-fill', 'bg' => '#ecfdf5', 'color' => '#059669'],
                            'order_shipped'          => ['icon' => 'bi-truck',          'bg' => '#eff6ff', 'color' => '#2563eb'],
                            'order_out_for_delivery' => ['icon' => 'bi-geo-alt-fill',   'bg' => '#fffbeb', 'color' => '#d97706'],
                            'order_delivered'        => ['icon' => 'bi-patch-check-fill', 'bg' => '#ecfdf5', 'color' => '#047857'],
                            'birthday_reward'        => ['icon' => 'bi-cake2-fill',     'bg' => '#fdf2f8', 'color' => '#db2777'],
                            'abandoned_cart'         => ['icon' => 'bi-cart-x-fill',    'bg' => '#f5f3ff', 'color' => '#7c3aed'],
                            'customer_winback'       => ['icon' => 'bi-arrow-repeat',   'bg' => '#ecfeff', 'color' => '#0891b2'],
                        ];
                    @endphp

                    @foreach($automations as $auto)
                        @php
                            $ic = $iconMap[$auto->key] ?? ['icon' => 'bi-lightning-fill', 'bg' => '#f1f5f9', 'color' => '#64748b'];
                            $titles = $auto->title_template ?? [];
                            $bodies = $auto->body_template ?? [];
                        @endphp
                        <tr class="automation-row" data-category="{{ $auto->category }}">
                            <!-- Col 1: Playbook & Event Trigger -->
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: {{ $ic['bg'] }}; color: {{ $ic['color'] }}; font-size: 1.15rem;">
                                        <i class="bi {{ $ic['icon'] }}"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 0.92rem; line-height: 1.25;">{{ $auto->name }}</div>
                                        <div class="d-flex align-items-center gap-1.5 mt-1">
                                            <code class="px-2 py-0.5 rounded text-muted bg-light border" style="font-size: 0.72rem;">{{ $auto->trigger_event }}</code>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Col 2: Timing -->
                            <td>
                                @if($auto->delay_minutes == 0)
                                    <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill fw-medium" style="font-size: 0.74rem;">
                                        <i class="bi bi-lightning-fill text-warning me-1"></i> Instant
                                    </span>
                                @else
                                    <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill fw-medium" style="font-size: 0.74rem;">
                                        <i class="bi bi-clock text-muted me-1"></i> {{ $auto->delay_minutes }} mins
                                    </span>
                                @endif
                            </td>

                            <!-- Col 3: Dispatched -->
                            <td class="text-center">
                                <span class="badge bg-light text-dark border font-monospace px-2.5 py-1 rounded-pill" style="font-size: 0.76rem;">
                                    {{ number_format($auto->total_sent) }}
                                </span>
                            </td>

                            <!-- Col 6: Status Toggle -->
                            <td class="text-center">
                                <div class="d-inline-flex align-items-center gap-2">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" {{ $auto->is_active ? 'checked' : '' }} onchange="togglePlaybook({{ $auto->id }}, this)">
                                    </div>
                                    <span id="statusBadge_{{ $auto->id }}" class="badge {{ $auto->is_active ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' }} px-2 py-0.5 rounded-pill" style="font-size: 0.72rem;">
                                        {{ $auto->is_active ? 'Active' : 'Paused' }}
                                    </span>
                                </div>
                            </td>

                            <!-- Col 7: Action -->
                            <td class="pe-4 text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1.5" onclick='openEditModal(@json($auto))' style="font-size: 0.78rem;">
                                    <i class="bi bi-sliders"></i>
                                    <span>Configure</span>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ── 4. MODERN PLAYBOOK CONFIGURATION MODAL ── -->
<div class="modal fade" id="editAutomationModal" tabindex="-1" aria-labelledby="editAutomationModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form id="editAutomationForm" class="modal-content border-0 shadow-xl rounded-4 overflow-hidden" method="POST" action="">
            @csrf
            @method('PUT')

            <!-- Modal Header -->
            <div class="modal-header border-bottom px-4 py-3.5 bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                        <i class="bi bi-sliders"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="modalAutomationName">Configure Automation</h5>
                        <code class="text-muted small" id="modalAutomationKey" style="font-size: 0.75rem;">trigger.event</code>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body (Scrollable & Contained) -->
            <div class="modal-body p-4">
                
                <!-- Status & Timing Row -->
                <div class="p-3.5 bg-light rounded-3 border mb-4">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-6 d-flex align-items-center justify-content-between">
                            <div>
                                <label class="form-label fw-bold text-dark mb-0" style="font-size: 0.88rem;">Playbook Status</label>
                                <div class="text-muted small" style="font-size: 0.76rem;">Toggle trigger active or paused</div>
                            </div>
                            <div class="form-check form-switch fs-4 mb-0">
                                <input class="form-check-input" type="checkbox" role="switch" id="modalIsActive" name="is_active" value="1">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">Trigger Delay</label>
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control" id="modalDelayMinutes" name="delay_minutes" min="0" max="10080" placeholder="0">
                                <span class="input-group-text bg-white text-muted">minutes (0 = Instant)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Placeholders Legend -->
                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-1.5">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-0" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                            Click to insert dynamic placeholder:
                        </label>
                    </div>
                    <div class="d-flex flex-wrap gap-1.5" id="dynamicTagsContainer">
                        <!-- Populated via JS -->
                    </div>
                </div>

                <!-- Single Unified Message Editor (No Multi-tabs, Clean & Direct) -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark mb-1">Notification Title</label>
                    <input type="text" class="form-control" id="modalTitle" name="title" placeholder="Type notification headline..." required onkeyup="updateLivePreview()">
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-bold text-dark mb-1">Notification Message</label>
                    <textarea class="form-control" id="modalBody" name="body" rows="3" placeholder="Type your custom notification message..." required onkeyup="updateLivePreview()"></textarea>
                </div>

                <!-- Live Mobile Lockscreen Preview Box -->
                <div class="mt-4 p-3.5 bg-dark rounded-4 text-white shadow-sm">
                    <div class="d-flex align-items-center justify-content-between mb-2.5 pb-2 border-bottom border-secondary">
                        <div class="small text-uppercase text-secondary fw-semibold d-flex align-items-center gap-1.5" style="font-size: 0.68rem; letter-spacing: 0.08em;">
                            <i class="bi bi-phone"></i> Live Device Lock-Screen Preview
                        </div>
                    </div>

                    <div class="p-3 rounded-3" style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(8px);">
                        <div class="d-flex align-items-center gap-2 mb-1.5">
                            <div style="width: 22px; height: 22px; border-radius: 6px; background: linear-gradient(135deg, #ec4899, #db2777); display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 800; color: #fff;">
                                S
                            </div>
                            <span style="font-size: 0.78rem; font-weight: 700; color: #fff; letter-spacing: 0.02em;">ShopCalm</span>
                            <span style="font-size: 0.68rem; color: rgba(255, 255, 255, 0.6); margin-left: auto;">now</span>
                        </div>
                        <div id="previewTitle" style="font-size: 0.85rem; font-weight: 700; color: #fff; line-height: 1.3; margin-bottom: 3px;">
                            Notification Title
                        </div>
                        <div id="previewBody" style="font-size: 0.78rem; color: rgba(255, 255, 255, 0.9); line-height: 1.4;">
                            Message body preview will appear here...
                        </div>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-top px-4 py-3 bg-light">
                <button type="button" class="btn btn-outline-secondary px-3.5 py-2 rounded-pill fw-medium" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill fw-semibold shadow-sm">
                    <i class="bi bi-check2-circle me-1"></i> Save Configuration
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentEditingData = null;
let activeTextareaId = 'modalBody';

// Segment filter (All / Order / Growth)
function filterCategory(cat, btn) {
    document.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    document.querySelectorAll('.automation-row').forEach(row => {
        if (cat === 'all' || row.getAttribute('data-category') === cat) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Track active textarea focus for tag insertion
document.addEventListener('DOMContentLoaded', function() {
    ['modalTitle', 'modalBody'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('focus', () => { activeTextareaId = id; });
        }
    });
});

function openEditModal(automation) {
    currentEditingData = automation;

    document.getElementById('editAutomationForm').action = `/marketing/automations/${automation.id}`;
    document.getElementById('modalAutomationName').innerText = automation.name;
    document.getElementById('modalAutomationKey').innerText = `${automation.trigger_event}`;

    document.getElementById('modalIsActive').checked = !!automation.is_active;
    document.getElementById('modalDelayMinutes').value = automation.delay_minutes || 0;

    // Single unified copy (no multi-tab clutter)
    const titles = automation.title_template || {};
    const bodies = automation.body_template || {};

    const currentTitle = titles.default || titles.en || (typeof titles === 'string' ? titles : Object.values(titles)[0] || '');
    const currentBody  = bodies.default || bodies.en || (typeof bodies === 'string' ? bodies : Object.values(bodies)[0] || '');

    document.getElementById('modalTitle').value = currentTitle;
    document.getElementById('modalBody').value = currentBody;

    // Render dynamic placeholder tags
    const container = document.getElementById('dynamicTagsContainer');
    container.innerHTML = '';
    const tags = automation.supported_tags || ['{customer_name}'];
    tags.forEach(tag => {
        const chip = document.createElement('button');
        chip.type = 'button';
        chip.className = 'btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1 text-primary fw-medium';
        chip.style.fontSize = '0.74rem';
        chip.innerHTML = `<i class="bi bi-plus"></i> ${tag}`;
        chip.onclick = () => insertTag(tag);
        container.appendChild(chip);
    });

    updateLivePreview();

    const modalEl = document.getElementById('editAutomationModal');
    const modalBody = modalEl.querySelector('.modal-body');
    if (modalBody) modalBody.scrollTop = 0;

    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
}

function insertTag(tag) {
    const el = document.getElementById(activeTextareaId);
    if (!el) return;

    const start = el.selectionStart;
    const end = el.selectionEnd;
    const text = el.value;
    el.value = text.substring(0, start) + tag + text.substring(end);
    el.focus();
    el.selectionStart = el.selectionEnd = start + tag.length;
    updateLivePreview();
}

function updateLivePreview() {
    let title = document.getElementById('modalTitle').value;
    let body = document.getElementById('modalBody').value;

    const sampleReplacements = {
        '{customer_name}': 'Srikant',
        '{order_id}': 'SC-84920',
        '{order_total}': '1,499.00',
    };

    for (const [tag, val] of Object.entries(sampleReplacements)) {
        title = title.replaceAll(tag, val);
        body = body.replaceAll(tag, val);
    }

    document.getElementById('previewTitle').innerText = title || 'Notification Title';
    document.getElementById('previewBody').innerText = body || 'Message preview...';
}

function togglePlaybook(id, checkbox) {
    checkbox.disabled = true;
    fetch(`/marketing/automations/${id}/toggle`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        }
    })
    .then(res => res.json())
    .then(data => {
        checkbox.disabled = false;
        if (data.success) {
            const badge = document.getElementById(`statusBadge_${id}`);
            if (data.is_active) {
                badge.className = 'badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 rounded-pill';
                badge.innerText = 'Active';
            } else {
                badge.className = 'badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-0.5 rounded-pill';
                badge.innerText = 'Paused';
            }
        }
    })
    .catch(err => {
        checkbox.disabled = false;
        checkbox.checked = !checkbox.checked;
        alert('Failed to update status. Please try again.');
    });
}
</script>
@endsection
