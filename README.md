# Municipal Bus Terminal Dispatch & Ticketing

## Normalized domain schema

The dispatch system uses five domain tables:

- `bus_companies` stores each operator once; `buses` references its company.
- `drivers` stores driver identity and active/suspended status.
- `terminal_trips` references one bus and driver and stores route, departure, fare, and status.
- `passenger_tickets` references a trip and stores passenger and seat details.

The bus plate, driver, and company details are not repeated on trips. The trip fare is stored once on `terminal_trips`; `booked_seats` and `gross_revenue` are derived from booked tickets and the trip fare rather than duplicated columns.

## Dispatch and booking rules

`TerminalTripService` and `PassengerTicketService` wrap writes in database transactions, sanitize text input, and enforce dispatch eligibility, seat-number limits, unique active seat bookings, and bus capacity. Bus plates are normalized to uppercase by the `Bus` model.

## Endpoints

Authenticated JSON endpoints are registered for `terminal-trips` and `passenger-tickets` in `routes/web.php`. Both provide paginated `index`, dependency-data `create`, `store`, and `update` actions.

## Run migrations and tests

```sh
php artisan migrate
php artisan test
```

## Populate the dashboard with demo data

After migrations, run the demo seeder to add repeatable sample companies, buses, drivers, trips, and tickets:

```sh
php artisan db:seed --class=BusTerminalDemoSeeder
```

The demo data is isolated by its `DEMO-` bus plates and can be seeded again without creating duplicate trips or tickets.
# ccad-midterm-patriarca1
