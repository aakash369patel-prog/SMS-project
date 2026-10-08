<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentTransportAssignment extends Model
{
    protected $fillable = [
        'student_id', 'transport_route_id', 'bus_id', 'pickup_point', 'drop_point', 'trip_type',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(TransportRoute::class, 'transport_route_id');
    }

    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }
}
