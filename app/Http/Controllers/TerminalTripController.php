<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TerminalTrip;

class TerminalTripController extends Controller
{
    // Display all terminal trips
    public function index()
    {
        $trips = TerminalTrip::all();

        return view('terminal_trips.index', compact('trips'));
    }

    // Show the create trip form
    public function create()
    {
        return view('terminal_trips.create');
    }

    // Store a new terminal trip
    public function store(Request $request)
    {
        $request->validate([
            'terminal_name' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'departure_time' => 'required|date',
            'fare' => 'required|numeric|min:0',
        ]);

        TerminalTrip::create([
            'terminal_name' => $request->terminal_name,
            'destination' => $request->destination,
            'departure_time' => $request->departure_time,
            'fare' => $request->fare,
        ]);

        return redirect()->route('terminal-trips.index')
            ->with('success', 'Terminal trip added successfully!');
    }

    // Display a specific trip
    public function show($id)
    {
        $trip = TerminalTrip::findOrFail($id);

        return view('terminal_trips.show', compact('trip'));
    }

    // Show the edit trip form
    public function edit($id)
    {
        $trip = TerminalTrip::findOrFail($id);

        return view('terminal_trips.edit', compact('trip'));
    }

    // Update trip information
    public function update(Request $request, $id)
    {
        $trip = TerminalTrip::findOrFail($id);

        $request->validate([
            'terminal_name' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'departure_time' => 'required|date',
            'fare' => 'required|numeric|min:0',
        ]);

        $trip->update($request->only([
            'terminal_name',
            'destination',
            'departure_time',
            'fare',
        ]));

        return redirect()->route('terminal-trips.index')
            ->with('success', 'Terminal trip updated successfully!');
    }

    // Delete a trip
    public function destroy($id)
    {
        $trip = TerminalTrip::findOrFail($id);

        $trip->delete();

        return redirect()->route('terminal-trips.index')
            ->with('success', 'Terminal trip deleted successfully!');
    }
}