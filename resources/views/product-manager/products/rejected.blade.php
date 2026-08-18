@extends('product-manager.layouts.app')

@section('title', 'Rejected Products')
@section('header', 'Rejected Submissions')
@section('subheader', 'Products returned by Admin with feedback')

@section('content')
<div class="card bg-white border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <div>
            <h6 class="mb-0 fw-bold text-danger">Action Needed ({{ $products->total() }})</h6>
            <small class="text-muted" style="font-size: 0.75rem;">Modify product specifications based on Admin feedback and click Resubmit.</small>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="font-size: 0.72rem;">Product</th>
                        <th style="font-size: 0.72rem;">Admin Feedback</th>
                        <th style="font-size: 0.72rem;">Price</th>
                        <th style="font-size: 0.72rem;">Stock</th>
                        <th class="pe-4 text-end" style="font-size: 0.72rem;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $prod)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                @if($prod->main_image)
                                    <img src="{{ asset('storage/' . $prod->main_image) }}" class="rounded-3 border" style="width: 42px; height: 42px; object-fit: cover;">
                                @else
                                    <div class="rounded-3 bg-light d-flex align-items-center justify-content-center text-muted border" style="width: 42px; height: 42px;">
                                        <i class="bi bi-image"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="fw-bold text-dark">{{ $prod->name }}</div>
                                    <div class="text-muted small" style="font-size: 0.72rem;">SKU: {{ $prod->sku }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="max-width: 340px;">
                            <div class="p-2.5 bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded-3 text-dark small" style="font-size: 0.78rem;">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="fw-bold text-danger"><i class="bi bi-chat-left-quote me-1"></i>Admin Feedback</span>
                                    @if($prod->latestRejectionReason)
                                        <span class="text-muted" style="font-size: 0.68rem;">{{ $prod->latestRejectionReason->created_at->diffForHumans() }}</span>
                                    @endif
                                </div>
                                <div>{{ $prod->active_rejection_reason ?? ($prod->latestRejectionReason->reason ?? $prod->rejection_reason ?? 'Review specifications.') }}</div>
                                @if($prod->latestRejectionReason && $prod->latestRejectionReason->rejector)
                                    <div class="mt-1 text-muted" style="font-size: 0.68rem;">
                                        By: {{ $prod->latestRejectionReason->rejector->name }}
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">₹{{ number_format($prod->price, 2) }}</div>
                        </td>
                        <td>
                            <span class="small fw-semibold text-dark">{{ $prod->stock }} units</span>
                        </td>
                        <td class="pe-4 text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('product-manager.products.edit', $prod) }}" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold" style="font-size: 0.78rem;">
                                    Edit
                                </a>
                                <form action="{{ route('product-manager.products.resubmit', $prod) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-warning text-dark rounded-pill px-3 fw-bold shadow-xs" style="font-size: 0.78rem;">
                                        Resubmit
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-check-circle text-success fs-2 mb-2 d-block opacity-50"></i>
                            <div class="fw-bold text-dark mb-1">No Rejected Items</div>
                            <small class="text-muted">All catalog items have clean approval records.</small>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="px-4 py-3 border-top bg-white">
                {{ $products->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
