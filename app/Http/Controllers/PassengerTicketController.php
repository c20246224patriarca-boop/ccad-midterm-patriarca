<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePassengerTicketRequest;
use App\Http\Requests\UpdatePassengerTicketRequest;
use App\Models\PassengerTicket;
use App\Models\TerminalTrip;
use App\Services\PassengerTicketService;
use Illuminate\Http\JsonResponse;

class PassengerTicketController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            PassengerTicket::query()
                ->with('terminalTrip.bus.busCompany', 'terminalTrip.driver')
                ->latest()
                ->paginate(15)
        );
    }

    public function create(): JsonResponse
    {
        return response()->json([
            'trips' => TerminalTrip::query()
                ->where('trip_status', 'scheduled')
                ->with(['bus.busCompany', 'driver'])
                ->orderBy('departure_time')
                ->get(),
        ]);
    }

    public function store(
        StorePassengerTicketRequest $request,
        PassengerTicketService $service
    ): JsonResponse {
        $ticket = $service->create($request->validated());

        return response()->json($ticket, 201);
    }

    public function update(
        UpdatePassengerTicketRequest $request,
        int $id,
        PassengerTicketService $service
    ): JsonResponse {
        return response()->json($service->update($id, $request->validated()));
    }
}
