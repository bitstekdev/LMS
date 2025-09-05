<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LikeDislikeReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'review_id',
        'user_id',
        'liked',
        'disliked',
    ];

    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
