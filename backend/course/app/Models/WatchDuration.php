<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WatchDuration extends Model
{
    protected $fillable = [
        'watched_student_id',
        'watched_course_id',
        'watched_lesson_id',
        'current_duration',
        'watched_counter',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'watched_student_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'watched_course_id');
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'watched_lesson_id');
    }
}
