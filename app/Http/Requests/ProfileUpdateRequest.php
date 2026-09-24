<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'mobile_number' => [
                'required',
                'string',
                'max:15',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'gender' => ['nullable', 'in:Male,Female,Other,Prefer not to say'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'interests' => ['nullable', 'array'],
            'interests.*' => ['exists:categories,id'],
        ];
    }
}
