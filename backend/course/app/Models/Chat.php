<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'reciver_id',
        'message_thrade',
        'chatcenter',
        'message',
        'file',
        'reaction',
        'read_status',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'reciver_id');
    }

    public function thread()
    {
        return $this->belongsTo(MessageThread::class, 'message_thrade');
    }
}
