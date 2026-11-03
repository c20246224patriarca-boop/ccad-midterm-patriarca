<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    protected $fillable = [
        'driver_name',
        'license_number',
        'status',
    ];

    public function terminalTrips(): HasMany
    {
        return $this->hasMany(TerminalTrip::class);
    }
}