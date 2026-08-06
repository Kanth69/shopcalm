@extends('admin.layouts.app')

@section('header', 'Store Settings')

@section('content')
<div class="row">
    <div class="col-md-12">
        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">General Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Store Name</label>
                            <input type="text" name="store_name" class="form-control" value="{{ $settings['store_name'] ?? 'Shopcalm' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Email</label>
                            <input type="email" name="contact_email" class="form-control" value="{{ $settings['contact_email'] ?? '' }}">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Contact Phone</label>
                            <input type="text" name="contact_phone" class="form-control" value="{{ $settings['contact_phone'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Currency</label>
                            <div class="input-group">
                                <input type="text" name="currency" class="form-control" placeholder="INR" value="{{ $settings['currency'] ?? 'INR' }}">
                                <input type="text" name="currency_symbol" class="form-control" placeholder="₹" value="{{ $settings['currency_symbol'] ?? '₹' }}">
                            </div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Store Address</label>
                        <textarea name="address" class="form-control" rows="3">{{ $settings['address'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">Branding</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Store Logo</label>
                            <input type="file" name="logo" class="form-control mb-2">
                            @if(isset($settings['logo']))
                                <img src="{{ asset('storage/' . $settings['logo']) }}" class="img-thumbnail" style="max-height: 100px;">
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Favicon</label>
                            <input type="file" name="favicon" class="form-control mb-2">
                            @if(isset($settings['favicon']))
                                <img src="{{ asset('storage/' . $settings['favicon']) }}" class="img-thumbnail" style="max-height: 32px;">
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0"><i class="bi bi-sliders me-2 text-primary"></i>Home Page Features Control</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Trust Badges Bar</label>
                            <select name="enable_trust_badges" class="form-select">
                                <option value="1" {{ ($settings['enable_trust_badges'] ?? '1') == '1' ? 'selected' : '' }}>Enabled</option>
                                <option value="0" {{ ($settings['enable_trust_badges'] ?? '1') == '0' ? 'selected' : '' }}>Disabled</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Free Shipping Threshold (₹)</label>
                            <input type="number" name="free_shipping_min" class="form-control" value="{{ $settings['free_shipping_min'] ?? '499' }}">
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label font-weight-bold">Flash Sale Section</label>
                            <select name="enable_flash_sale" class="form-select">
                                <option value="1" {{ ($settings['enable_flash_sale'] ?? '1') == '1' ? 'selected' : '' }}>Enabled</option>
                                <option value="0" {{ ($settings['enable_flash_sale'] ?? '1') == '0' ? 'selected' : '' }}>Disabled</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-weight-bold">Flash Sale Title</label>
                            <input type="text" name="flash_sale_title" class="form-control" value="{{ $settings['flash_sale_title'] ?? '⚡ Limited Time Deals' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-weight-bold">Flash Sale Countdown End</label>
                            <input type="datetime-local" name="flash_sale_end_time" class="form-control" value="{{ $settings['flash_sale_end_time'] ?? date('Y-m-d\TH:i', strtotime('+3 days')) }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 mb-5">
                <button type="submit" class="btn btn-primary btn-lg">Save Settings</button>
            </div>
        </form>
    </div>
</div>
@endsection
