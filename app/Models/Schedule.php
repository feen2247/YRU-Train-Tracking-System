<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model {
    protected $table = 'schedules';
    protected $primaryKey = 'timetable_code';
    public $incrementing = false; // String primary key
    protected $keyType = 'string';

    protected $fillable = [
        'timetable_code',
        'skytrain_code',
        'driver_id',
        'route_code',
        'departure_time',
        'arrival_time',
        'date',
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
