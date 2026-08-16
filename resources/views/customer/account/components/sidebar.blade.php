<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-body p-3">
        <div class="px-3 py-2 mb-2 border-bottom">
            <span class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Account Menu</span>
        </div>
        <nav class="nav flex-column gap-1">
            <a class="nav-link rounded-3 px-3 py-2.5 d-flex align-items-center justify-content-between text-decoration-none fw-semibold {{ request()->routeIs('dashboard') ? 'bg-primary text-white shadow-sm' : 'text-dark hover-light' }}" href="{{ route('dashboard') }}" style="font-size: 0.875rem;">
                <div><i class="bi bi-speedometer2 me-2.5 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-primary' }}"></i> Dashboard</div>
            </a>
            
            <a class="nav-link rounded-3 px-3 py-2.5 d-flex align-items-center justify-content-between text-decoration-none fw-semibold {{ request()->routeIs('account.orders.*') ? 'bg-primary text-white shadow-sm' : 'text-dark hover-light' }}" href="{{ route('account.orders.index') }}" style="font-size: 0.875rem;">
                <div><i class="bi bi-box-seam me-2.5 {{ request()->routeIs('account.orders.*') ? 'text-white' : 'text-primary' }}"></i> My Orders</div>
            </a>

            <a class="nav-link rounded-3 px-3 py-2.5 d-flex align-items-center justify-content-between text-decoration-none fw-semibold {{ request()->routeIs('wishlist.index') ? 'bg-primary text-white shadow-sm' : 'text-dark hover-light' }}" href="{{ route('wishlist.index') }}" style="font-size: 0.875rem;">
                <div><i class="bi bi-heart me-2.5 {{ request()->routeIs('wishlist.index') ? 'text-white' : 'text-danger' }}"></i> Wishlist</div>
            </a>

            <a class="nav-link rounded-3 px-3 py-2.5 d-flex align-items-center justify-content-between text-decoration-none fw-semibold {{ request()->routeIs('account.reviews') ? 'bg-primary text-white shadow-sm' : 'text-dark hover-light' }}" href="{{ route('account.reviews') }}" style="font-size: 0.875rem;">
                <div><i class="bi bi-star me-2.5 {{ request()->routeIs('account.reviews') ? 'text-white' : 'text-warning' }}"></i> My Reviews</div>
            </a>

            <a class="nav-link rounded-3 px-3 py-2.5 d-flex align-items-center justify-content-between text-decoration-none fw-semibold {{ request()->routeIs('account.addresses.*') ? 'bg-primary text-white shadow-sm' : 'text-dark hover-light' }}" href="{{ route('account.addresses.index') }}" style="font-size: 0.875rem;">
                <div><i class="bi bi-geo-alt me-2.5 {{ request()->routeIs('account.addresses.*') ? 'text-white' : 'text-info' }}"></i> Saved Addresses</div>
            </a>

            <a class="nav-link rounded-3 px-3 py-2.5 d-flex align-items-center justify-content-between text-decoration-none fw-semibold {{ request()->routeIs('profile.edit') ? 'bg-primary text-white shadow-sm' : 'text-dark hover-light' }}" href="{{ route('profile.edit') }}" style="font-size: 0.875rem;">
                <div><i class="bi bi-person-gear me-2.5 {{ request()->routeIs('profile.edit') ? 'text-white' : 'text-success' }}"></i> My Profile</div>
            </a>

            <a class="nav-link rounded-3 px-3 py-2.5 d-flex align-items-center justify-content-between text-decoration-none fw-semibold {{ request()->routeIs('account.change-password') ? 'bg-primary text-white shadow-sm' : 'text-dark hover-light' }}" href="{{ route('account.change-password') }}" style="font-size: 0.875rem;">
                <div><i class="bi bi-shield-lock me-2.5 {{ request()->routeIs('account.change-password') ? 'text-white' : 'text-secondary' }}"></i> Change Password</div>
            </a>

            <div class="my-2 border-bottom"></div>

            <a class="nav-link rounded-3 px-3 py-2.5 d-flex align-items-center text-decoration-none fw-semibold text-danger hover-danger-light" href="#" onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();" style="font-size: 0.875rem;">
                <i class="bi bi-box-arrow-right me-2.5"></i> Logout
            </a>
            <form id="logout-form-sidebar" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </nav>
    </div>
</div>

{{-- Lower Support & Guarantees Card --}}
<div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%); border: 1px solid #e2e8f0 !important;">
    <div class="card-body p-3.5 text-center">
        <div class="d-inline-flex align-items-center justify-content-center mb-2 rounded-circle text-white shadow-sm" 
            style="width: 44px; height: 44px; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);">
            <i class="bi bi-headset fs-5"></i>
        </div>
        <h6 class="fw-bold text-dark mb-1" style="font-size: 0.9rem;">Need Assistance?</h6>
        <p class="text-muted small mb-3" style="font-size: 0.78rem;">Have a question about an order or account settings? Our support team is here to help!</p>
        <a href="{{ route('page.contact') }}" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-semibold w-100 shadow-sm" style="font-size: 0.8rem;">
            <i class="bi bi-chat-dots me-1"></i> Contact Support
        </a>
    </div>
    <div class="card-footer bg-white border-top py-2.5 px-3">
        <div class="d-flex align-items-center justify-content-around text-muted small" style="font-size: 0.72rem;">
            <span title="Fast Shipping"><i class="bi bi-truck text-primary me-1"></i> Fast Shipping</span>
            <span>•</span>
            <span title="Secure Checkout"><i class="bi bi-shield-check text-success me-1"></i> 100% Secure</span>
        </div>
    </div>
</div>

<style>
.hover-light:hover { background-color: #f8fafc !important; color: var(--bs-primary) !important; }
.hover-danger-light:hover { background-color: #fef2f2 !important; }
</style>
