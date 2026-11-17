<?php

namespace App\Services;

use App\Models\PassengerTicket;
use App\Models\TerminalTrip;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PassengerTicketService
{
    public function create(array $data): PassengerTicket
    {
        return DB::transaction(function () use ($data): PassengerTicket {
            $trip = TerminalTrip::query()
                ->lockForUpdate()
                ->with(['bus', 'driver'])
                ->findOrFail($data['terminal_trip_id']);

            $data = $this->cleanTextFields($data);
            $data['status'] = $data['status'] ?? 'booked';

            $this->assertTripCanBeBooked($trip);
            $this->assertSeatWithinCapacity($trip, $data['seat_number']);

            if ($data['status'] === 'booked') {
                $this->assertSeatIsAvailable($trip->id, $data['seat_number']);
                $this->assertAvailableBookingSlot($trip);
            }

            $ticket = PassengerTicket::create($data);

            return $ticket->load('terminalTrip.bus.busCompany', 'terminalTrip.driver');
        });
    }

    public function update(int $id, array $data): PassengerTicket
    {
        return DB::transaction(function () use ($id, $data): PassengerTicket {
            $ticket = PassengerTicket::query()->lockForUpdate()->findOrFail($id);
            $trip = TerminalTrip::query()
                ->lockForUpdate()
                ->with(['bus', 'driver'])
                ->findOrFail($ticket->terminal_trip_id);

            $data = $this->cleanTextFields($data);
            $status = $data['status'] ?? $ticket->status;
            $seatNumber = $data['seat_number'] ?? $ticket->seat_number;

            $this->assertSeatWithinCapacity($trip, $seatNumber);

            if ($status === 'booked') {
                $this->assertTripCanBeBooked($trip);
                $this->assertAvailableBookingSlot($trip, $ticket->id);
                $this->assertSeatIsAvailable($trip->id, $seatNumber, $ticket->id);
            }

            $ticket->update($data);

            return $ticket->refresh()->load('terminalTrip.bus.busCompany', 'terminalTrip.driver');
        });
    }

    private function cleanTextFields(array $data): array
    {
        foreach (['passenger_name', 'passenger_email'] as $field) {
            if (isset($data[$field])) {
                $data[$field] = trim(strip_tags($data[$field]));
            }
        }

        return $data;
    }

    private function assertTripCanBeBooked(TerminalTrip $trip): void
    {
        if (! $trip->bus->is_inspected) {
            throw ValidationException::withMessages([
                'terminal_trip_id' => 'Tickets cannot be booked on a trip using an uninspected bus.',
            ]);
        }

        if ($trip->driver->status !== 'active') {
            throw ValidationException::withMessages([
                'terminal_trip_id' => 'Tickets cannot be booked on a trip assigned to a suspended driver.',
            ]);
        }
    }

    private function assertSeatWithinCapacity(TerminalTrip $trip, int $seatNumber): void
    {
        if ($seatNumber > $trip->bus->total_seats) {
            throw ValidationException::withMessages([
                'seat_number' => 'Seat number cannot exceed the bus capacity.',
            ]);
        }
    }

    private function assertAvailableBookingSlot(TerminalTrip $trip, ?int $exceptTicketId = null): void
    {
        $bookedCount = $trip->passengerTickets()
            ->where('status', 'booked')
            ->when($exceptTicketId, fn ($query) => $query->whereKeyNot($exceptTicketId))
            ->count();

        if ($bookedCount >= $trip->bus->total_seats) {
            throw ValidationException::withMessages([
                'seat_number' => 'This trip has no remaining seats.',
            ]);
        }
    }

    private function assertSeatIsAvailable(
        int $tripId,
        int $seatNumber,
        ?int $exceptTicketId = null
    ): void {
        $seatIsBooked = PassengerTicket::query()
            ->where('terminal_trip_id', $tripId)
            ->where('seat_number', $seatNumber)
            ->where('status', 'booked')
            ->when($exceptTicketId, fn ($query) => $query->whereKeyNot($exceptTicketId))
            ->exists();

        if ($seatIsBooked) {
            throw ValidationException::withMessages([
                'seat_number' => 'This seat is already booked for the selected trip.',
            ]);
        }
    }
}
