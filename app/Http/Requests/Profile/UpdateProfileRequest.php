<?php

namespace App\Http\Requests\Profile;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return auth()->check();
    }

    
    public function rules(): array
    {
         return [
            'name'     => ['sometimes', 'string', 'min:1', 'max:255'],
            'phone'    => ['nullable', 'string', 'max:14'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'         => 'Your name cannot be empty.',
            'name.max'              => 'Name must not exceed 255 characters.',
            'phone.max'             => 'Phone number is too long.',
            'password.min'          => 'New password must be at least 8 characters.',
            'password.confirmed'    => 'Password confirmation does not match.',
        ];
    }
}
