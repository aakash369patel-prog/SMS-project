<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bus extends Model
{
    protected $fillable = [
        'transport_route_id', 'registration_number', 'capacity', 'driver_name', 'driver_phone',
    ];

    public function route(): BelongsTo
    {
        return $this->belongsTo(TransportRoute::class, 'transport_route_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(StudentTransportAssignment::class);
    }
}
