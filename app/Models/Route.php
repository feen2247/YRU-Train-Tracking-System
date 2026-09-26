<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Route extends Model {
    protected $table = 'routes';
    protected $primaryKey = 'route_code';
    public $incrementing = false; // String primary key
    protected $keyType = 'string';

    protected $fillable = [
        'route_code',
        'route_name',
        'route_details',
        'polyline_data',
        'route_color',
    ];

    protected $casts = [
        'polyline_data' => 'array',
    ];

    public function routeStops(): HasMany {
        return $this->hasMany(RouteStop::class, 'route_code', 'route_code')->orderBy('stop_order');
    }

    public function stations(): HasMany {
        return $this->hasMany(Station::class, 'route_code', 'route_code')->orderBy('order_of_parking_spots');
    }

    public function schedules(): HasMany {
        return $this->hasMany(Schedule::class, 'route_code', 'route_code');
    }

    public function travelHistories(): HasMany {
        return $this->hasMany(TravelHistory::class, 'route_code', 'route_code');
    }
}
