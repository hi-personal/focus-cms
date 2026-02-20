<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;


class UserSession extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;
    protected $table = 'sessions';

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'payload',
        'last_activity',
        'login_valid_datetime',
        'auth_2fa_validated'
    ];

    protected $casts = [
        'last_activity' => 'integer',
        'login_valid_datetime' => 'datetime',
    ];


    protected $attributes = [
        'last_activity' => 0,
        'login_valid_datetime' => null,
    ];

    public function getLastActivityAttribute()
    {
        return isset($this->attributes['last_activity'])
            ? Carbon::createFromTimestamp($this->attributes['last_activity'])
            : null;
    }

    public function getLoginValidDateTimeAttribute()
    {
        return isset($this->attributes['login_valid_datetime'])
            ? Carbon::createFromTimestamp($this->attributes['login_valid_datetime'])
            : null;
    }
}