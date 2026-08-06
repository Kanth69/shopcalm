@extends('admin.layouts.app')

@section('header', 'Product Reviews')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.reviews.index') }}" class="mb-4">
            <div class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search reviews..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                        <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>User</th>
                        <th>Rating</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $review)
                    <tr>
                        <td>{{ $review->product->name }}</td>
                        <td>{{ $review->user->name }}</td>
                        <td>{{ $review->rating }} ★</td>
                        <td>{{ $review->title }}</td>
                        <td>
                            <span class="badge bg-{{ $review->status == 'Approved' ? 'success' : ($review->status == 'Rejected' ? 'danger' : 'warning') }}">
                                {{ $review->status }}
                            </span>
                        </td>
                        <td>{{ $review->created_at->format('d M, Y') }}</td>
                        <td>
                            <div class="btn-group">
                                <form action="{{ route('admin.reviews.update', $review) }}" method="POST" class="me-1">
                                    @csrf
                                    @method('PATCH')
                                    @if($review->status != 'Approved')
                                        <button type="submit" name="status" value="Approved" class="btn btn-sm btn-success">Approve</button>
                                    @endif
                                    @if($review->status != 'Rejected')
                                        <button type="submit" name="status" value="Rejected" class="btn btn-sm btn-warning">Reject</button>
                                    @endif
                                </form>
                                <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No reviews found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $reviews->links() }}
        </div>
    </div>
</div>
@endsection
