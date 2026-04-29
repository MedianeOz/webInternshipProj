<?php

namespace App\Http\Requests\Booking;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return auth()->check(); //authenticated user can book a flight, not a guest
    }

    
    public function rules(): array
    {
        return [
            'flight_id'  => ['required', 'integer', 'exists:flights,id'],
            'seat_count' => ['required', 'integer', 'min:1', 'max:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'flight_id.exists'   => 'The selected flight does not exist.',
            'seat_count.min'     => 'You must book at least 1 seat.',
            'seat_count.max'     => 'You cannot book more than 10 seats at once.',
        ];
    }
}
