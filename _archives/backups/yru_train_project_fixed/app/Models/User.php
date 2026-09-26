<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'user_id';
    public $incrementing = false; // String primary key
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'employee_id',
        'prefix',
        'first_name',
        'last_name',
        'username',
        'name',
        'email',
        'password',
        'phone_number',
        'usage_rights',
        'user_role',
        'status',
        'remark',
        'otp_code',
        'otp_expires_at',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    // Relationships
    public function schedules(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Schedule::class, 'driver_id', 'user_id');
    }

    public function maintenances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Maintenance::class, 'user_id', 'user_id');
    }

    public function travelHistories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TravelHistory::class, 'driver_id', 'user_id');
    }

    public function notifications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Notification::class, 'user_id', 'user_id');
    }
}