<?php

namespace App\Http\Requests\Flight;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFlightRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin'; //only admin can update flights
    }

    
    public function rules(): array
    {
        $flightId = $this->route('id'); //updating specific flight

        return [
            'flight_number'          => ['sometimes', 'string', 'max:20', Rule::unique('flights', 'flight_number')->ignore($flightId)],
            'airline_id'             => ['sometimes', 'integer', 'exists:airlines,id'],
            'origin_airport_id'      => ['sometimes', 'integer', 'exists:airports,id'],
            'destination_airport_id' => ['sometimes', 'integer', 'exists:airports,id', 'different:origin_airport_id'],
            'departure_time'         => ['sometimes', 'date'],
            'arrival_time'           => ['sometimes', 'date', 'after:departure_time'],
            'price'                  => ['sometimes', 'numeric', 'min:0'],
            'total_seats'            => ['sometimes', 'integer', 'min:1'],
            'available_seats'        => ['sometimes', 'integer', 'min:0'],
            'status'                 => ['sometimes', 'in:scheduled,delayed,cancelled,completed'],
        ];
    }
}
