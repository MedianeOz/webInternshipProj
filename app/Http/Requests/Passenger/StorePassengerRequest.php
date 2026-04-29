<?php

namespace App\Http\Requests\Passenger;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePassengerRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return auth()->check();
    }

    
    public function rules(): array
    {
        return [
            'first_name'      => ['required', 'string', 'max:100'],
            'last_name'       => ['required', 'string', 'max:100'],
            'passport_number' => ['nullable', 'string', 'max:20'],
            'nationality'     => ['nullable', 'string', 'max:100'],
            'date_of_birth'   => ['nullable', 'date', 'before:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required'  => 'First name is required.',
            'last_name.required'   => 'Last name is required.',
            'date_of_birth.before' => 'Date of birth must be in the past.',
        ];
    }

}
