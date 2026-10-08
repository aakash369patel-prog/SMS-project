<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransportRoute extends Model
{
    protected $fillable = [
        'name', 'code', 'start_point', 'end_point', 'morning_departure', 'afternoon_departure',
    ];

    public function buses(): HasMany
    {
        return $this->hasMany(Bus::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(StudentTransportAssignment::class);
    }
}
