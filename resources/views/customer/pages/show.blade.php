@extends('layouts.customer')

@section('title', $page->meta_title ?? $page->title)

@push('styles')
    <meta name="description" content="{{ $page->meta_description }}">
@endpush

@section('content')
<div class="container my-3 my-md-4 px-3 px-md-4">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: #ffffff; border: 1.5px solid #e2e8f0 !important;">
                <div class="card-body p-3 p-sm-4 p-md-5">
                    {!! $page->content !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
