<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use App\Models\Driver;
use App\Models\PassengerTicket;
use App\Models\TerminalTrip;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $now = now();

        $totalTrips = TerminalTrip::count();

        $upcomingTrips = TerminalTrip::query()
            ->where('trip_status', 'scheduled')
            ->where('departure_time', '>=', $now)
            ->count();

        $bookedTickets = PassengerTicket::query()
            ->where('status', 'booked')
            ->count();

        $grossRevenue = (float) PassengerTicket::query()
            ->where('passenger_tickets.status', 'booked')
            ->join('terminal_trips', 'terminal_trips.id', '=', 'passenger_tickets.terminal_trip_id')
            ->sum('terminal_trips.ticket_price');

        $statusCounts = TerminalTrip::query()
            ->selectRaw('trip_status, COUNT(*) as status_count')
            ->groupBy('trip_status')
            ->pluck('status_count', 'trip_status');

        $tripStatuses = collect([
            ['key' => 'scheduled', 'label' => 'Scheduled', 'color' => 'blue'],
            ['key' => 'completed', 'label' => 'Completed', 'color' => 'green'],
            ['key' => 'departed', 'label' => 'Departed', 'color' => 'yellow'],
            ['key' => 'cancelled', 'label' => 'Cancelled', 'color' => 'red'],
        ])->map(function (array $status) use ($statusCounts, $totalTrips): array {
            $count = (int) ($statusCounts->get($status['key']) ?? 0);

            return [
                'label' => $status['label'],
                'count' => $count,
                'percentage' => $totalTrips > 0 ? (int) round($count / $totalTrips * 100) : 0,
                'color' => $status['color'],
            ];
        });

        $chartStart = $now->copy()->subDays(6)->startOfDay();
        $revenueByDate = PassengerTicket::query()
            ->where('passenger_tickets.status', 'booked')
            ->where('passenger_tickets.created_at', '>=', $chartStart)
            ->join('terminal_trips', 'terminal_trips.id', '=', 'passenger_tickets.terminal_trip_id')
            ->selectRaw('DATE(passenger_tickets.created_at) as date, SUM(terminal_trips.ticket_price) as total')
            ->groupByRaw('DATE(passenger_tickets.created_at)')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $revenueData = collect(range(6, 0))
            ->map(function (int $daysAgo) use ($now, $revenueByDate): array {
                $date = $now->copy()->subDays($daysAgo);
                $revenue = $revenueByDate->get($date->toDateString());

                return [
                    'date' => $date->format('D'),
                    'total' => (float) ($revenue->total ?? 0),
                ];
            })
            ->all();

        $departures = TerminalTrip::query()
            ->where('trip_status', 'scheduled')
            ->where('departure_time', '>=', $now)
            ->with(['bus.busCompany', 'driver'])
            ->withCount([
                'passengerTickets as booked_tickets_count' => fn ($query) => $query
                    ->where('status', 'booked'),
            ])
            ->orderBy('departure_time')
            ->limit(5)
            ->get()
            ->map(fn (TerminalTrip $trip): array => [
                'id' => $trip->id,
                'route_origin' => $trip->route_origin,
                'route_destination' => $trip->route_destination,
                'departure_date' => $trip->departure_time->format('M d, Y'),
                'departure_time' => $trip->departure_time->format('h:i A'),
                'status' => ucfirst($trip->trip_status),
                'bus_plate' => $trip->bus->bus_plate,
                'company_name' => $trip->bus->busCompany->company_name,
                'driver_name' => $trip->driver->driver_name,
                'booked_seats' => (int) $trip->booked_tickets_count,
                'total_seats' => $trip->bus->total_seats,
            ]);

        $recentBookings = PassengerTicket::query()
            ->with('terminalTrip:id,ticket_price,terminal_trip_id,route_origin,route_destination')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (PassengerTicket $ticket): array => [
                'id' => $ticket->id,
                'amount' => (float) $ticket->terminalTrip->ticket_price,
                'passenger' => $ticket->passenger_name,
                'route' => $ticket->terminalTrip->route_origin.' - '.$ticket->terminalTrip->route_destination,
                'status' => ucfirst($ticket->status),
                'created_at' => $ticket->created_at->format('M j, Y'),
            ]);

        return Inertia::render('dashboard', [
            'totalTrips' => $totalTrips,
            'upcomingTrips' => $upcomingTrips,
            'bookedTickets' => $bookedTickets,
            'grossRevenue' => $grossRevenue,
            'completedTrips' => (int) ($statusCounts->get('completed') ?? 0),
            'inspectedBuses' => Bus::query()
                ->where('is_inspected', true)
                ->count(),
            'availableBuses' => Bus::query()
                ->where('is_inspected', true)
                ->whereDoesntHave('terminalTrips', fn ($query) => $query
                    ->where('trip_status', 'scheduled')
                    ->where('departure_time', '>=', $now))
                ->count(),
            'activeDrivers' => Driver::query()
                ->where('status', 'active')
                ->count(),
            'totalPassengers' => PassengerTicket::query()
                ->distinct()
                ->count('passenger_name'),
            'tripStatuses' => $tripStatuses,
            'revenueData' => $revenueData,
            'departures' => $departures,
            'recentBookings' => $recentBookings,
        ]);
    }
}
