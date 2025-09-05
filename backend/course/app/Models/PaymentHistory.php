<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'payment_type',
        'amount',
        'invoice',
        'date_added',
        'last_modified',
        'admin_revenue',
        'instructor_revenue',
        'tax',
        'instructor_payment_status',
        'transaction_id',
        'session_id',
        'coupon',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
