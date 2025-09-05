<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaFile extends Model
{
    protected $fillable = [
        'user_id',
        'post_id',
        'story_id',
        'album_id',
        'product_id',
        'page_id',
        'group_id',
        'chat_id',
        'file_name',
        'file_type',
        'privacy',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
