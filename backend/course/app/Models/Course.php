<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'short_description',
        'course_type',
        'status',
        'level',
        'language',
        'is_paid',
        'is_best',
        'price',
        'discounted_price',
        'discount_flag',
        'enable_drip_content',
        'drip_content_settings',
        'meta_keywords',
        'meta_description',
        'thumbnail',
        'banner',
        'preview',
        'description',
        'requirements',
        'outcomes',
        'faqs',
        'instructor_ids',
        'average_rating',
        'expiry_period',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }
}
