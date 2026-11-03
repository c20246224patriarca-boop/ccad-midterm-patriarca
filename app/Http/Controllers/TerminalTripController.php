<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use App\Models\Driver;
use App\Models\TerminalTrip;

use Illuminate\Contracts\Container\Container;

use App\Http\Requests\StoreTerminalTripRequest;
use App\Http\Requests\UpdateTerminalTripRequest;

class TerminalTripController extends Controller
{
    protected mixed $service;

    public function __construct(Container $container)
    {
        $this->service = $container->make('App\\Services\\TerminalDispatchService');
    }

    /*
    |--------------------------------------------------------------------------
    | Display All Trips
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $trips = TerminalTrip::with([
            'bus.busCompany',
            'driver',
            'passengerTickets'
        ])
            ->latest()
            ->paginate(10);

        return view('terminal-trips.index', compact('trips'));
    }

    /*
    |--------------------------------------------------------------------------
    | Create Trip Form
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $buses = Bus::with('busCompany')
            ->where('is_inspected', true)
            ->get();

        $drivers = Driver::where('status', 'active')
            ->get();

        return view(
            'terminal-trips.create',
            compact('buses', 'drivers')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Store New Trip
    |--------------------------------------------------------------------------
    */

    public function store(StoreTerminalTripRequest $request)
    {
        $trip = $this->service->createTrip(
            $request->validated()
        );

        return redirect()
            ->route('terminal-trips.index')
            ->with('success', 'Trip successfully created.');
    }

    /*
    |--------------------------------------------------------------------------
    | Update Existing Trip
    |--------------------------------------------------------------------------
    */

    public function update(
        UpdateTerminalTripRequest $request,
        $id
    ) {
        $this->service->updateTrip(
            $request->validated(),
            (int) $id
        );

        return redirect()
            ->route('terminal-trips.index')
            ->with('success', 'Trip successfully updated.');
    }
}