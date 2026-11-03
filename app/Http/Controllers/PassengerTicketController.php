<?php

namespace App\Http\Controllers;

use App\Models\PassengerTicket;
use App\Models\TerminalTrip;

use App\Http\Requests\StorePassengerTicketRequest;
use App\Http\Requests\UpdatePassengerTicketRequest;

class PassengerTicketController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Display Passenger Tickets
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $tickets = PassengerTicket::with([
            'terminalTrip.bus',
            'terminalTrip.driver'
        ])
            ->latest()
            ->paginate(10);

        return view('passenger-tickets.index', compact('tickets'));
    }

    /*
    |--------------------------------------------------------------------------
    | Create Ticket Form
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $trips = TerminalTrip::with('bus')
            ->where('trip_status', 'scheduled')
            ->get();

        return view('passenger-tickets.create', compact('trips'));
    }

    /*
    |--------------------------------------------------------------------------
    | Store Passenger Ticket
    |--------------------------------------------------------------------------
    */

    public function store(StorePassengerTicketRequest $request)
    {
        PassengerTicket::create($request->validated());

        return redirect()
            ->route('passenger-tickets.index')
            ->with('success', 'Passenger ticket successfully created.');
    }

    /*
    |--------------------------------------------------------------------------
    | Update Passenger Ticket
    |--------------------------------------------------------------------------
    */

    public function update(
        UpdatePassengerTicketRequest $request,
        $id
    ) {
        $ticket = PassengerTicket::findOrFail((int) $id);
        $ticket->update($request->validated());

        return redirect()
            ->route('passenger-tickets.index')
            ->with('success', 'Passenger ticket successfully updated.');
    }
}