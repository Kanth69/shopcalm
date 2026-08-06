@extends('admin.layouts.app')

@section('header', 'Business Reports')

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.reports.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Report Type</label>
                    <select name="type" class="form-select">
                        <option value="sales" {{ $type == 'sales' ? 'selected' : '' }}>Sales Report</option>
                        <option value="revenue" {{ $type == 'revenue' ? 'selected' : '' }}>Revenue Report</option>
                        <option value="customers" {{ $type == 'customers' ? 'selected' : '' }}>Customer Report</option>
                        <option value="products" {{ $type == 'products' ? 'selected' : '' }}>Product Report</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $start }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $end }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">Generate</button>
                    <a href="{{ request()->fullUrlWithQuery(['export' => 1]) }}" class="btn btn-outline-success"><i class="bi bi-download"></i></a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        @if($type == 'sales')
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->user->name }}</td>
                            <td>₹{{ number_format($order->total_amount, 2) }}</td>
                            <td>@include('customer.components.order-status-badge', ['status' => $order->status])</td>
                            <td>{{ $order->created_at->format('d M, Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @elseif($type == 'revenue')
             <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th class="text-end">Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $row)
                        <tr>
                            <td>{{ $row->date }}</td>
                            <td class="text-end fw-bold">₹{{ number_format($row->total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th>TOTAL</th>
                            <th class="text-end">₹{{ number_format($data->sum('total'), 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
