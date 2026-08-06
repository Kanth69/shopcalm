@extends('admin.layouts.app')

@section('header', 'Admin User Management')

@section('actions')
    @can('manage-admins', App\Models\User::class)
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus"></i> Create New Admin
        </a>
    @endcan
@endsection

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Name</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Role</th>
                        <th>Last Login</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($admins as $admin)
                    <tr>
                        <td class="ps-3 fw-bold">{{ $admin->name }}</td>
                        <td>{{ $admin->email }}</td>
                        <td>{{ $admin->mobile_number }}</td>
                        <td>
                            <span class="badge bg-{{ $admin->role_id == 0 ? 'danger' : 'primary' }}">
                                {{ $admin->role_id == 0 ? 'Super Admin' : 'Admin' }}
                            </span>
                        </td>
                        <td>{{ $admin->last_login_at ? $admin->last_login_at->diffForHumans() : 'Never' }}</td>
                        <td class="text-end pe-3">
                            @can('manage-admins', App\Models\User::class)
                                @if($admin->id !== auth()->id())
                                    <form action="{{ route('admin.roles.destroy', $admin) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
