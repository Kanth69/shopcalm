<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Recent Orders</h5>
    </div>
    <div class="card-body p-0">
        @if($recentOrders->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $order)
                            <tr>
                                <td>{{ $order->order_number }}</td>
                                <td>{{ $order->created_at->format('d M, Y') }}</td>
                                <td><span class="badge bg-info">{{ ucfirst($order->status) }}</span></td>
                                <td>₹{{ number_format($order->total_amount, 2) }}</td>
                                <td><a href="{{ route('account.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">View</a></td>
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
