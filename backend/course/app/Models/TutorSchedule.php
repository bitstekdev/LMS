<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TutorSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'tutor_id',
        'category_id',
        'subject_id',
        'start_time',
        'end_time',
        'tution_type',
        'duration',
        'description',
        'status',
        'booking_id',
    ];

    public function tutor()
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    public function category()
    {
        return $this->belongsTo(TutorCategory::class, 'category_id');
    }

    public function subject()
    {
        return $this->belongsTo(TutorSubject::class, 'subject_id');
    }

    public function booking()
    {
        return $this->belongsTo(TutorBooking::class, 'booking_id');
    }

    public function canTeach()
    {
        return $this->hasOne(TutorCanTeach::class, 'subject_id', 'subject_id')
            ->where('category_id', $this->category_id)
            ->where('user_id', $this->tutor_id);
    }
}
