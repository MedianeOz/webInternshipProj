<?php

namespace App\Http\Requests\Flight;

use App\Models\Flight;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
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

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $flight = Flight::find($this->route('id'));

                if (!$flight) {
                    return;
                }

                $totalSeats = $this->has('total_seats')
                    ? (int) $this->input('total_seats')
                    : (int) $flight->total_seats;

                $availableSeats = $this->has('available_seats')
                    ? (int) $this->input('available_seats')
                    : (int) $flight->available_seats;

                if ($availableSeats > $totalSeats) {
                    $validator->errors()->add(
                        'available_seats',
                        'Available seats cannot exceed total seats.'
                    );
                }

                $originAirportId = $this->has('origin_airport_id')
                    ? (int) $this->input('origin_airport_id')
                    : (int) $flight->origin_airport_id;

                $destinationAirportId = $this->has('destination_airport_id')
                    ? (int) $this->input('destination_airport_id')
                    : (int) $flight->destination_airport_id;

                if ($originAirportId === $destinationAirportId) {
                    $validator->errors()->add(
                        'destination_airport_id',
                        'Destination must be different from origin.'
                    );
                }

                if ($this->has('departure_time') || $this->has('arrival_time')) {
                    try {
                        $departureTime = Carbon::parse($this->input('departure_time', $flight->departure_time));
                        $arrivalTime = Carbon::parse($this->input('arrival_time', $flight->arrival_time));
                    } catch (\Throwable) {
                        return;
                    }

                    if ($arrivalTime->lte($departureTime)) {
                        $validator->errors()->add(
                            'arrival_time',
                            'Arrival time must be after departure time.'
                        );
                    }
                }
            },
        ];
    }
}
