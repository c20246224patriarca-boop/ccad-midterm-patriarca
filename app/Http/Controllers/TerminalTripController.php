<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTerminalTripRequest;
use App\Http\Requests\UpdateTerminalTripRequest;
use App\Models\TerminalTrip;
use App\Services\TerminalTripService;
use Illuminate\Http\JsonResponse;

class TerminalTripController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            TerminalTrip::query()
                ->with(['bus.busCompany', 'driver', 'passengerTickets'])
                ->latest('departure_time')
                ->paginate(15)
        );
    }

    public function create(TerminalTripService $service): JsonResponse
    {
        return response()->json([
            'buses' => $service->availableBuses(),
            'drivers' => $service->availableDrivers(),
        ]);
    }

    public function store(
        StoreTerminalTripRequest $request,
        TerminalTripService $service
    ): JsonResponse {
        $trip = $service->create($request->validated());

        return response()->json($trip, 201);
    }

    public function update(
        UpdateTerminalTripRequest $request,
        int $id,
        TerminalTripService $service
    ): JsonResponse {
        return response()->json($service->update($id, $request->validated()));
    }
}
