<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoField extends Model
{
    protected $fillable = [
        'course_id',
        'blog_id',
        'bootcamp_id',
        'route',
        'name_route',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'meta_robot',
        'canonical_url',
        'custom_url',
        'json_ld',
        'og_title',
        'og_description',
        'og_image',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }

    public function bootcamp()
    {
        return $this->belongsTo(Bootcamp::class);
    }
}
