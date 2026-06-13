<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Flight;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data): Booking {
            $flight = Flight::whereKey($data['flight_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $seatCount = (int) $data['seat_count'];

            if ($flight->status !== 'scheduled') {
                throw ValidationException::withMessages([
                    'data.flight_id' => 'This flight is not available for booking.',
                ]);
            }

            if ($flight->available_seats < $seatCount) {
                throw ValidationException::withMessages([
                    'data.seat_count' => 'Not enough seats available. Only '.$flight->available_seats.' seats left.',
                ]);
            }

            if (! in_array($data['status'], ['pending', 'confirmed'], true)) {
                throw ValidationException::withMessages([
                    'data.status' => 'The selected booking status is invalid.',
                ]);
            }

            $booking = Booking::create([
                'user_id' => $data['user_id'],
                'flight_id' => $flight->id,
                'booking_reference' => $this->generateBookingReference(),
                'seat_count' => $seatCount,
                'total_price' => (float) $flight->price * $seatCount,
                'status' => $data['status'],
            ]);

            $flight->decrement('available_seats', $seatCount);

            return $booking;
        });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', [
            'record' => $this->record,
        ]);
    }

    private function generateBookingReference(): string
    {
        do {
            $reference = 'BV-'.strtoupper(Str::random(8));
        } while (Booking::where('booking_reference', $reference)->exists());

        return $reference;
    }
}
