<div class="text-center p-4">
    <i class="bi {{ $icon }} display-4 text-muted"></i>
    <h5 class="mt-3">{{ $title }}</h5>
    <p class="text-muted">{{ $message }}</p>
    @if(isset($button_text) && isset($button_url))
        <a href="{{ $button_url }}" class="btn btn-primary mt-2">{{ $button_text }}</a>
    @endif
</div>
