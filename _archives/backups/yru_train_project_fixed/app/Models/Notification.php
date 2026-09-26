<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model {
    protected $table = 'notifications';

    protected $fillable = [
        'title',
        'content',
        'type',
        'user_id',
    ];

    public function creator(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
