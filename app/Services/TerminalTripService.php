<?php

namespace App\Services;

use App\Models\Bus;
use App\Models\Driver;
use App\Models\TerminalTrip;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TerminalTripService
{
    public function create(array $data): TerminalTrip
    {
        return DB::transaction(function () use ($data): TerminalTrip {
            $data = $this->cleanTextFields($data);
            $data['trip_status'] = $data['trip_status'] ?? 'scheduled';
            $bus = Bus::query()->lockForUpdate()->findOrFail($data['bus_id']);
            $driver = Driver::query()->lockForUpdate()->findOrFail($data['driver_id']);

            $this->assertDispatchAllowed($bus, $driver);
            $this->assertDifferentLocations($data['route_origin'], $data['route_destination']);

            $trip = TerminalTrip::create($data);

            return $trip->load(['bus.busCompany', 'driver', 'passengerTickets']);
        });
    }

    public function update(int $id, array $data): TerminalTrip
    {
        return DB::transaction(function () use ($id, $data): TerminalTrip {
            $trip = TerminalTrip::query()->lockForUpdate()->findOrFail($id);
            $data = $this->cleanTextFields($data);

            $bus = Bus::query()
                ->lockForUpdate()
                ->findOrFail($data['bus_id'] ?? $trip->bus_id);
            $driver = Driver::query()
                ->lockForUpdate()
                ->findOrFail($data['driver_id'] ?? $trip->driver_id);

            $this->assertDispatchAllowed($bus, $driver);
            $this->assertDifferentLocations(
                $data['route_origin'] ?? $trip->route_origin,
                $data['route_destination'] ?? $trip->route_destination
            );
            $this->assertExistingBookingsFit($trip, $bus);

            $trip->update($data);

            return $trip->refresh()->load(['bus.busCompany', 'driver', 'passengerTickets']);
        });
    }

    public function availableBuses(): Collection
    {
        return Bus::query()
            ->where('is_inspected', true)
            ->with('busCompany')
            ->orderBy('bus_plate')
            ->get();
    }

    public function availableDrivers(): Collection
    {
        return Driver::query()
            ->where('status', 'active')
            ->orderBy('driver_name')
            ->get();
    }

    private function cleanTextFields(array $data): array
    {
        foreach (['route_origin', 'route_destination'] as $field) {
            if (isset($data[$field])) {
                $data[$field] = trim(strip_tags($data[$field]));
            }
        }

        return $data;
    }

    private function assertDispatchAllowed(Bus $bus, Driver $driver): void
    {
        if (! $bus->is_inspected) {
            throw ValidationException::withMessages([
                'bus_id' => 'An uninspected bus cannot be assigned to a trip.',
            ]);
        }

        if ($driver->status !== 'active') {
            throw ValidationException::withMessages([
                'driver_id' => 'A suspended driver cannot be assigned to a trip.',
            ]);
        }
    }

    private function assertDifferentLocations(string $origin, string $destination): void
    {
        if (strtolower($origin) === strtolower($destination)) {
            throw ValidationException::withMessages([
                'route_destination' => 'Origin and destination must be different.',
            ]);
        }
    }

    private function assertExistingBookingsFit(TerminalTrip $trip, Bus $bus): void
    {
        $bookedTickets = $trip->passengerTickets()
            ->where('status', 'booked')
            ->get(['seat_number']);

        if ($bookedTickets->count() > $bus->total_seats) {
            throw ValidationException::withMessages([
                'bus_id' => 'The selected bus does not have enough seats for existing bookings.',
            ]);
        }

        if ($bookedTickets->contains(fn ($ticket) => $ticket->seat_number > $bus->total_seats)) {
            throw ValidationException::withMessages([
                'bus_id' => 'The selected bus capacity is below an already-booked seat number.',
            ]);
        }
    }
}
