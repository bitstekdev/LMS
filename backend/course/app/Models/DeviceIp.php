<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceIp extends Model
{
    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'session_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
