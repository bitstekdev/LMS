<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'section_id',
        'title',
        'lesson_type',
        'duration',
        'total_mark',
        'pass_mark',
        'retake',
        'lesson_src',
        'attachment',
        'attachment_type',
        'video_type',
        'thumbnail',
        'is_free',
        'sort',
        'description',
        'summary',
        'status',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
