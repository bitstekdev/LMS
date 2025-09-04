<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = [
        'admin_id',
        'permissions',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
