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

    protected $appends = [
        'total_duration',
        'total_seconds',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sections()
    {
        return $this->hasMany(Section::class)->orderBy('sort');
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // Custom Accessor
    public function getTotalSecondsAttribute()
    {
        return $this->lessons()
            ->selectRaw('SUM(TIME_TO_SEC(duration)) as total_time')
            ->value('total_time') ?? 0;
    }

    public function getTotalDurationAttribute()
    {
        $seconds = $this->total_seconds;

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $remainingSeconds);
    }

    // Helper Methods
    public function instructors($instructorIds = null)
    {
        // Determine source of instructor IDs
        if (is_null($instructorIds)) {
            $instructorIds = $this->instructor_ids ?? [];
        }

        if (is_string($instructorIds)) {
            $instructorIds = json_decode($instructorIds, true) ?? [];
        }

        if (! is_array($instructorIds) || empty($instructorIds)) {
            return collect(); // Return empty collection
        }

        return User::whereIn('id', $instructorIds)->get();
    }
}
