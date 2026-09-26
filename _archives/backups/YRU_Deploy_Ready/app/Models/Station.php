<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Station extends Model {
    protected $table = 'stations';
    protected $primaryKey = 'parking_spot_code';
    public $incrementing = false; // String primary key
    protected $keyType = 'string';

    protected $fillable = [
        'parking_spot_code',
        'route_code',
        'parking_spot_name',
        'order_of_parking_spots',
    ];

    public function route(): BelongsTo {
        return $this->belongsTo(Route::class, 'route_code', 'route_code');
    }
}
