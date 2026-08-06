<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'banner_type'        => 'required|in:GENERAL_PROMO,CAMPAIGN_OFFER,CATEGORY_HEADER,BRAND_PROMO',
            'offer_id'           => 'nullable|exists:offers,id',
            'link_category_id'   => 'nullable|exists:categories,id',
            'link_brand_id'      => 'nullable|exists:brands,id',
            'title'              => 'required|string|max:255',
            'subtitle'           => 'nullable|string|max:255',
            'bg_gradient'        => 'nullable|string|max:100',
            'desktop_image'      => ['nullable', 'image', 'max:5120'],
            'mobile_image'       => ['nullable', 'image', 'max:2048'],
            'primary_button_text'=> 'nullable|string|max:50',
            'primary_button_link'=> 'nullable|string|max:255',
            'display_order'      => 'required|integer|min:0',
            'is_active'          => 'boolean',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'is_active' => $this->has('is_active'),
        ]);
    }
}
