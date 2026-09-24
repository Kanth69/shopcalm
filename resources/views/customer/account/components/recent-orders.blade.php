<div class="card border-0 shadow-xs rounded-4 overflow-hidden mb-3.5" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
    <div class="card-header bg-white py-3 px-3 px-md-4 border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-3 d-flex align-items-center justify-content-center text-primary" style="width: 32px; height: 32px; background: #ede9fe;">
                <i class="bi bi-clock-history"></i>
            </div>
            <h6 class="mb-0 fw-bolder text-dark" style="font-size: 0.95rem;">Recent Orders</h6>
        </div>
        <a href="{{ route('account.orders.index') }}" class="small fw-semibold text-primary text-decoration-none" style="font-size: 0.8rem;">
            View All <i class="bi bi-arrow-right ms-0.5"></i>
        </a>
    </div>
    
    <div class="card-body p-0">
        @if($recentOrders->isNotEmpty())
            {{-- 1. Desktop Table View --}}
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                        <tr>
                            <th class="ps-4 text-uppercase text-muted fw-bold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Order #</th>
                            <th class="text-uppercase text-muted fw-bold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Date</th>
                            <th class="text-uppercase text-muted fw-bold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Status</th>
                            <th class="text-uppercase text-muted fw-bold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Total</th>
                            <th class="pe-4 text-end text-uppercase text-muted fw-bold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $order)
                            @php
                                $s = strtolower(str_replace([' ', '-'], '_', $order->status));
                                $statusStyle = match($s) {
                                    'delivered'        => 'background:#d1fae5; color:#065f46; border:1px solid #a7f3d0;',
                                    'out_for_delivery' => 'background:#fef3c7; color:#b45309; border:1px solid #fcd34d;',
                                    'shipped'          => 'background:#f3e8ff; color:#6b21a8; border:1px solid #d8b4fe;',
                                    'packed', 'confirmed', 'processing' => 'background:#dbeafe; color:#1e40af; border:1px solid #93c5fd;',
                                    'cancelled'        => 'background:#fee2e2; color:#991b1b; border:1px solid #fca5a5;',
                                    default            => 'background:#fef3c7; color:#92400e; border:1px solid #fde68a;'
                                };
                                $statusLabel = ucwords(str_replace('_', ' ', $order->status));
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold text-dark" style="font-size: 0.88rem;">#{{ $order->order_number }}</span>
                                    <div class="text-muted" style="font-size: 0.74rem;">{{ $order->items->count() }} item{{ $order->items->count() !== 1 ? 's' : '' }}</div>
                                </td>
                                <td class="small text-secondary" style="font-size: 0.82rem;">
                                    {{ $order->created_at ? $order->created_at->format('d M, Y') : '—' }}
                                </td>
                                <td>
                                    <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="{{ $statusStyle }} font-size: 0.72rem;">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="fw-bold text-dark" style="font-size: 0.9rem;">
                                    ₹{{ number_format($order->total_amount, 2) }}
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="{{ route('account.orders.show', $order) }}" 
                                       class="btn btn-sm btn-light border rounded-pill px-3 py-1 fw-semibold text-primary" 
                                       style="font-size: 0.78rem; background: #ffffff; transition: all 0.2s;">
                                        View <i class="bi bi-chevron-right ms-0.5" style="font-size: 0.7rem;"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- 2. Mobile Card List View --}}
            <div class="d-block d-md-none p-2.5">
                <div class="d-flex flex-column gap-2.5">
                    @foreach($recentOrders as $order)
                        @php
                            $s = strtolower(str_replace([' ', '-'], '_', $order->status));
                            $statusStyle = match($s) {
                                'delivered'        => 'background:#d1fae5; color:#065f46; border:1px solid #a7f3d0;',
                                'out_for_delivery' => 'background:#fef3c7; color:#b45309; border:1px solid #fcd34d;',
                                'shipped'          => 'background:#f3e8ff; color:#6b21a8; border:1px solid #d8b4fe;',
                                'packed', 'confirmed', 'processing' => 'background:#dbeafe; color:#1e40af; border:1px solid #93c5fd;',
                                'cancelled'        => 'background:#fee2e2; color:#991b1b; border:1px solid #fca5a5;',
                                default            => 'background:#fef3c7; color:#92400e; border:1px solid #fde68a;'
                            };
                            $statusLabel = ucwords(str_replace('_', ' ', $order->status));
                            $firstItem = $order->items->first();
                            $product = $firstItem?->product;
                        @endphp
                        <a href="{{ route('account.orders.show', $order) }}" class="p-3 rounded-4 border bg-white shadow-xs text-decoration-none text-dark d-block transition-all" style="border-color: #e2e8f0 !important;">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-1.5">
                                    <i class="bi bi-box-seam text-primary small"></i>
                                    <span class="fw-bold text-dark font-monospace" style="font-size: 0.85rem;">#{{ $order->order_number }}</span>
                                </div>
                                <span class="badge rounded-pill px-2 py-0.5 fw-bold" style="{{ $statusStyle }} font-size: 0.68rem;">
                                    {{ $statusLabel }}
                                </span>
                            </div>
                            
                            <div class="d-flex align-items-center gap-2.5 mb-2.5">
                                <div class="flex-shrink-0">
                                    @if($product && $product->main_image)
                                        <img src="{{ asset('storage/' . $product->main_image) }}" alt="{{ $firstItem->product_name }}" 
                                             class="rounded-3 border p-0.5 bg-white" width="48" height="48" style="object-fit: contain;">
                                    @else
                                        <div class="rounded-3 bg-light border d-flex align-items-center justify-content-center text-muted" style="width: 48px; height: 48px;">
                                            <i class="bi bi-image opacity-25"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-grow-1">
                                    <div class="fw-semibold text-dark text-truncate mb-0.5" style="font-size: 0.84rem;">
                                        {{ $firstItem ? $firstItem->product_name : 'Order Items' }}
                                    </div>
                                    <div class="text-muted small" style="font-size: 0.72rem;">
                                        {{ $order->created_at ? $order->created_at->format('d M, Y') : '—' }} • {{ $order->items->count() }} {{ Str::plural('item', $order->items->count()) }}
                                    </div>
                                </div>
                                <div class="text-end flex-shrink-0">
                                    <div class="fw-bolder text-dark font-monospace" style="font-size: 0.92rem;">₹{{ number_format($order->total_amount, 2) }}</div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between pt-2 border-top small text-primary fw-semibold" style="font-size: 0.76rem; border-color: #f1f5f9 !important;">
                                <span>Track Order & Invoice</span>
                                <i class="bi bi-chevron-right small"></i>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @else
            <div class="p-4 text-center">
                @include('customer.account.components.empty-state', [
                    'icon'        => 'bi-box-seam',
                    'title'       => 'No Orders Yet',
                    'message'     => 'You haven\'t placed any orders yet. Discover our catalog today!',
                    'button_text' => 'Start Shopping',
                    'button_url'  => route('shop')
                ])
            </div>
        @endif
    </div>
</div>
