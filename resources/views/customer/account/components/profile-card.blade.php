<div class="card border-0 shadow-xs rounded-4 overflow-hidden mb-3.5" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
    <div class="card-header bg-white py-3 px-3.5 px-md-4 border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-3 d-flex align-items-center justify-content-center text-success" style="width: 30px; height: 30px; background: #d1fae5;">
                <i class="bi bi-shield-check"></i>
            </div>
            <h6 class="mb-0 fw-bolder text-dark" style="font-size: 0.92rem;">Account Status</h6>
        </div>
        <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background: rgba(16, 185, 129, 0.15); color: #059669; font-size: 0.7rem;">
            Active
        </span>
    </div>
    <div class="card-body p-3 p-md-3.5">
        <div class="d-flex align-items-center gap-2.5 mb-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-xs flex-shrink-0"
                 style="width: 48px; height: 48px; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); font-size: 1.2rem;">
                {{ strtoupper(substr($user->name ?? 'C', 0, 1)) }}
            </div>
            <div class="overflow-hidden">
                <h6 class="fw-bold text-dark mb-0.5 text-truncate" style="font-size: 0.9rem;">{{ $user->name }}</h6>
                <p class="text-muted small mb-0 text-truncate" style="font-size: 0.76rem;">{{ $user->email ?? $user->mobile_number }}</p>
                <div class="text-muted small" style="font-size: 0.7rem;">Member since {{ $user->created_at ? $user->created_at->format('M Y') : '2026' }}</div>
            </div>
        </div>

        <div class="p-2.5 rounded-3 mb-3" style="background: #f8fafc; border: 1px solid #f1f5f9; font-size: 0.78rem;">
            <div class="d-flex justify-content-between mb-1">
                <span class="text-muted"><i class="bi bi-envelope-check me-1.5 text-success"></i>Email:</span>
                <span class="fw-semibold text-dark">{{ $user->email ? 'Added' : 'Not added' }}</span>
            </div>
            <div class="d-flex justify-content-between">
                <span class="text-muted"><i class="bi bi-telephone-check me-1.5 text-success"></i>Mobile:</span>
                <span class="fw-semibold text-dark">{{ $user->mobile_number ? 'Linked' : 'Not added' }}</span>
            </div>
        </div>

        <div class="d-grid gap-2">
            <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary btn-sm rounded-pill fw-semibold py-1.5" style="font-size: 0.8rem;">
                <i class="bi bi-pencil-square me-1"></i> Edit Profile
            </a>
        </div>
    </div>
</div>
