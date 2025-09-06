<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TutorCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'status',
    ];

    public function upcomingTutorSchedules()
    {
        return $this->hasMany(TutorSchedule::class, 'category_id')
            ->where('start_time', '>=', now()->timestamp);
    }
}
