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

    public function contactOne()
    {
        return $this->belongsTo(User::class, 'contact_one');
    }

    public function contactTwo()
    {
        return $this->belongsTo(User::class, 'contact_one');
    }

    public function participant()
    {
        if (auth('web')->id() === $this->contact_one) {
            return $this->belongsTo(User::class, 'contact_two');
        }

        return $this->belongsTo(User::class, 'contact_one');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'thread_id');
    }
}
