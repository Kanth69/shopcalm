<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history text-primary me-2"></i>Recent Orders</h6>
        <a href="{{ route('account.orders.index') }}" class="small fw-semibold text-primary text-decoration-none">View All Orders <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="card-body p-0">
        @if($recentOrders->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background:#f8fafc; border-bottom: 1px solid #e2e8f0;">
                        <tr>
                            <th class="ps-4">Order #</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $order)
                            @php
                                $statusStyle = match(strtolower($order->status)) {
                                    'delivered' => 'background:#d1fae5; color:#065f46; border:1px solid #a7f3d0;',
                                    'shipped'   => 'background:#f3e8ff; color:#6b21a8; border:1px solid #d8b4fe;',
                                    'processing'=> 'background:#dbeafe; color:#1e40af; border:1px solid #93c5fd;',
                                    'cancelled' => 'background:#fee2e2; color:#991b1b; border:1px solid #fca5a5;',
                                    default     => 'background:#fef3c7; color:#92400e; border:1px solid #fde68a;'
                                };
                            @endphp
                            <tr>
                                <td class="ps-4 fw-bold text-dark" style="font-size:0.85rem;">#{{ $order->order_number }}</td>
                                <td class="small text-muted" style="font-size:0.8rem;">{{ $order->created_at->format('d M, Y') }}</td>
                                <td>
                                    <span class="badge rounded-pill px-3 py-1 fw-bold" style="{{ $statusStyle }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="fw-bold text-dark" style="font-size:0.875rem;">₹{{ number_format($order->total_amount, 2) }}</td>
                                <td class="pe-4 text-end">
                                    <a href="{{ route('account.orders.show', $order) }}" class="btn btn-sm btn-light text-primary border rounded-pill px-3 py-1" style="font-size:0.75rem;">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            @include('customer.account.components.empty-state', [
                'icon' => 'bi-box-seam',
                'title' => 'No Orders Yet',
                'message' => 'You haven\'t placed any orders yet. Let\'s change that!',
                'button_text' => 'Start Shopping',
                'button_url' => route('shop')
            ])
        @endif
    </div>
</div>
