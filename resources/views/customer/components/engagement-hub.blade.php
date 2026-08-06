@if(isset($activeEngagement) && $activeEngagement)
    <div id="engagement-card" class="card border-0 shadow-sm mb-4 overflow-hidden"
         style="border-left: 5px solid {{ $activeEngagement['template']['theme_color'] ?? '#3b82f6' }} !important;">
        <div class="card-body p-4">
            <div class="d-flex align-items-start">
                <div class="flex-shrink-0 bg-light rounded-circle p-3 me-3 text-primary">
                    <i class="bi {{ $activeEngagement['template']['icon'] ?? 'bi-megaphone' }} fs-3"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h5 class="fw-bold mb-0">{{ $activeEngagement['template']['title'] }}</h5>
                        <button type="button" class="btn-close small" id="dismiss-engagement"
                                data-campaign-id="{{ $activeEngagement['config']['id'] }}" title="Skip for now"></button>
                    </div>

                    <div id="engagement-content-step-1">
                        <p class="text-muted mb-3">{{ $activeEngagement['template']['message'] }}</p>

                        @if(isset($activeEngagement['data']['percentage']))
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small text-muted">Profile Strength</span>
                                    <span class="small fw-bold text-primary">{{ $activeEngagement['data']['percentage'] }}%</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-primary" role="progressbar"
                                         style="width: {{ $activeEngagement['data']['percentage'] }}%"></div>
                                </div>
                            </div>
                        @endif

                        <div class="d-flex align-items-center gap-2">
                            @if($activeEngagement['config']['key'] === 'PROFILE_COMPLETION')
                                <button type="button" class="btn btn-primary px-4 shadow-sm" id="start-inline-completion">
                                    Complete Profile
                                </button>
                                <button type="button" class="btn btn-link text-muted text-decoration-none small" id="skip-engagement-link">
                                    Skip for now
                                </button>
                            @else
                                <a href="{{ $activeEngagement['template']['button_url'] ?? '#' }}" class="btn btn-primary px-4">
                                    {{ $activeEngagement['template']['button_text'] ?? 'Action' }}
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Inline Profile Form (Hidden by default) --}}
                    <div id="engagement-content-step-2" class="d-none">
                        <form id="inline-profile-form" class="mt-2">
                            <div class="row g-3">
                                {{-- Only show fields that are currently missing --}}
                                @php
                                    $missingKeys = collect($activeEngagement['data']['missing_fields'])->pluck('key')->toArray();
                                    $hasFields = false;
                                @endphp

                                @if(in_array('gender', $missingKeys))
                                    @php $hasFields = true; @endphp
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Gender <span class="text-muted fw-normal">(Optional)</span></label>
                                        <select name="gender" class="form-select form-select-sm">
                                            <option value="">Select Gender</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                @endif

                                @if(in_array('date_of_birth', $missingKeys))
                                    @php $hasFields = true; @endphp
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Birthday <span class="text-muted fw-normal">(Optional)</span></label>
                                        <input type="date" name="date_of_birth" class="form-control form-control-sm">
                                    </div>
                                @endif

                                @if(!$hasFields)
                                    <div class="col-12">
                                        <p class="text-muted">Please visit the full profile page to complete the remaining steps (Address & Interests).</p>
                                        <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-primary">Go to Full Profile</a>
                                    </div>
                                @endif
                            </div>

                            @if($hasFields)
                                <div class="mt-3 d-flex align-items-center gap-3">
                                    <button type="submit" class="btn btn-sm btn-primary px-4">Save Profile</button>
                                    <button type="button" class="btn btn-sm btn-light" id="cancel-inline">Back</button>
                                </div>
                            @endif
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        (function() {
            const card = document.getElementById('engagement-card');
            const step1 = document.getElementById('engagement-content-step-1');
            const step2 = document.getElementById('engagement-content-step-2');
            const startBtn = document.getElementById('start-inline-completion');
            const cancelBtn = document.getElementById('cancel-inline');
            const form = document.getElementById('inline-profile-form');
            const dismissBtn = document.getElementById('dismiss-engagement');
            const skipLink = document.getElementById('skip-engagement-link');

            const campaignId = "{{ $activeEngagement['config']['id'] }}";

            const fadeOut = () => {
                card.style.transition = 'all 0.4s ease';
                card.style.opacity = '0';
                card.style.transform = 'translateY(-20px)';
                setTimeout(() => card.remove(), 400);
            };

            const logDismissal = () => {
                fadeOut();
                fetch("{{ route('engagement.dismiss', ':id') }}".replace(':id', campaignId), {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
            };

            startBtn?.addEventListener('click', () => {
                step1.classList.add('d-none');
                step2.classList.remove('d-none');
            });

            cancelBtn?.addEventListener('click', () => {
                step2.classList.add('d-none');
                step1.classList.remove('d-none');
            });

            dismissBtn?.addEventListener('click', logDismissal);
            skipLink?.addEventListener('click', logDismissal);

            form?.addEventListener('submit', function(e) {
                e.preventDefault();
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

                const formData = new FormData(this);
                const data = Object.fromEntries(formData.entries());

                fetch("{{ route('engagement.complete-profile') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                }).then(res => res.json()).then(res => {
                    if (res.success) {
                        window.location.reload();
                    }
                });
            });
        })();
    </script>
    @endpush
@endif
