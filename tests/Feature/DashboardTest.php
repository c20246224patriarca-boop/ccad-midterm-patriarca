<?php

namespace Tests\Feature;

use App\Models\Bus;
use App\Models\BusCompany;
use App\Models\Driver;
use App\Models\User;
use App\Services\PassengerTicketService;
use App\Services\TerminalTripService;
use Database\Seeders\BusTerminalDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $this->actingAs($user = User::factory()->create());

        $this->get('/dashboard')->assertOk();
    }

    public function test_dashboard_shows_dispatch_and_booking_data()
    {
        $this->actingAs(User::factory()->create());

        $company = BusCompany::create(['company_name' => 'Dashboard Bus Co']);
        $bus = Bus::create([
            'bus_company_id' => $company->id,
            'bus_plate' => 'DASH 100',
            'total_seats' => 30,
            'is_inspected' => true,
        ]);
        $driver = Driver::create([
            'driver_name' => 'Dashboard Driver',
            'license_number' => 'DASH-LIC-100',
            'status' => 'active',
        ]);
        $trip = app(TerminalTripService::class)->create([
            'bus_id' => $bus->id,
            'driver_id' => $driver->id,
            'route_origin' => 'Central Terminal',
            'route_destination' => 'North Terminal',
            'departure_time' => now()->addDay()->toDateTimeString(),
            'ticket_price' => 125,
        ]);
        app(PassengerTicketService::class)->create([
            'terminal_trip_id' => $trip->id,
            'passenger_name' => 'Dashboard Passenger',
            'seat_number' => 1,
        ]);

        $this->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('dashboard')
                ->where('totalTrips', 1)
                ->where('upcomingTrips', 1)
                ->where('bookedTickets', 1)
                ->where('grossRevenue', 125)
                ->where('completedTrips', 0)
                ->where('activeDrivers', 1)
                ->where('availableBuses', 0)
                ->where('totalPassengers', 1)
                ->has('departures', 1)
                ->where('departures.0.bus_plate', 'DASH 100')
                ->has('tripStatuses', 4)
                ->has('recentBookings', 1)
                ->where('recentBookings.0.passenger', 'Dashboard Passenger')
                ->where('recentBookings.0.route', 'Central Terminal - North Terminal')
                ->has('revenueData', 7)
                ->where('revenueData.6.total', 125)
            );
    }

    public function test_demo_seeder_populates_the_dashboard_and_can_run_repeatedly()
    {
        $this->actingAs(User::factory()->create());

        $this->seed(BusTerminalDemoSeeder::class);
        $this->seed(BusTerminalDemoSeeder::class);

        $this->assertDatabaseCount('terminal_trips', 5);
        $this->assertDatabaseCount('passenger_tickets', 10);

        $this->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('totalTrips', 5)
                ->where('upcomingTrips', 2)
                ->where('bookedTickets', 8)
                ->where('grossRevenue', 2000)
                ->where('completedTrips', 1)
                ->where('availableBuses', 3)
                ->where('totalPassengers', 10)
                ->has('departures', 2)
                ->where('tripStatuses.0.count', 2)
                ->where('tripStatuses.1.count', 1)
                ->where('tripStatuses.2.count', 1)
                ->where('tripStatuses.3.count', 1)
                ->has('recentBookings', 5)
            );
    }
}
