<?php

namespace App\Http\Requests\Admin;

use App\Enums\CouponType;
use App\Enums\CouponApplicableType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rule;

class CouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        $id = $this->route('coupon')?->id;

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('coupons', 'code')->ignore($id)],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'discount_type' => ['required', new Enum(CouponType::class)],
            'discount_value' => 'required|numeric|min:0.01',
            'minimum_order_amount' => 'required|numeric|min:0',
            'maximum_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_limit_per_customer' => 'required|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'boolean',
            'stackable' => 'boolean',
            'priority' => 'required|integer|min:0',
            'applicable_type' => ['required', new Enum(CouponApplicableType::class)],
            'applicable_id' => [
                'nullable',
                Rule::requiredIf(fn() => $this->applicable_type !== CouponApplicableType::ALL->value),
                'integer'
            ],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'is_active' => $this->has('is_active'),
            'stackable' => $this->has('stackable'),
            'code' => strtoupper($this->code),
        ]);
    }
}
