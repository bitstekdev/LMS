<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class BootcampPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice',
        'user_id',
        'bootcamp_id',
        'price',
        'tax',
        'payment_method',
        'payment_details',
        'status',
        'admin_revenue',
        'instructor_revenue',
    ];

    protected $casts = [
        'price' => 'float',
        'tax' => 'float',
        'admin_revenue' => 'float',
        'instructor_revenue' => 'float',
        'status' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bootcamp()
    {
        return $this->belongsTo(Bootcamp::class);
    }

    public function scopePaid($query)
    {
        return $query->where('status', 1);
    }

    public function getIsPaidAttribute(): bool
    {
        return (int) $this->status === 1;
    }

    /**
     * Handle bootcamp enrollment (without payment gateways).
     */
    public static function purchase_bootcamp(int $bootcampId)
    {
        $user = auth('web')->user();
        $bootcamp = Bootcamp::findOrFail($bootcampId);

        if (! $user || ! $bootcamp) {
            abort(403, 'Unauthorized or invalid bootcamp.');
        }

        $price = $bootcamp->is_paid ? $bootcamp->price : 0;
        $tax = 0;

        $payment = [
            'invoice' => '#'.Str::random(20),
            'user_id' => $user->id,
            'bootcamp_id' => $bootcamp->id,
            'price' => $price,
            'tax' => $tax,
            'status' => 1,
            'payment_method' => 'manual',
        ];

        // Revenue logic
        $creator = get_user_info($bootcamp->user_id);

        if ($creator->role === 'admin') {
            $payment['admin_revenue'] = $price;
            $payment['instructor_revenue'] = 0;
        } else {
            $instructor_percentage = (float) get_settings('instructor_revenue', 70);
            $payment['instructor_revenue'] = round($price * ($instructor_percentage / 100), 2);
            $payment['admin_revenue'] = $price - $payment['instructor_revenue'];
        }

        BootcampPurchase::create($payment);

        Session::flash('success', get_phrase('Bootcamp enrolled successfully.'));

        return redirect()->route('my.bootcamps');
    }
}
