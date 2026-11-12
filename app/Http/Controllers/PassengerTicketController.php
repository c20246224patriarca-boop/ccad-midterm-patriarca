<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PassengerTicket;

class PassengerTicketController extends Controller
{
    // Display all passenger tickets
    public function index()
    {
        $tickets = PassengerTicket::all();

        return view('passenger_tickets.index', compact('tickets'));
    }

    // Show the ticket booking form
    public function create()
    {
        return view('passenger_tickets.create');
    }

    // Store a new passenger ticket
    public function store(Request $request)
    {
        $request->validate([
            'passenger_name' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'travel_date' => 'required|date',
            'ticket_price' => 'required|numeric|min:0',
        ]);

        PassengerTicket::create([
            'passenger_name' => $request->passenger_name,
            'destination' => $request->destination,
            'travel_date' => $request->travel_date,
            'ticket_price' => $request->ticket_price,
        ]);

        return redirect()->route('passenger-tickets.index')
            ->with('success', 'Passenger ticket booked successfully!');
    }

    // Display a specific ticket
    public function show($id)
    {
        $ticket = PassengerTicket::findOrFail($id);

        return view('passenger_tickets.show', compact('ticket'));
    }

    // Show the edit form
    public function edit($id)
    {
        $ticket = PassengerTicket::findOrFail($id);

        return view('passenger_tickets.edit', compact('ticket'));
    }

    // Update ticket information
    public function update(Request $request, $id)
    {
        $ticket = PassengerTicket::findOrFail($id);

        $request->validate([
            'passenger_name' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'travel_date' => 'required|date',
            'ticket_price' => 'required|numeric|min:0',
        ]);

        $ticket->update($request->only([
            'passenger_name',
            'destination',
            'travel_date',
            'ticket_price',
        ]));

        return redirect()->route('passenger-tickets.index')
            ->with('success', 'Ticket updated successfully!');
    }

    // Delete a ticket
    public function destroy($id)
    {
        $ticket = PassengerTicket::findOrFail($id);

        $ticket->delete();

        return redirect()->route('passenger-tickets.index')
            ->with('success', 'Ticket deleted successfully!');
    }
}