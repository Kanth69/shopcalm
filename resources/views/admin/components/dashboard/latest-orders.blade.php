<div class="card h-100 border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0">Latest Orders</h5>
    </div>
    <div class="card-body p-0">
        @if($latestOrders->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <tbody>
                        @foreach($latestOrders as $order)
                            <tr>
                                <td class="ps-3">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="fw-bold text-dark text-decoration-none">{{ $order->order_number }}</a>
                                    <div class="small text-muted">{{ $order->user->name }}</div>
                                </td>
                                <td class="text-end">
                                    <div class="fw-bold">₹{{ number_format($order->total_amount, 2) }}</div>
                                    <div class="small text-muted">{{ $order->created_at->diffForHumans() }}</div>
                                </td>
                                <td class="text-end pe-3">
                                    @include('customer.components.order-status-badge', ['status' => $order->status])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-4 text-center text-muted">No recent orders found.</div>
        @endif
    </div>
</div>
