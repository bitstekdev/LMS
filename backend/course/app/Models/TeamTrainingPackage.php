<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamTrainingPackage extends Model
{
    /** @use HasFactory<\Database\Factories\TeamTrainingPackageFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'price',
        'course_privacy',
        'course_id',
        'allocation',
        'expiry_type',
        'start_date',
        'expiry_date',
        'features',
        'thumbnail',
        'pricing_type',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
