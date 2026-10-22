<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class StoreCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id'      => 'required|exists:products,id',
            'quantity'        => 'required|integer|min:1',
            'selected_option' => 'nullable|string|max:100',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $productId = $this->input('product_id');
            if ($productId) {
                $product = Product::find($productId);
                if ($product && $product->has_options && !empty($product->option_stocks)) {
                    $selectedOption = trim((string) $this->input('selected_option'));
                    if (empty($selectedOption)) {
                        $validator->errors()->add('selected_option', 'Please select an available variant (such as size or color) before proceeding.');
                    } else {
                        $stock = $product->getOptionStock($selectedOption);
                        if ($stock < 1) {
                            $validator->errors()->add('selected_option', 'The selected variant is currently out of stock.');
                        }
                    }
                }
            }
        });
    }
}
