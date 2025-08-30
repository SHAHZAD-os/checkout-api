<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    protected $fillable = ['user_id', 'login_time', 'logout_time'];

    protected $casts = [
        'login_time' => 'datetime',
        'logout_time' => 'datetime',
    ];
}
