<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Maintenance extends Model {
    protected $table = 'maintenances';
    protected $primaryKey = 'maintenance_code';
    public $incrementing = false; // String primary key
    protected $keyType = 'string';

    protected $fillable = [
        'maintenance_code',
        'user_id',
        'skytrain_code',
        'repair_details',
        'repair_notification_date',
        'repair_start_date',
        'date_of_repair_completion',
        'repair_status',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function electricTrain(): BelongsTo {
        return $this->belongsTo(ElectricTrain::class, 'skytrain_code', 'skytrain_code');
    }
}
