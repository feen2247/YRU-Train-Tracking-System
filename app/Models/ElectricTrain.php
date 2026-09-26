<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ElectricTrain extends Model {
    protected $table = 'electric_trains';
    protected $primaryKey = 'skytrain_code';
    public $incrementing = false; // String primary key
    protected $keyType = 'string';

    protected $fillable = [
        'skytrain_code',
        'car_number',
        'electric_train_type',
        'car_status',
        'route_code',
    ];

    public function route(): BelongsTo {
        return $this->belongsTo(Route::class, 'route_code', 'route_code');
    }

    public function locations(): HasMany {
        return $this->hasMany(TrainLocation::class, 'skytrain_code', 'skytrain_code');
    }

    public function schedules(): HasMany {
        return $this->hasMany(Schedule::class, 'skytrain_code', 'skytrain_code');
    }

    public function maintenances(): HasMany {
        return $this->hasMany(Maintenance::class, 'skytrain_code', 'skytrain_code');
    }

    public function travelHistories(): HasMany {
        return $this->hasMany(TravelHistory::class, 'skytrain_code', 'skytrain_code');
    }
}
