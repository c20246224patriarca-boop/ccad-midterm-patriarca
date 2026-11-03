<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bus extends Model
{
    protected $fillable = [
        'bus_company_id',
        'bus_plate',
        'total_seats',
        'is_inspected',
    ];

    protected function casts(): array
    {
        return [
            'is_inspected' => 'boolean',
            'total_seats' => 'integer',
        ];
    }

    public function busCompany(): BelongsTo
    {
        return $this->belongsTo(BusCompany::class);
    }

    public function terminalTrips(): HasMany
    {
        return $this->hasMany(TerminalTrip::class);
    }
}