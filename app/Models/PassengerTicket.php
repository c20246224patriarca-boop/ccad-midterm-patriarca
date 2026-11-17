<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PassengerTicket extends Model
{
    protected $fillable = [
        'terminal_trip_id',
        'passenger_name',
        'passenger_email',
        'seat_number',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'seat_number' => 'integer',
        ];
    }

    public function terminalTrip(): BelongsTo
    {
        return $this->belongsTo(TerminalTrip::class);
    }
}
