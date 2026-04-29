<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Flight\StoreFlightRequest;
use App\Http\Requests\Flight\UpdateFlightRequest;
use App\Models\Flight;
use Illuminate\Http\Request;

class FlightController extends Controller
{
    
    // -----------------------------------------------
    // PUBLIC: GET /api/flights
    // List all available (scheduled) flights
    // -----------------------------------------------
    public function index()
    {
        $flights = Flight::with(['airline', 'originAirport', 'destinationAirport'])
            ->where('status', 'scheduled')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $flights,
        ], 200);
    }



    // -----------------------------------------------
    // PUBLIC: GET /api/flights/search
    // Search flights by origin, destination, date
    // -----------------------------------------------
    public function search(Request $request)
    {
        $request->validate([
            'origin'      => ['nullable', 'string'],
            'destination' => ['nullable', 'string'],
            'date'        => ['nullable', 'date'],
            'min_price'   => ['nullable', 'numeric', 'min:0'],
            'max_price'   => ['nullable', 'numeric', 'min:0'],
        ]);

        $query = Flight::with(['airline', 'originAirport', 'destinationAirport'])
            ->where('status', 'scheduled');

        // Filter by origin city or airport code
        if ($request->filled('origin')) {
            $query->whereHas('originAirport', function ($q) use ($request) {
                $q->where('city', 'like', '%' . $request->origin . '%')
                  ->orWhere('code', 'like', '%' . $request->origin . '%');
            });
        }

        // Filter by destination city or airport code
        if ($request->filled('destination')) {
            $query->whereHas('destinationAirport', function ($q) use ($request) {
                $q->where('city', 'like', '%' . $request->destination . '%')
                  ->orWhere('code', 'like', '%' . $request->destination . '%');
            });
        }

        // Filter by departure date
        if ($request->filled('date')) {
            $query->whereDate('departure_time', $request->date);
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $flights = $query->get();

        return response()->json([
            'success' => true,
            'data'    => $flights,
        ], 200);
    }



    // -----------------------------------------------
    // PUBLIC: GET /api/flights/{id}
    // Get a specific flight
    // -----------------------------------------------
    public function show($id)
    {
        $flight = Flight::with(['airline', 'originAirport', 'destinationAirport'])
            ->find($id);

        if (!$flight) {
            return response()->json([
                'success' => false,
                'message' => 'Flight not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $flight,
        ], 200);
    }


    // -----------------------------------------------
    // ADMIN: POST /api/admin/flights
    // Create a new flight
    // -----------------------------------------------
    public function store(StoreFlightRequest $request)
    {
        $flight = Flight::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Flight created successfully.',
            'data'    => $flight->load(['airline', 'originAirport', 'destinationAirport']),
        ], 201);
    }



    // -----------------------------------------------
    // ADMIN: PUT /api/admin/flights/{id}
    // Update a flight
    // -----------------------------------------------
    public function update(UpdateFlightRequest $request, $id)
    {
        $flight = Flight::find($id);

        if (!$flight) {
            return response()->json([
                'success' => false,
                'message' => 'Flight not found.',
            ], 404);
        }

        $flight->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Flight updated successfully.',
            'data'    => $flight->load(['airline', 'originAirport', 'destinationAirport']),
        ], 200);
    }

    

    // -----------------------------------------------
    // ADMIN: DELETE /api/admin/flights/{id}
    // Delete a flight
    // -----------------------------------------------
    public function destroy($id)
    {
        $flight = Flight::find($id);

        if (!$flight) {
            return response()->json([
                'success' => false,
                'message' => 'Flight not found.',
            ], 404);
        }

        $flight->delete();

        return response()->json([
            'success' => true,
            'message' => 'Flight deleted successfully.',
        ], 200);
    }

}
