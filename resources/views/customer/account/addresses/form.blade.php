<div class="row">
    <div class="col-md-12 mb-3">
        <label for="name" class="form-label">Full Name</label>
        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $address->name ?? '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label for="phone" class="form-label">Phone</label>
        <input type="tel" class="form-control" id="phone" name="phone" value="{{ old('phone', $address->phone ?? '') }}" required>
    </div>
    <div class="col-12 mb-3">
        <label for="address" class="form-label">Address</label>
        <textarea class="form-control" id="address" name="address" rows="3" required>{{ old('address', $address->address ?? '') }}</textarea>
    </div>
    <div class="col-md-4 mb-3">
        <label for="city" class="form-label">City</label>
        <input type="text" class="form-control" id="city" name="city" value="{{ old('city', $address->city ?? '') }}" required>
    </div>
    <div class="col-md-4 mb-3">
        <label for="state" class="form-label">State</label>
        <input type="text" class="form-control" id="state" name="state" value="{{ old('state', $address->state ?? '') }}" required>
    </div>
    <div class="col-md-4 mb-3">
        <label for="zip" class="form-label">ZIP Code</label>
        <input type="text" class="form-control" id="zip" name="zip" value="{{ old('zip', $address->zip ?? '') }}" required>
    </div>
    <div class="col-12 mb-3">
        <label for="country" class="form-label">Country</label>
        <input type="text" class="form-control" id="country" name="country" value="{{ old('country', $address->country ?? 'India') }}" required>
    </div>
</div>
