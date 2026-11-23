@extends('admin.layouts.app')

@section('header', 'Firebase Cloud Messaging (FCM) Push Notifications Hub')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.integrations.index') }}">Integrations Hub</a></li>
    <li class="breadcrumb-item active" aria-current="page">FCM Push Notifications</li>
@endsection

@section('content')
<div class="row g-4 mb-4">
    <!-- Header Summary Card -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 text-white overflow-hidden" style="background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%);">
            <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1"><i class="bi bi-bell-fill me-2"></i>Firebase Cloud Messaging (FCM) Control Center</h4>
                    <p class="mb-0 text-white-50 small">Manage Google Firebase Service Account credentials for automated Abandoned Cart recovery nudges, Order Lifecycle status updates, and Promotional Flash Sale notifications.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.integrations.index') }}" class="btn btn-light rounded-pill px-3 py-2 fw-bold text-dark shadow-sm">
                        <i class="bi bi-arrow-left me-1"></i> Integrations Hub
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Metric Counters --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted fw-bold d-block text-uppercase" style="font-size: 11px;">Integration Status</small>
                    <h5 class="fw-bold mb-0 mt-1 {{ $firebaseStatus === 'ACTIVE' ? 'text-success' : 'text-warning' }}">
                        <i class="bi {{ $firebaseStatus === 'ACTIVE' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' }} me-1"></i>
                        {{ $firebaseStatus === 'ACTIVE' ? 'Connected & Active' : 'Setup Required' }}
                    </h5>
                </div>
                <div class="rounded-circle p-3 {{ $firebaseStatus === 'ACTIVE' ? 'bg-success bg-opacity-10 text-success' : 'bg-warning bg-opacity-10 text-warning' }}">
                    <i class="bi bi-broadcast fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted fw-bold d-block text-uppercase" style="font-size: 11px;">Active Customer Devices</small>
                    <h5 class="fw-bold mb-0 mt-1 text-primary">{{ number_format($activeDevices) }} Devices</h5>
                </div>
                <div class="rounded-circle p-3 bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-phone-fill fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted fw-bold d-block text-uppercase" style="font-size: 11px;">Automated Recovery</small>
                    <h5 class="fw-bold mb-0 mt-1 text-success">Active (30m Interval)</h5>
                </div>
                <div class="rounded-circle p-3 bg-success bg-opacity-10 text-success">
                    <i class="bi bi-cart-check-fill fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Service Account Configuration -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-key-fill text-warning me-2"></i>Google Service Account Credentials (HTTP v1)</h6>
                <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3 py-1 fw-bold" style="font-size: 0.72rem;">OAuth2 Standard</span>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.integrations.firebase.update') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">FIREBASE PROJECT ID</label>
                        <input type="text" name="firebase_project_id" class="form-control rounded-3" 
                               value="{{ $settings['firebase_project_id'] ?? '' }}" 
                               placeholder="e.g. shopcalm-app">
                        <small class="text-muted d-block mt-1">Found in Firebase Console ➔ Project Settings ➔ General.</small>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-bold small text-muted mb-0">SERVICE ACCOUNT PRIVATE KEY JSON</label>
                            @if($hasServiceAccount)
                                <span class="badge bg-success-subtle text-success border border-success-subtle fw-semibold">
                                    <i class="bi bi-shield-lock-fill me-1"></i>Key Configured &amp; Active
                                    @if(!empty($hasLocalFile) && empty($settings['firebase_service_account_json']))
                                        (via storage file)
                                    @endif
                                </span>
                            @endif
                        </div>
                        <textarea name="firebase_service_account_json" rows="8" class="form-control font-monospace rounded-3" 
                                  placeholder='@if(!empty($hasLocalFile))•••••••• (Service account JSON is loaded and active from storage/app/firebase-service-account.json — leave blank to keep)@else Paste entire contents of your Firebase service-account.json here:&#10;{&#10;  "type": "service_account",&#10;  "project_id": "...",&#10;  "private_key_id": "...",&#10;  "private_key": "-----BEGIN PRIVATE KEY-----...",&#10;  "client_email": "..."&#10;}@endif'>{{ $settings['firebase_service_account_json'] ?? '' }}</textarea>
                        <small class="text-muted d-block mt-1">
                            @if(!empty($hasLocalFile) && empty($settings['firebase_service_account_json']))
                                <i class="bi bi-check-circle-fill text-success me-1"></i> Using active private key from <code>storage/app/firebase-service-account.json</code> (OAuth2 HTTP v1 connected).
                            @else
                                Download from: <strong>Firebase Console ➔ Project Settings ➔ Service Accounts ➔ Generate New Private Key</strong>.
                            @endif
                        </small>
                    </div>

                    <button type="submit" class="btn btn-warning text-dark fw-bold rounded-pill px-4 py-2 shadow-sm">
                        <i class="bi bi-save2-fill me-1"></i> Save Firebase Credentials
                    </button>
                </form>
            </div>
        </div>

        {{-- Test Notification Tool --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-send-check-fill text-primary me-2"></i>Test FCM Push Connection</h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.integrations.firebase.test') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">TARGET DEVICE FCM TOKEN (OPTIONAL)</label>
                        <input type="text" name="test_fcm_token" class="form-control rounded-3 font-monospace" placeholder="Paste device FCM token to send an actual push to your phone">
                        <small class="text-muted d-block mt-1">Leave blank to simply test OAuth2 authentication with Google.</small>
                    </div>
                    <button type="submit" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-bold">
                        <i class="bi bi-lightning-charge-fill me-1"></i> Test Google Connection
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Setup Guide & Recent Devices -->
    <div class="col-lg-5">
        {{-- How to get credentials --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-info-circle-fill text-info me-2"></i>How to get your Firebase Credentials</h6>
            </div>
            <div class="card-body p-4 small">
                <ol class="ps-3 mb-0" style="line-height: 1.8;">
                    <li>Go to <a href="https://console.firebase.google.com/" target="_blank" class="fw-bold text-decoration-none text-primary">Firebase Console <i class="bi bi-box-arrow-up-right"></i></a>.</li>
                    <li>Click <strong>Add Project</strong> (name it e.g. <code>shopcalm-app</code>).</li>
                    <li>Click the <strong>Gear icon ⚙️ ➔ Project Settings</strong>.</li>
                    <li>Open the <strong>Service Accounts</strong> tab.</li>
                    <li>Click <strong>Generate New Private Key</strong> and download the <code>.json</code> file.</li>
                    <li>Open that file in Notepad, copy everything, and paste it into the box on the left.</li>
                </ol>
            </div>
        </div>

        {{-- Recent Registered Devices --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-phone-vibrate text-secondary me-2"></i>Recently Connected Devices</h6>
                <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">Live Registered</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive mb-0">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.82rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Customer</th>
                                <th>Platform</th>
                                <th class="pe-3">Registered</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentDevices as $device)
                                <tr>
                                    <td class="ps-3 fw-semibold">
                                        {{ $device->user?->name ?? 'Guest Device' }}
                                        <div class="text-muted" style="font-size: 0.74rem;">{{ substr($device->fcm_token, 0, 14) }}...</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border rounded-pill text-uppercase" style="font-size: 0.68rem;">
                                            <i class="bi bi-{{ $device->device_type === 'ios' ? 'apple' : 'android2' }} me-1"></i>
                                            {{ $device->device_type }}
                                        </span>
                                    </td>
                                    <td class="pe-3 text-muted">
                                        {{ $device->created_at->diffForHumans(null, true) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">
                                        <i class="bi bi-phone text-muted fs-4 d-block mb-1"></i>
                                        No mobile devices registered yet. Devices register automatically when customers open the app.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
