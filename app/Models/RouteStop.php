<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouteStop extends Model {
    protected $table = 'route_stops';

    protected $fillable = [
        'route_code',
        'parking_spot_code',
        'stop_order',
    ];

    public function route(): BelongsTo {
        return $this->belongsTo(Route::class, 'route_code', 'route_code');
    }

    public function station(): BelongsTo {
        return $this->belongsTo(Station::class, 'parking_spot_code', 'parking_spot_code');
    }
}
