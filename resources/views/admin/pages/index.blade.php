@extends('admin.layouts.app')

@section('header', 'Content Pages Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Static & Legal Pages</li>
@endsection

@section('content')
<!-- KPI Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                    <i class="bi bi-file-earmark-text-fill fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Pages</div>
                    <div class="fs-4 fw-bold text-dark">{{ $pages->count() }} Content Pages</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                    <i class="bi bi-broadcast fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Live & Published</div>
                    <div class="fs-4 fw-bold text-dark">{{ $pages->where('is_active', true)->count() }} Pages</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                    <i class="bi bi-file-earmark-lock-fill fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Draft Mode</div>
                    <div class="fs-4 fw-bold text-dark">{{ $pages->where('is_active', false)->count() }} Pages</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pages Table Card -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-journal-text text-primary fs-5"></i>
            <h6 class="mb-0 fw-bold text-dark">Storefront Pages & Legal Policies</h6>
        </div>
        <span class="badge bg-light text-dark border px-2.5 py-1.5 rounded-pill font-monospace small">
            {{ $pages->count() }} Managed Documents
        </span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width: 320px;">Page Title</th>
                        <th>Storefront URL</th>
                        <th class="text-center">Live Status</th>
                        <th>Last Modified</th>
                        <th class="pe-4 text-end" style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pages as $page)
                    <tr>
                        <!-- Title & Icon -->
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 p-2.5 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                                    @if($page->slug === 'about-us')
                                        <i class="bi bi-building fs-5"></i>
                                    @elseif(str_contains($page->slug, 'policy') || str_contains($page->slug, 'terms'))
                                        <i class="bi bi-shield-check fs-5"></i>
                                    @elseif($page->slug === 'faq')
                                        <i class="bi bi-question-circle fs-5"></i>
                                    @else
                                        <i class="bi bi-file-earmark-richtext fs-5"></i>
                                    @endif
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $page->title }}</div>
                                    @if($page->meta_title)
                                        <div class="text-muted small text-truncate" style="max-width: 220px; font-size: 0.72rem;">{{ $page->meta_title }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <!-- URL Link -->
                        <td>
                            <a href="{{ url('/' . $page->slug) }}" target="_blank" class="badge bg-light text-primary border text-decoration-none px-2.5 py-1.5 font-monospace small">
                                /{{ $page->slug }} <i class="bi bi-box-arrow-up-right ms-1 text-secondary" style="font-size: 0.7rem;"></i>
                            </a>
                        </td>

                        <!-- Status Badge -->
                        <td class="text-center">
                            @if($page->is_active)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1">
                                    <i class="bi bi-check-circle-fill me-1"></i> Published
                                </span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2.5 py-1">
                                    <i class="bi bi-eye-slash me-1"></i> Draft (Hidden)
                                </span>
                            @endif
                        </td>

                        <!-- Last Updated -->
                        <td class="small text-muted">
                            <i class="bi bi-clock-history me-1 text-secondary"></i>{{ $page->updated_at->diffForHumans() }}
                        </td>

                        <!-- Actions -->
                        <td class="pe-4 text-end">
                            <div class="d-flex justify-content-end align-items-center gap-2">
                                <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center" title="Edit Page Content">
                                    <i class="bi bi-pencil-square me-1.5"></i> Edit
                                </a>
                                <a href="{{ url('/' . $page->slug) }}" target="_blank" class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 text-dark d-inline-flex align-items-center shadow-xs" title="Open live storefront page in new tab">
                                    <i class="bi bi-box-arrow-up-right me-1 text-primary"></i> View
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-file-earmark-x text-muted opacity-50 display-6 d-block mb-2"></i>
                            <h6 class="fw-bold text-dark">No Static Pages Found</h6>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
