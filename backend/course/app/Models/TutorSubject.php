<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TutorSubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'status',
    ];

    public function upcomingSchedules()
    {
        return $this->hasMany(TutorSchedule::class, 'subject_id', 'id')
            ->where('start_time', '>=', time());
    }
}
