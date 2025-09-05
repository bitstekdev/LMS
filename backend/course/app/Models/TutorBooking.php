<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TutorBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice',
        'schedule_id',
        'student_id',
        'tutor_id',
        'start_time',
        'end_time',
        'joining_data',
        'price',
        'admin_revenue',
        'instructor_revenue',
        'tax',
        'payment_method',
        'payment_details',
    ];

    public function schedule()
    {
        return $this->belongsTo(TutorSchedule::class, 'schedule_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function tutor()
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }
}
