<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $category = $this->route('category');
        $categoryId = $category instanceof \App\Models\Category ? $category->id : $category;

        return [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $categoryId,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ];
    }
}
