<?php

namespace Tests\Feature;

use App\Models\Bus;
use App\Models\BusCompany;
use App\Models\Driver;
use App\Models\User;
use App\Services\PassengerTicketService;
use App\Services\TerminalTripService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BusTerminalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_trip_dispatch_sanitizes_routes_and_reports_derived_revenue(): void
    {
        [$bus, $driver] = $this->makeDispatchResources();
        $trip = app(TerminalTripService::class)->create([
            'bus_id' => $bus->id,
            'driver_id' => $driver->id,
            'route_origin' => ' <b>Central</b> ',
            'route_destination' => ' North Station ',
            'departure_time' => now()->addDay()->toDateTimeString(),
            'ticket_price' => 12.50,
        ]);

        app(PassengerTicketService::class)->create([
            'terminal_trip_id' => $trip->id,
            'passenger_name' => ' <b>Alex Rider</b> ',
            'seat_number' => 1,
        ]);
        app(PassengerTicketService::class)->create([
            'terminal_trip_id' => $trip->id,
            'passenger_name' => 'Jamie Lee',
            'seat_number' => 2,
        ]);

        $trip->refresh()->load('passengerTickets');

        $this->assertSame('Central', $trip->route_origin);
        $this->assertSame('North Station', $trip->route_destination);
        $this->assertSame('scheduled', $trip->trip_status);
        $this->assertSame('AB 1234', $bus->refresh()->bus_plate);
        $this->assertSame(2, $trip->booked_seats);
        $this->assertSame(25.0, $trip->gross_revenue);
        $this->assertSame('Alex Rider', $trip->passengerTickets->first()->passenger_name);
    }

    public function test_suspended_drivers_cannot_be_assigned_to_trips(): void
    {
        [$bus, $driver] = $this->makeDispatchResources();
        $driver->update(['status' => 'suspended']);

        $this->expectException(ValidationException::class);

        app(TerminalTripService::class)->create($this->tripData($bus, $driver));
    }

    public function test_uninspected_buses_cannot_be_assigned_to_trips(): void
    {
        [$bus, $driver] = $this->makeDispatchResources();
        $bus->update(['is_inspected' => false]);

        $this->expectException(ValidationException::class);

        app(TerminalTripService::class)->create($this->tripData($bus, $driver));
    }

    public function test_ticket_service_rejects_duplicate_and_over_capacity_bookings(): void
    {
        [$bus, $driver] = $this->makeDispatchResources(totalSeats: 1);
        $trip = app(TerminalTripService::class)->create($this->tripData($bus, $driver));
        $ticketService = app(PassengerTicketService::class);

        $ticketService->create([
            'terminal_trip_id' => $trip->id,
            'passenger_name' => 'First Passenger',
            'seat_number' => 1,
        ]);

        try {
            $ticketService->create([
                'terminal_trip_id' => $trip->id,
                'passenger_name' => 'Second Passenger',
                'seat_number' => 1,
            ]);
            $this->fail('A duplicate seat should not be booked.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('seat_number', $exception->errors());
        }

        try {
            $ticketService->create([
                'terminal_trip_id' => $trip->id,
                'passenger_name' => 'Third Passenger',
                'seat_number' => 2,
            ]);
            $this->fail('A ticket should not exceed bus capacity.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('seat_number', $exception->errors());
        }
    }

    public function test_trip_and_ticket_endpoints_require_authentication_and_return_paginated_data(): void
    {
        $this->get(route('terminal-trips.index'))->assertRedirect('/login');
        $this->get(route('passenger-tickets.index'))->assertRedirect('/login');

        $this->actingAs(User::factory()->create());

        $this->getJson(route('terminal-trips.index'))
            ->assertOk()
            ->assertJsonStructure(['data', 'current_page', 'per_page']);
        $this->getJson(route('passenger-tickets.index'))
            ->assertOk()
            ->assertJsonStructure(['data', 'current_page', 'per_page']);
    }

    private function makeDispatchResources(int $totalSeats = 2): array
    {
        $company = BusCompany::create(['company_name' => 'City Bus Co']);
        $bus = Bus::create([
            'bus_company_id' => $company->id,
            'bus_plate' => ' ab 1234 ',
            'total_seats' => $totalSeats,
            'is_inspected' => true,
        ]);
        $driver = Driver::create([
            'driver_name' => 'Morgan Driver',
            'license_number' => 'LIC-1001',
            'status' => 'active',
        ]);

        return [$bus, $driver];
    }

    private function tripData(Bus $bus, Driver $driver): array
    {
        return [
            'bus_id' => $bus->id,
            'driver_id' => $driver->id,
            'route_origin' => 'Central',
            'route_destination' => 'North Station',
            'departure_time' => now()->addDay()->toDateTimeString(),
            'ticket_price' => 12.50,
        ];
    }
}
