<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstructorReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'instructor_id',
        'rating',
        'review',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }
}
