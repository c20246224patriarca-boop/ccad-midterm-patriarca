<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusCompany extends Model
{
    protected $fillable = [
        'company_name',
        'contact_number',
    ];

    public function buses(): HasMany
    {
        return $this->hasMany(Bus::class);
    }
}