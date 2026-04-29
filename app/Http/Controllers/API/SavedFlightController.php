<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Flight;
use Illuminate\Http\Request;

class SavedFlightController extends Controller
{
    // --------------------------------------------------
    // GET /api/saved-flights
    // List all saved flights for the authenticated user 
    // --------------------------------------------------
    public function index(Request $request)
    {
        $savedFlights = $request->user()
            ->savedFlights()
            ->with(['airline', 'originAirport', 'destinationAirport'])
            ->get();

        // If the user hasn't saved any flights yet
        if ($savedFlights->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => "You haven't saved any flight yet.",
                'data'    => [],
            ], 200);
        }

        // Normal response with the list
        return response()->json([
            'success' => true,
            'data'    => $savedFlights,
        ], 200);
    }


    // --------------------------------------------------
    // POST /api/flights/{id}/save
    // Save a specific flight
    // --------------------------------------------------
    public function save(Request $request, $id)
    {
        $flight = Flight::with(['airline', 'originAirport', 'destinationAirport'])
                        ->find($id);

        if (!$flight) {
            return response()->json([
                'success' => false,
                'message' => 'Flight not found.',
            ], 404);
        }

        $user = $request->user();
        $alreadySaved = $user->savedFlights()->where('flight_id', $flight->id)->exists();
        if ($alreadySaved) {
            return response()->json([
                'success' => true,
                'message' => 'Flight is already in your saved list.',
                'data'    => $flight,
            ], 200);
        }
        
        $user->savedFlights()->attach($flight->id);// Not saved yet → attach it

        return response()->json([
            'success' => true,
            'message' => 'Flight saved successfully.',
            'data'    => $flight,
        ], 200);
    }


    // --------------------------------------------------
    // DELETE /api/flights/{id}/unsave
    // Delete a specific flight
    // --------------------------------------------------
    public function unsave(Request $request, $id)
    {
        $flight = Flight::find($id);

        if (!$flight) {
            return response()->json([
                'success' => false,
                'message' => 'Flight not found.',
            ], 404);
        }

        $request->user()->savedFlights()->detach($flight->id);

        return response()->json([
            'success' => true,
            'message' => 'Flight removed from saved list.',
        ], 200);
    }
}