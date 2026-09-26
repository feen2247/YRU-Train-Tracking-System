<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GpsDevice extends Model {
    protected $table = 'gps_devices';

    protected $fillable = [
        'device_id',
        'name',
        'vehicle_id',
        'latitude',
        'longitude',
        'speed_kmh',
        'satellites',
        'hdop',
        'last_seen_at',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'speed_kmh' => 'float',
        'hdop' => 'float',
        'satellites' => 'integer',
        'last_seen_at' => 'datetime',
    ];

    public function isOnline(): bool {
        if (!$this->last_seen_at) {
            return false;
        }
        return $this->last_seen_at->diffInSeconds(now()) <= (int) config('services.gps.stale_seconds', 120);
    }

    public function toApiArray(): array {
        return [
            'device_id' => $this->device_id,
            'name' => $this->name,
            'vehicle_id' => $this->vehicle_id,
            'lat' => $this->latitude,
            'lng' => $this->longitude,
            'speed_kmh' => $this->speed_kmh,
            'satellites' => $this->satellites,
            'hdop' => $this->hdop,
            'last_seen_at' => $this->last_seen_at?->toIso8601String(),
            'age_seconds' => $this->last_seen_at ? (int) $this->last_seen_at->diffInSeconds(now()) : null,
            'online' => $this->isOnline(),
        ];
    }
}
