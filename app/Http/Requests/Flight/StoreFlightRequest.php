<?php

namespace App\Http\Requests\Flight;


use Illuminate\Foundation\Http\FormRequest;

class StoreFlightRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin'; //only admins can create flights
    }

    
    public function rules(): array
    {
        return [
            'flight_number'          => ['required', 'string', 'max:20', 'unique:flights,flight_number'],
            'airline_id'             => ['required', 'integer', 'exists:airlines,id'],
            'origin_airport_id'      => ['required', 'integer', 'exists:airports,id'],
            'destination_airport_id' => ['required', 'integer', 'exists:airports,id', 'different:origin_airport_id'],
            'departure_time'         => ['required', 'date', 'after:now'],
            'arrival_time'           => ['required', 'date', 'after:departure_time'],
            'price'                  => ['required', 'numeric', 'min:0'],
            'total_seats'            => ['required', 'integer', 'min:1'],
            'available_seats'        => ['required', 'integer', 'min:0', 'lte:total_seats'],
            'status'                 => ['required', 'in:scheduled,delayed,cancelled,completed'],
        ];
    }

    public function messages(): array
    {
        return [
            'flight_number.unique'                => 'This flight number already exists.',
            'destination_airport_id.different'    => 'Destination must be different from origin.',
            'departure_time.after'                => 'Departure time must be in the future.',
            'arrival_time.after'                  => 'Arrival time must be after departure time.',
            'available_seats.lte'                 => 'Available seats cannot exceed total seats.',
            'status.in'                           => 'Status must be: scheduled, delayed, cancelled, or completed.',
        ];
    }

}