<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bootcamp extends Model
{
    /** @use HasFactory<\Database\Factories\BootcampFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'description',
        'short_description',
        'is_paid',
        'price',
        'discount_flag',
        'discounted_price',
        'publish_date',
        'thumbnail',
        'faqs',
        'requirements',
        'outcomes',
        'meta_keywords',
        'meta_description',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(BootcampCategory::class, 'category_id');
    }

    public function modules()
    {
        return $this->hasMany(BootcampModule::class, 'module_id');
    }
}
