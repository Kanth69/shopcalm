@php
    $badgeClass = 'bg-secondary';
    switch ($status) {
        case 'pending': $badgeClass = 'bg-secondary'; break;
        case 'confirmed': $badgeClass = 'bg-primary'; break;
        case 'packed': $badgeClass = 'bg-info'; break;
        case 'shipped': $badgeClass = 'bg-warning text-dark'; break;
        case 'out for delivery': $badgeClass = 'bg-dark'; break;
        case 'delivered': $badgeClass = 'bg-success'; break;
        case 'cancelled': $badgeClass = 'bg-danger'; break;
    }
@endphp

<span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span>
