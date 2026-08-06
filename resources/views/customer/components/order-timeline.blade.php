@php
    $statuses = ['pending', 'confirmed', 'packed', 'shipped', 'out for delivery', 'delivered'];
    $currentStatusIndex = array_search($order->status, $statuses);
@endphp

<div class="order-timeline">
    @foreach($statuses as $index => $status)
        <div class="timeline-step {{ $index <= $currentStatusIndex ? 'completed' : '' }}">
            <div class="timeline-icon">
                <i class="bi bi-check"></i>
            </div>
            <div class="timeline-label">{{ ucfirst($status) }}</div>
        </div>
        @if(!$loop->last)
            <div class="timeline-connector {{ $index < $currentStatusIndex ? 'completed' : '' }}"></div>
        @endif
    @endforeach
</div>
