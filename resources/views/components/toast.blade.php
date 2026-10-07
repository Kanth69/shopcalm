@php
    $toastData = null;

    if (session()->has('toast')) {
        $toastData = session('toast');
    } elseif (session()->has('success')) {
        $toastData = [
            'type'    => 'success',
            'title'   => 'Success',
            'message' => session('success'),
        ];
    } elseif (session()->has('error')) {
        $toastData = [
            'type'    => 'danger',
            'title'   => 'Error',
            'message' => session('error'),
        ];
    } elseif (session()->has('status')) {
        $toastData = [
            'type'    => 'info',
            'title'   => 'Notice',
            'message' => session('status'),
        ];
    } elseif (session()->has('info')) {
        $toastData = [
            'type'    => 'info',
            'title'   => 'Information',
            'message' => session('info'),
        ];
    } elseif (isset($errors) && $errors->any()) {
        $toastData = [
            'type'    => 'danger',
            'title'   => 'Could Not Save Changes',
            'message' => $errors->first(),
        ];
    }
@endphp

@if($toastData)
    @php
        $type = $toastData['type'] ?? 'success';
        if ($type === 'error') { $type = 'danger'; }
        
        $accentColor = match($type) {
            'danger'  => '#ef4444',
            'warning' => '#f59e0b',
            'info'    => '#6366f1',
            default   => '#10b981',
        };
        
        $iconClass = match($type) {
            'danger'  => 'bi-exclamation-octagon-fill',
            'warning' => 'bi-exclamation-triangle-fill',
            'info'    => 'bi-info-circle-fill',
            default   => 'bi-check-circle-fill',
        };
    @endphp

    <div id="shopcalm-global-toast" class="position-fixed" style="top: 24px; right: 24px; z-index: 109999; max-width: 420px; width: calc(100vw - 48px); pointer-events: none;">
        <div class="card border-0 shadow-lg overflow-hidden position-relative animate-toast-slide" 
             style="background: #ffffff; border-radius: 16px; border-left: 5px solid {{ $accentColor }} !important; box-shadow: 0 20px 45px -10px rgba(15, 23, 42, 0.22), 0 0 1px 1px rgba(0,0,0,0.05); pointer-events: auto;">
            
            <div class="card-body p-3.5 d-flex align-items-start gap-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" 
                     style="width: 38px; height: 38px; background: {{ $accentColor }}18; color: {{ $accentColor }}; font-size: 1.25rem;">
                    <i class="bi {{ $iconClass }}"></i>
                </div>
                
                <div class="flex-grow-1 overflow-hidden pe-2">
                    <div class="d-flex align-items-center justify-content-between mb-0.5">
                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.9rem; letter-spacing: -0.2px;">
                            {{ $toastData['title'] ?? 'Notification' }}
                        </h6>
                    </div>
                    <p class="text-secondary mb-0 small" style="font-size: 0.82rem; line-height: 1.45;">
                        {{ $toastData['message'] }}
                    </p>
                </div>

                <button type="button" class="btn-close ms-auto flex-shrink-0 p-1" onclick="dismissGlobalToast()" aria-label="Close" style="font-size: 0.75rem;"></button>
            </div>

            <!-- Auto-dismiss Progress Bar -->
            <div id="shopcalm-toast-progress" style="height: 3px; background: {{ $accentColor }}; width: 100%; transition: width 4s linear;"></div>
        </div>
    </div>

    <style>
        @keyframes toastSlideIn {
            0% {
                transform: translateX(120%) scale(0.95);
                opacity: 0;
            }
            100% {
                transform: translateX(0) scale(1);
                opacity: 1;
            }
        }

        @keyframes toastSlideOut {
            0% {
                transform: translateX(0) scale(1);
                opacity: 1;
            }
            100% {
                transform: translateX(120%) scale(0.95);
                opacity: 0;
            }
        }

        .animate-toast-slide {
            animation: toastSlideIn 0.38s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .animate-toast-dismiss {
            animation: toastSlideOut 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>

    <script>
        function dismissGlobalToast() {
            const toast = document.getElementById('shopcalm-global-toast');
            if (toast) {
                const card = toast.querySelector('.card');
                if (card) {
                    card.classList.remove('animate-toast-slide');
                    card.classList.add('animate-toast-dismiss');
                    setTimeout(() => toast.remove(), 320);
                } else {
                    toast.remove();
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const progressBar = document.getElementById('shopcalm-toast-progress');
            if (progressBar) {
                // Trigger transition to 0% width
                setTimeout(() => {
                    progressBar.style.width = '0%';
                }, 50);

                // Auto dismiss after 4 seconds
                setTimeout(dismissGlobalToast, 4000);
            }
        });
    </script>
@endif
