<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Passenger\StorePassengerRequest;
use App\Models\Booking;
use App\Models\Passenger;

class PassengerController extends Controller
{
    // -----------------------------------------------
    // GET /api/bookings/{id}/passengers
    // List all passengers for a booking
    // -----------------------------------------------
    public function index($bookingId)
    {
        $booking = Booking::find($bookingId);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found.',
            ], 404);
        }

        // Only the booking owner can see passengers
        if ($booking->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view passengers for this booking.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data'    => $booking->passengers,
        ], 200);
    }

    
    // -----------------------------------------------
    // POST /api/bookings/{id}/passengers
    // Add a passenger to a booking
    // -----------------------------------------------
    public function store(StorePassengerRequest $request, $bookingId)
    {
        $booking = Booking::find($bookingId);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found.',
            ], 404);
        }

        // Authorization check
        if ($booking->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to add passengers to this booking.',
            ], 403);
        }

        // Cannot add passengers to a cancelled booking
        if ($booking->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot add passengers to a cancelled booking.',
            ], 422);
        }

        // Check passenger count does not exceed seat count
        $currentPassengers = $booking->passengers()->count();
        if ($currentPassengers >= $booking->seat_count) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot add more passengers than booked seats (' . $booking->seat_count . ').',
            ], 422);
        }

        $passenger = Passenger::create([
            'booking_id'      => $booking->id,
            'first_name'      => $request->first_name,
            'last_name'       => $request->last_name,
            'passport_number' => $request->passport_number,
            'nationality'     => $request->nationality,
            'date_of_birth'   => $request->date_of_birth,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Passenger added successfully.',
            'data'    => $passenger,
        ], 201);
    }

    
    // -----------------------------------------------
    // DELETE /api/bookings/{id}/passengers/{pid}
    // Remove a passenger from a booking
    // -----------------------------------------------
    public function destroy($bookingId, $passengerId)
    {
        $booking = Booking::find($bookingId);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found.',
            ], 404);
        }

        if ($booking->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to remove passengers from this booking.',
            ], 403);
        }

        $passenger = Passenger::where('id', $passengerId)
            ->where('booking_id', $bookingId)
            ->first();

        if (!$passenger) {
            return response()->json([
                'success' => false,
                'message' => 'Passenger not found in this booking.',
            ], 404);
        }

        $passenger->delete();

        return response()->json([
            'success' => true,
            'message' => 'Passenger removed successfully.',
        ], 200);
    }
}
