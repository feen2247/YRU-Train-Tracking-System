<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainLocation extends Model {
    protected $table = 'train_locations';
    protected $primaryKey = 'position_code'; // Auto-incrementing INT PK

    protected $fillable = [
        'skytrain_code',
        'latitude',
        'longitude',
        'recorded_time',
    ];

    public function electricTrain(): BelongsTo {
        return $this->belongsTo(ElectricTrain::class, 'skytrain_code', 'skytrain_code');
    }
}
