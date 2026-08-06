<div class="card account-sidebar">
    <div class="card-body">
        <nav class="nav flex-column nav-pills">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <i class="bi bi-house-door me-2"></i> Dashboard
            </a>
            <a class="nav-link {{ request()->routeIs('account.orders.*') ? 'active' : '' }}" href="{{ route('account.orders.index') }}">
                <i class="bi bi-box-seam me-2"></i> My Orders
            </a>
            <a class="nav-link {{ request()->routeIs('wishlist.index') ? 'active' : '' }}" href="{{ route('wishlist.index') }}">
                <i class="bi bi-heart me-2"></i> Wishlist
            </a>
            <a class="nav-link {{ request()->routeIs('account.reviews') ? 'active' : '' }}" href="{{ route('account.reviews') }}">
                <i class="bi bi-star me-2"></i> My Reviews
            </a>
            <a class="nav-link {{ request()->routeIs('account.addresses.*') ? 'active' : '' }}" href="{{ route('account.addresses.index') }}">
                <i class="bi bi-geo-alt me-2"></i> Saved Addresses
            </a>
            <a class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
                <i class="bi bi-person me-2"></i> My Profile
            </a>
            <a class="nav-link {{ request()->routeIs('account.change-password') ? 'active' : '' }}" href="{{ route('account.change-password') }}">
                <i class="bi bi-lock me-2"></i> Change Password
            </a>
            <hr>
            <a class="nav-link text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();">
                <i class="bi bi-door-open me-2"></i> Logout
            </a>
            <form id="logout-form-sidebar" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </nav>
    </div>
</div>
