<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageThread extends Model
{
    protected $fillable = [
        'code',
        'contact_one',
        'contact_two',
    ];
}
