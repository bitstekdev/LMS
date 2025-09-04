<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TutorReview extends Model
{
    protected $fillable = [
        'tutor_id',
        'student_id',
        'rating',
        'review',
    ];

    public function tutor()
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
