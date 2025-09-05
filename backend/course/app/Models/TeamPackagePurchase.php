<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamPackagePurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice',
        'user_id',
        'package_id',
        'price',
        'admin_revenue',
        'instructor_revenue',
        'tax',
        'payment_method',
        'payment_details',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(TeamTrainingPackage::class, 'package_id');
    }
}
