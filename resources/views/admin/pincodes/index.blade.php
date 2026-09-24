@extends('admin.layouts.app')

@section('header', 'Serviceable Pincodes & Delivery Zones')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Pincodes & Delivery</li>
@endsection

@section('actions')
    <div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem !important;">
        <button type="button" class="btn btn-outline-primary rounded-pill px-3.5 py-1.5 fw-semibold d-inline-flex align-items-center shadow-xs" style="gap: 0.5rem !important;" data-bs-toggle="modal" data-bs-target="#bulkImportModal">
            <i class="bi bi-file-earmark-arrow-up"></i> Bulk Import CSV
        </button>
        <a href="{{ route('admin.pincodes.create') }}" class="btn btn-primary rounded-pill px-3.5 py-1.5 fw-semibold d-inline-flex align-items-center shadow-sm" style="gap: 0.5rem !important;">
            <i class="bi bi-geo-alt-fill"></i> Add Pincode
        </a>
    </div>
@endsection

@section('content')
<!-- KPI Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center mb-2.5" style="gap: 0.75rem !important;">
                <div class="rounded-3 p-2 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                    <i class="bi bi-geo-alt-fill fs-5"></i>
                </div>
                <div class="text-muted small fw-bold text-uppercase text-truncate" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Pincodes</div>
            </div>
            <div class="fs-4 fw-bolder text-dark">{{ $stats['total'] }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center mb-2.5" style="gap: 0.75rem !important;">
                <div class="rounded-3 p-2 bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                </div>
                <div class="text-muted small fw-bold text-uppercase text-truncate" style="font-size: 0.72rem; letter-spacing: 0.5px;">Serviceable</div>
            </div>
            <div class="fs-4 fw-bolder text-success">{{ $stats['serviceable'] }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center mb-2.5" style="gap: 0.75rem !important;">
                <div class="rounded-3 p-2 bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                    <i class="bi bi-cash-stack fs-5"></i>
                </div>
                <div class="text-muted small fw-bold text-uppercase text-truncate" style="font-size: 0.72rem; letter-spacing: 0.5px;">COD Available</div>
            </div>
            <div class="fs-4 fw-bolder text-info">{{ $stats['cod_available'] }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center mb-2.5" style="gap: 0.75rem !important;">
                <div class="rounded-3 p-2 bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                    <i class="bi bi-slash-circle fs-5"></i>
                </div>
                <div class="text-muted small fw-bold text-uppercase text-truncate" style="font-size: 0.72rem; letter-spacing: 0.5px;">Blocked / Inactive</div>
            </div>
            <div class="fs-4 fw-bolder text-danger">{{ $stats['unserviceable'] }}</div>
        </div>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3.5">
        <form action="{{ route('admin.pincodes.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0 text-muted ps-3"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control bg-light border-0 py-2 ps-2" placeholder="Search by Pincode, City, or State..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3 col-6">
                <select name="status" class="form-select bg-light border-0 py-2">
                    <option value="">All Service Status</option>
                    <option value="serviceable" {{ request('status') == 'serviceable' ? 'selected' : '' }}>Serviceable Only</option>
                    <option value="unserviceable" {{ request('status') == 'unserviceable' ? 'selected' : '' }}>Blocked Only</option>
                </select>
            </div>
            <div class="col-md-2 col-6">
                <select name="cod" class="form-select bg-light border-0 py-2">
                    <option value="">All Payment Modes</option>
                    <option value="yes" {{ request('cod') == 'yes' ? 'selected' : '' }}>COD Enabled</option>
                    <option value="no" {{ request('cod') == 'no' ? 'selected' : '' }}>Prepaid Only</option>
                </select>
            </div>
            <div class="col-md-2 d-flex" style="gap: 0.5rem !important;">
                <button type="submit" class="btn btn-primary rounded-3 flex-grow-1 py-2 fw-semibold">Filter</button>
                @if(request()->hasAny(['search', 'status', 'cod']))
                    <a href="{{ route('admin.pincodes.index') }}" class="btn btn-light border rounded-3 py-2 px-3 text-muted" title="Reset Filters"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Pincodes Table Card -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center" style="gap: 0.75rem !important;">
            <div class="rounded-3 d-flex align-items-center justify-content-center text-primary flex-shrink-0" style="width: 32px; height: 32px; background: #ede9fe;">
                <i class="bi bi-geo fs-5"></i>
            </div>
            <h6 class="mb-0 fw-bold text-dark">Serviceable Locations Registry</h6>
        </div>
        <span class="badge bg-light text-dark border px-2.5 py-1.5 rounded-pill font-monospace small">
            Showing {{ $pincodes->count() }} of {{ $pincodes->total() }} Records
        </span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">PIN Code</th>
                        <th>City / Zone</th>
                        <th>State</th>
                        <th>Transit Days</th>
                        <th>COD Fee (₹)</th>
                        <th>Service Status</th>
                        <th class="pe-4 text-end" style="width: 130px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pincodes as $item)
                    <tr>
                        <!-- PIN Code -->
                        <td class="ps-4">
                            <span class="font-monospace fw-bold fs-6 px-2.5 py-1 rounded bg-light border text-dark">
                                {{ $item->pincode }}
                            </span>
                        </td>

                        <!-- City -->
                        <td>
                            <div class="fw-semibold text-dark">{{ $item->city }}</div>
                        </td>

                        <!-- State -->
                        <td>
                            <div class="text-secondary small">{{ $item->state }}</div>
                        </td>

                        <!-- Transit Days -->
                        <td>
                            <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1">
                                <i class="bi bi-clock me-1 text-primary"></i> {{ $item->delivery_days }} Days
                            </span>
                        </td>

                        <!-- COD -->
                        <td>
                            <form action="{{ route('admin.pincodes.toggle-cod', $item) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm p-0 border-0" title="Click to toggle COD">
                                    @if($item->is_cod_available)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1">
                                            <i class="bi bi-check-circle me-1"></i> Available
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2.5 py-1">
                                            <i class="bi bi-x-circle me-1"></i> Prepaid Only
                                        </span>
                                    @endif
                                </button>
                            </form>
                        </td>

                        <!-- COD Fee -->
                        <td>
                            @if($item->cod_fee !== null)
                                <span class="font-monospace fw-bold text-dark px-2 py-0.5 rounded bg-light border">₹{{ number_format($item->cod_fee, 2) }}</span>
                                <span class="badge bg-info bg-opacity-10 text-info border ms-1" style="font-size: 0.65rem;">Custom</span>
                            @else
                                <span class="text-muted small font-monospace">₹{{ number_format(\App\Models\Setting::get('cod_flat_fee', 40.00), 2) }}</span>
                                <span class="text-muted fst-italic" style="font-size: 0.65rem;">(Default)</span>
                            @endif
                        </td>

                        <!-- Status -->
                        <td>
                            <form action="{{ route('admin.pincodes.toggle-serviceable', $item) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm p-0 border-0" title="Click to toggle Serviceability">
                                    @if($item->is_serviceable)
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1">
                                            <i class="bi bi-truck me-1"></i> Active
                                        </span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2.5 py-1">
                                            <i class="bi bi-slash-circle me-1"></i> Blocked
                                        </span>
                                    @endif
                                </button>
                            </form>
                        </td>

                        <!-- Actions -->
                        <td class="pe-4 text-end">
                            <div class="d-flex align-items-center justify-content-end" style="gap: 0.35rem !important;">
                                <a href="{{ route('admin.pincodes.edit', $item) }}" class="btn btn-sm btn-outline-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Edit Pincode">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.pincodes.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete pincode {{ $item->pincode }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Delete Pincode">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-geo-alt text-muted opacity-50 display-6 d-block mb-2"></i>
                            <h6 class="fw-bold text-dark">No Pincodes Found</h6>
                            <p class="small text-muted mb-3">Add a single pincode or bulk import via CSV.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pincodes->hasPages())
        <div class="p-3 border-top d-flex justify-content-center">
            {{ $pincodes->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>

<!-- Bulk Import Modal -->
<div class="modal fade" id="bulkImportModal" tabindex="-1" aria-labelledby="bulkImportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4">
                <h6 class="modal-title fw-bold text-dark" id="bulkImportModalLabel">
                    <i class="bi bi-file-earmark-arrow-up text-primary me-2"></i>Bulk Import Pincodes (CSV)
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.pincodes.bulk-import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Upload CSV File</label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv, .txt" required>
                        <div class="form-text small text-muted mt-2">
                            CSV Format: <code>pincode, city, state, delivery_days, is_cod_available(1/0), delivery_charge</code>
                        </div>
                    </div>

                    <div class="alert alert-light border rounded-3 p-3 small text-secondary">
                        <strong class="text-dark d-block mb-1">CSV Template Example:</strong>
                        500081, Hyderabad (Hitec City), Telangana, 2, 1, 0.00<br>
                        560001, Bengaluru, Karnataka, 2, 1, 0.00
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 px-4" style="gap: 0.5rem !important;">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                        <i class="bi bi-upload me-1"></i> Upload & Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
