<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Flight;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    // -----------------------------------------------
    // GET /api/bookings
    // List all bookings for the authenticated user
    // -----------------------------------------------
    public function index()
    {
        $bookings = Booking::with(['flight.airline', 'flight.originAirport', 'flight.destinationAirport', 'passengers'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $bookings,
        ], 200);
    }


    // -----------------------------------------------
    // POST /api/bookings
    // Create a new booking
    // -----------------------------------------------
    public function store(StoreBookingRequest $request)
    {
        $flight = Flight::find($request->flight_id);

        // Check flight is available
        if ($flight->status !== 'scheduled') {
            return response()->json([
                'success' => false,
                'message' => 'This flight is not available for booking.',
            ], 422);
        }

        // Check enough seats available
        if ($flight->available_seats < $request->seat_count) {
            return response()->json([
                'success' => false,
                'message' => 'Not enough seats available. Only ' . $flight->available_seats . ' seats left.',
            ], 422);
        }

        // Calculate total price
        $totalPrice = $flight->price * $request->seat_count;

        // Create the booking
        $booking = Booking::create([
            'user_id'           => auth()->id(),
            'flight_id'         => $flight->id,
            'booking_reference' => 'BV-' . strtoupper(Str::random(8)),
            'seat_count'        => $request->seat_count,
            'total_price'       => $totalPrice,
            'status'            => 'confirmed',
        ]);

        // Reduce available seats on the flight
        $flight->decrement('available_seats', $request->seat_count);

        return response()->json([
            'success' => true,
            'message' => 'Booking created successfully.',
            'data'    => $booking->load(['flight.airline', 'flight.originAirport', 'flight.destinationAirport']),
        ], 201);
    }


    // -----------------------------------------------
    // GET /api/bookings/{id}
    // Get a specific booking (only owner can see it)
    // -----------------------------------------------
    public function show($id)
    {
        $booking = Booking::with(['flight.airline', 'flight.originAirport', 'flight.destinationAirport', 'passengers'])
            ->find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found.',
            ], 404);
        }

        // Authorization: only the owner can view their booking
        if ($booking->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view this booking.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data'    => $booking,
        ], 200);
    }


    // -----------------------------------------------
    // PATCH /api/bookings/{id}/cancel
    // Cancel a booking
    // -----------------------------------------------
    public function cancel($id)
    {
        $booking = Booking::find($id);

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
                'message' => 'You are not authorized to cancel this booking.',
            ], 403);
        }

        // Check it's not already cancelled
        if ($booking->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'This booking is already cancelled.',
            ], 422);
        }

        // Release the seats back to the flight
        $booking->flight->increment('available_seats', $booking->seat_count);

        // Update booking status
        $booking->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully.',
            'data'    => $booking,
        ], 200);
    }
}
