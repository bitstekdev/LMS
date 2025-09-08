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

    public static function purchase_bootcamp(string $identifier)
    {
        $user = auth('web')->user();
        $payment_details = session('payment_details');

        if (! $user || empty($payment_details)) {
            abort(403, 'Unauthorized or missing payment details.');
        }

        $payment = [];

        // Handle session-based transaction keys
        if (Session::has('keys')) {
            $payment['payment_details'] = json_encode(session('keys'));
            Session::forget('keys');
        }

        if (Session::has('session_id')) {
            $payment['payment_details'] = session('session_id');
            Session::forget('session_id');
        }

        // Base payment info
        $payment['invoice'] = '#'.Str::random(20);
        $payment['user_id'] = $user->id;
        $payment['bootcamp_id'] = $payment_details['items'][0]['id'];
        $payment['price'] = $payment_details['payable_amount'];
        $payment['tax'] = $payment_details['tax'];
        $payment['payment_method'] = $identifier;
        $payment['status'] = 1;

        // Revenue distribution
        $bootcamp = Bootcamp::findOrFail($payment['bootcamp_id']);
        $creator = get_user_info($bootcamp->user_id);

        if ($creator->role === 'admin') {
            $payment['admin_revenue'] = $payment['price'];
            $payment['instructor_revenue'] = 0;
        } else {
            $instructor_percentage = (float) get_settings('instructor_revenue', 70);
            $payment['instructor_revenue'] = round($payment['price'] * ($instructor_percentage / 100), 2);
            $payment['admin_revenue'] = $payment['price'] - $payment['instructor_revenue'];
        }

        // Persist to DB
        BootcampPurchase::create($payment);

        // Clear session
        Session::forget('payment_details');
        Session::flash('success', 'Bootcamp purchased successfully.');

        return redirect()->route('my.bootcamps');
    }
}
