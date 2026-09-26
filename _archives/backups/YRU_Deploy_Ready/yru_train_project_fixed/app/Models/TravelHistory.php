<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelHistory extends Model {
    protected $table = 'travel_histories';

    protected $fillable = [
        'skytrain_code',
        'driver_id',
        'route_code',
        'start_time',
        'end_time',
        'distance_km',
        'travel_status',
    ];

    public function electricTrain(): BelongsTo {
        return $this->belongsTo(ElectricTrain::class, 'skytrain_code', 'skytrain_code');
    }

    public function driver(): BelongsTo {
        return $this->belongsTo(User::class, 'driver_id', 'user_id');
    }

    public function route(): BelongsTo {
        return $this->belongsTo(Route::class, 'route_code', 'route_code');
    }
}
