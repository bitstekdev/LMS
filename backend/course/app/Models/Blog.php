<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'description',
        'thumbnail',
        'banner',
        'keywords',
        'is_popular',
        'status',
    ];

    protected $casts = [
        'is_popular' => 'boolean',
        'status' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    /**
     * Scope for published blogs (status = true)
     */
    public function scopePublished($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope: Latest popular blogs (is_popular = true, ordered by latest)
     */
    public function scopeLatestPopular($query)
    {
        return $query->where('is_popular', true)->latest();
    }
}
