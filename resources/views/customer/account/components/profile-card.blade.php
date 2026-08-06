<div class="card mb-4 border-0 shadow-sm rounded-4 text-center">
    <div class="card-body p-4">
        <img src="{{ $user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=EBF4FF&color=7F9CF5' }}"
             alt="{{ $user->name }}" class="rounded-circle mb-3 shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">
        <h5 class="card-title fw-bold">{{ $user->name }}</h5>
        <p class="text-muted mb-1 small">{{ $user->email ?? 'No email provided' }}</p>
        <p class="text-muted small">Member since {{ $user->created_at ? $user->created_at->format('M Y') : 'Unknown' }}</p>
        <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-outline-primary rounded-pill px-4 mt-2">Edit Profile</a>
    </div>
</div>
