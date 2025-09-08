<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
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

    protected $casts = [
        'faqs' => 'array',
        'instructor_ids' => 'array',
        'requirements' => 'array',
        'outcomes' => 'array',
        'drip_content_settings' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
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

    public function wishlists()
    {
        $query = $this->hasMany(Wishlist::class);

        if (auth('web')->user()) {
            $query->where('user_id', auth('web')->user()->id);
        }

        return $query;
    }

    // Custom Accessor
    public function getTotalSecondsAttribute()
    {
        // Fetch durations as a collection of strings
        $durations = $this->lessons()->pluck('duration');

        $totalSeconds = 0;

        foreach ($durations as $duration) {
            if (! $duration) {
                continue;
            }

            // Try to parse "H:i:s" or "i:s"
            $parts = explode(':', $duration);

            if (count($parts) === 3) {
                [$hours, $minutes, $seconds] = $parts;
            } elseif (count($parts) === 2) {
                $hours = 0;
                [$minutes, $seconds] = $parts;
            } else {
                // fallback: assume it's seconds already
                $hours = 0;
                $minutes = 0;
                $seconds = (int) $duration;
            }

            $totalSeconds += ((int) $hours * 3600) + ((int) $minutes * 60) + (int) $seconds;
        }

        return $totalSeconds;
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
