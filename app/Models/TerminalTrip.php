<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TerminalTrip extends Model
{
    protected $fillable = [
        'bus_id',
        'driver_id',
        'route_origin',
        'route_destination',
        'departure_time',
        'ticket_price',
        'trip_status',
    ];

    protected function casts(): array
    {
        return [
            'departure_time' => 'datetime',
            'ticket_price' => 'decimal:2',
        ];
    }

    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function passengerTickets(): HasMany
    {
        return $this->hasMany(PassengerTicket::class);
    }

    public function getBookedSeatsAttribute(): int
    {
        return $this->passengerTickets()
            ->where('status', 'booked')
            ->count();
    }

    public function getGrossRevenueAttribute(): float
    {
        return round(
            $this->booked_seats * (float) $this->ticket_price,
            2
        );
    }
}