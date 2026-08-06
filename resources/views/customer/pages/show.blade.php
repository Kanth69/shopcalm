@extends('layouts.customer')

@section('title', $page->meta_title ?? $page->title)

@push('styles')
    <meta name="description" content="{{ $page->meta_description }}">
@endpush

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card">
                <div class="card-body p-md-5">
                    {!! $page->content !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
