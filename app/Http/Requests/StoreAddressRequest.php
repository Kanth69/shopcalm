<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => 'required|string|max:255',
            'phone'      => ['required', 'string', 'digits:10'],
            'house_no'   => 'nullable|string|max:255',
            'street'     => 'nullable|string|max:255',
            'address'    => 'required|string|max:500',
            'city'       => 'required|string|max:100',
            'state'      => 'required|string|max:100',
            'zip'        => 'required|string|max:20',
            'country'    => 'nullable|string|max:100',
            'latitude'   => 'nullable|numeric|between:-90,90',
            'longitude'  => 'nullable|numeric|between:-180,180',
            'landmark'   => 'nullable|string|max:255',
            'type'       => 'nullable|string|max:50',
            'is_default' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'Please enter a 10-digit mobile number.',
            'phone.digits'   => 'Mobile number must be exactly 10 digits.',
        ];
    }
}
