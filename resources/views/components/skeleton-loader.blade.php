@props(['count' => 1, 'type' => 'card'])

<div class="skeleton-wrapper w-100">
    <div class="row">
        @for($i = 0; $i < $count; $i++)
            @if($type === 'card')
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card border-0 shadow-sm" aria-hidden="true">
                        <div class="bg-secondary bg-opacity-25" style="height: 220px; border-radius: 0.75rem 0.75rem 0 0;"></div>
                        <div class="card-body">
                            <h5 class="card-title placeholder-glow">
                                <span class="placeholder col-8"></span>
                            </h5>
                            <p class="card-text placeholder-glow">
                                <span class="placeholder col-7"></span>
                                <span class="placeholder col-4"></span>
                                <span class="placeholder col-6"></span>
                            </p>
                            <div class="placeholder-glow mt-3">
                                <span class="placeholder col-12 py-2 rounded"></span>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($type === 'table')
                <div class="col-12 mb-2">
                    <div class="placeholder-glow">
                        <span class="placeholder col-12 py-3 mb-2 rounded bg-secondary bg-opacity-25"></span>
                        <span class="placeholder col-12 py-3 mb-2 rounded bg-secondary bg-opacity-25"></span>
                        <span class="placeholder col-12 py-3 mb-2 rounded bg-secondary bg-opacity-25"></span>
                        <span class="placeholder col-12 py-3 mb-2 rounded bg-secondary bg-opacity-25"></span>
                        <span class="placeholder col-12 py-3 rounded bg-secondary bg-opacity-25"></span>
                    </div>
                </div>
            @elseif($type === 'details')
                <div class="col-lg-6 mb-4">
                     <div class="placeholder-glow">
                         <span class="placeholder col-12 rounded bg-secondary bg-opacity-25" style="height: 400px;"></span>
                     </div>
                </div>
                <div class="col-lg-6 mb-4">
                     <div class="placeholder-glow">
                        <span class="placeholder col-8 py-3 mb-3 rounded bg-secondary bg-opacity-25"></span>
                        <span class="placeholder col-4 py-2 mb-4 rounded bg-secondary bg-opacity-25"></span>
                        <span class="placeholder col-12 py-1 mb-2 rounded bg-secondary bg-opacity-25"></span>
                        <span class="placeholder col-12 py-1 mb-2 rounded bg-secondary bg-opacity-25"></span>
                        <span class="placeholder col-10 py-1 mb-4 rounded bg-secondary bg-opacity-25"></span>
                        <span class="placeholder col-12 py-3 mb-2 rounded bg-secondary bg-opacity-25" style="height: 50px;"></span>
                     </div>
                </div>
            @endif
        @endfor
    </div>
</div>
