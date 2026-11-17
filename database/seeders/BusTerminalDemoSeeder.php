<?php

namespace Database\Seeders;

use App\Models\Bus;
use App\Models\BusCompany;
use App\Models\Driver;
use App\Models\PassengerTicket;
use App\Models\TerminalTrip;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusTerminalDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $company = BusCompany::updateOrCreate(
                ['company_name' => 'Demo Municipal Transit'],
                ['contact_number' => '000-000-0000']
            );

            $buses = [];
            $drivers = [];

            foreach (range(1, 5) as $number) {
                $plate = sprintf('DEMO-BUS-%03d', $number);
                $license = sprintf('DEMO-LIC-%03d', $number);

                $buses[$plate] = Bus::updateOrCreate(
                    ['bus_plate' => $plate],
                    [
                        'bus_company_id' => $company->id,
                        'total_seats' => 30,
                        'is_inspected' => true,
                    ]
                );

                $drivers[$license] = Driver::updateOrCreate(
                    ['license_number' => $license],
                    [
                        'driver_name' => "Demo Driver {$number}",
                        'status' => 'active',
                    ]
                );
            }

            $trips = [
                [
                    'bus' => 'DEMO-BUS-001',
                    'driver' => 'DEMO-LIC-001',
                    'origin' => 'Cebu City',
                    'destination' => 'Danao City',
                    'days' => 1,
                    'hour' => 8,
                    'price' => 250,
                    'status' => 'scheduled',
                    'tickets' => [
                        ['name' => 'Demo Passenger 01', 'seat' => 1, 'status' => 'booked'],
                        ['name' => 'Demo Passenger 02', 'seat' => 2, 'status' => 'booked'],
                        ['name' => 'Demo Passenger 03', 'seat' => 3, 'status' => 'cancelled'],
                    ],
                ],
                [
                    'bus' => 'DEMO-BUS-002',
                    'driver' => 'DEMO-LIC-002',
                    'origin' => 'Cebu City',
                    'destination' => 'Bogo City',
                    'days' => 2,
                    'hour' => 9,
                    'price' => 350,
                    'status' => 'scheduled',
                    'tickets' => [
                        ['name' => 'Demo Passenger 04', 'seat' => 1, 'status' => 'booked'],
                        ['name' => 'Demo Passenger 05', 'seat' => 2, 'status' => 'booked'],
                    ],
                ],
                [
                    'bus' => 'DEMO-BUS-003',
                    'driver' => 'DEMO-LIC-003',
                    'origin' => 'Cebu City',
                    'destination' => 'Carcar City',
                    'days' => -1,
                    'hour' => 10,
                    'price' => 180,
                    'status' => 'departed',
                    'tickets' => [
                        ['name' => 'Demo Passenger 06', 'seat' => 1, 'status' => 'booked'],
                        ['name' => 'Demo Passenger 07', 'seat' => 2, 'status' => 'booked'],
                    ],
                ],
                [
                    'bus' => 'DEMO-BUS-004',
                    'driver' => 'DEMO-LIC-004',
                    'origin' => 'Cebu City',
                    'destination' => 'Toledo City',
                    'days' => -2,
                    'hour' => 11,
                    'price' => 220,
                    'status' => 'completed',
                    'tickets' => [
                        ['name' => 'Demo Passenger 08', 'seat' => 1, 'status' => 'booked'],
                        ['name' => 'Demo Passenger 09', 'seat' => 2, 'status' => 'booked'],
                    ],
                ],
                [
                    'bus' => 'DEMO-BUS-005',
                    'driver' => 'DEMO-LIC-005',
                    'origin' => 'Cebu City',
                    'destination' => 'Argao',
                    'days' => -3,
                    'hour' => 13,
                    'price' => 200,
                    'status' => 'cancelled',
                    'tickets' => [
                        ['name' => 'Demo Passenger 10', 'seat' => 1, 'status' => 'cancelled'],
                    ],
                ],
            ];

            foreach ($trips as $tripData) {
                $trip = TerminalTrip::updateOrCreate(
                    [
                        'bus_id' => $buses[$tripData['bus']]->id,
                        'route_origin' => $tripData['origin'],
                        'route_destination' => $tripData['destination'],
                    ],
                    [
                        'driver_id' => $drivers[$tripData['driver']]->id,
                        'departure_time' => now()
                            ->startOfDay()
                            ->addDays($tripData['days'])
                            ->setTime($tripData['hour'], 0),
                        'ticket_price' => $tripData['price'],
                        'trip_status' => $tripData['status'],
                    ]
                );

                foreach ($tripData['tickets'] as $ticketData) {
                    PassengerTicket::updateOrCreate(
                        [
                            'terminal_trip_id' => $trip->id,
                            'seat_number' => $ticketData['seat'],
                        ],
                        [
                            'passenger_name' => $ticketData['name'],
                            'passenger_email' => strtolower(str_replace(' ', '.', $ticketData['name'])).'@example.test',
                            'status' => $ticketData['status'],
                        ]
                    );
                }
            }
        });
    }
}
