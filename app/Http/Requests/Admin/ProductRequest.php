<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $product = $this->route('product');
        $productId = $product instanceof \App\Models\Product ? $product->id : $product;

        return [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'sku' => 'required|string|max:100|unique:products,sku,' . $productId,
            'slug' => 'nullable|string|max:255|unique:products,slug,' . $productId,
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'cost_price' => 'nullable|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'featured' => 'nullable|boolean',
            'trending' => 'nullable|boolean',
            'status' => 'nullable|string|in:Active,Inactive,Pending_Approval,Rejected',
            'main_image'       => 'nullable|file|mimes:jpeg,png,jpg,webp,gif,svg,bmp,jfif,avif|max:10240',
            'gallery_images'   => 'nullable|array',
            'gallery_images.*' => 'nullable|file|mimes:jpeg,png,jpg,webp,gif,svg,bmp,jfif,avif|max:10240',
            'weight'           => 'nullable|numeric|min:0',
            'length'           => 'nullable|numeric|min:0',
            'width'            => 'nullable|numeric|min:0',
            'height'           => 'nullable|numeric|min:0',
            'has_options'      => 'nullable|boolean',
            'option_type'      => 'nullable|string|max:50',
            'option_stocks'    => 'nullable|array',
            'hsn_code'         => 'nullable|string|max:20',
            'tax_rate'         => 'nullable|numeric|min:0|max:100',
        ];
    }
}
