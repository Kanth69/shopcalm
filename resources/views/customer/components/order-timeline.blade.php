@php
    $normalizedStatus = strtolower(str_replace(['_', '-'], ' ', (string)$order->status));
    $statuses = ['pending', 'confirmed', 'packed', 'shipped', 'out for delivery', 'delivered'];
    $currentStatusIndex = array_search($normalizedStatus, $statuses);
    if ($currentStatusIndex === false) {
        $statusMap = [
            'placed' => 0,
            'processing' => 1,
        ];
        $currentStatusIndex = $statusMap[$normalizedStatus] ?? 0;
    }
@endphp

<div class="order-timeline">
    @foreach($statuses as $index => $status)
        <div class="timeline-step {{ $index <= $currentStatusIndex ? 'completed' : '' }}">
            <div class="timeline-icon">
                <i class="bi bi-check"></i>
            </div>
            <div class="timeline-label">{{ ucwords($status) }}</div>
        </div>
        @if(!$loop->last)
            <div class="timeline-connector {{ $index < $currentStatusIndex ? 'completed' : '' }}"></div>
        @endif
    @endforeach
</div>
