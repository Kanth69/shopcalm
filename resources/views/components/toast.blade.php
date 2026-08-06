@if(session('toast'))
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
        <div id="liveToast" class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-{{ session('toast.type') == 'success' ? 'success' : 'danger' }} text-white">
                <strong class="me-auto">{{ session('toast.title') }}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                {{ session('toast.message') }}
            </div>
        </div>
    </div>
@endif
