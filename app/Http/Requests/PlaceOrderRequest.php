<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address_id'         => 'nullable|exists:addresses,id',
            'shipping_name'       => 'required|string|max:255',
            'shipping_email'      => 'nullable|email|max:255',
            'shipping_phone'      => 'required|string|max:20',
            'shipping_address'    => 'required|string|max:500',
            'shipping_city'       => 'required|string|max:100',
            'shipping_state'      => 'required|string|max:100',
            'shipping_zip'        => 'required|string|max:20',
            'shipping_country'    => 'required|string|max:100',
            'shipping_latitude'   => 'nullable|numeric|between:-90,90',
            'shipping_longitude'  => 'nullable|numeric|between:-180,180',
            'shipping_landmark'   => 'nullable|string|max:255',
            'payment_method'      => 'nullable|string|in:cod,online,online_razorpay,wallet',
            'use_wallet'          => 'nullable|boolean',
            'terms_consent'       => 'nullable|string',
            'notes'               => 'nullable|string|max:150',
            'save_address'        => 'nullable|boolean',
        ];
    }
}
