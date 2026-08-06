@extends('admin.layouts.app')

@section('header', 'Coupon Details: ' . $coupon->code)

@section('actions')
    <div class="btn-group">
        <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <a href="{{ route('admin.coupons.edit', $coupon) }}" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Edit
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0">Coupon Overview</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <h2 class="display-6 fw-bold text-primary">{{ $coupon->code }}</h2>
                    <p class="text-muted">{{ $coupon->name }}</p>
                    @if($coupon->status && (!$coupon->expiry_date || !$coupon->expiry_date->isPast()))
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive/Expired</span>
                    @endif
                </div>

                <table class="table table-sm table-borderless">
                    <tr>
                        <th class="ps-0 text-muted">Discount Type</th>
                        <td class="text-end">{{ $coupon->discount_type->value }}</td>
                    </tr>
                    <tr>
                        <th class="ps-0 text-muted">Discount Value</th>
                        <td class="text-end fw-bold">
                            @if($coupon->discount_type->value === 'FLAT')
                                ₹{{ number_format($coupon->discount_value, 2) }}
                            @else
                                {{ $coupon->discount_value }}%
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th class="ps-0 text-muted">Max Discount</th>
                        <td class="text-end">{{ $coupon->maximum_discount ? '₹' . number_format($coupon->maximum_discount, 2) : 'No Limit' }}</td>
                    </tr>
                    <tr>
                        <th class="ps-0 text-muted">Min Order Amount</th>
                        <td class="text-end">₹{{ number_format($coupon->minimum_order_amount, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0">Usage & Limits</h5>
            </div>
            <div class="card-body">
                <div class="row text-center mb-4">
                    <div class="col-6 border-end">
                        <div class="h3 fw-bold text-dark mb-0">{{ $coupon->used_count }}</div>
                        <div class="small text-muted">Total Uses</div>
                    </div>
                    <div class="col-6">
                        <div class="h3 fw-bold text-success mb-0">₹{{ number_format($coupon->usages->sum('discount_applied'), 2) }}</div>
                        <div class="small text-muted">Total Discount Given</div>
                    </div>
                </div>

                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <th class="ps-0 text-muted">Total Limit</th>
                        <td class="text-end">{{ $coupon->usage_limit ?: 'Unlimited' }}</td>
                    </tr>
                    <tr>
                        <th class="ps-0 text-muted">Per User Limit</th>
                        <td class="text-end">{{ $coupon->per_user_limit }}</td>
                    </tr>
                    <tr>
                        <th class="ps-0 text-muted">Valid From</th>
                        <td class="text-end">{{ $coupon->start_date ? $coupon->start_date->format('d M, Y H:i') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th class="ps-0 text-muted">Valid Until</th>
                        <td class="text-end">{{ $coupon->expiry_date ? $coupon->expiry_date->format('d M, Y H:i') : 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Recent Usages</h5>
                <span class="badge bg-primary rounded-pill">{{ $usages->total() }} total</span>
            </div>
            <div class="card-body p-0">
                @if($usages->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Customer</th>
                                    <th>Order #</th>
                                    <th class="text-end">Discount Applied</th>
                                    <th class="text-end pe-3">Date Used</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($usages as $usage)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold">{{ $usage->user->name }}</div>
                                        <div class="small text-muted">{{ $usage->user->email }}</div>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $usage->order) }}" class="text-decoration-none">
                                            {{ $usage->order->order_number }}
                                        </a>
                                    </td>
                                    <td class="text-end text-success fw-bold">-₹{{ number_format($usage->discount_applied, 2) }}</td>
                                    <td class="text-end pe-3 text-muted small">{{ $usage->created_at->format('d M Y, H:i') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-clock-history fs-1 text-muted mb-3 d-block"></i>
                        <h5 class="text-muted">No usages recorded yet.</h5>
                    </div>
                @endif
            </div>
            @if($usages->hasPages())
                <div class="card-footer bg-white py-3">
                    {{ $usages->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
