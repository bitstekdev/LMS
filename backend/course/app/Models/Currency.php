<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'code',
        'symbol',
        'paypal_supported',
        'stripe_supported',
        'ccavenue_supported',
        'iyzico_supported',
        'paystack_supported',
    ];

    /**
     * Casts for boolean fields.
     */
    protected $casts = [
        'paypal_supported' => 'boolean',
        'stripe_supported' => 'boolean',
        'ccavenue_supported' => 'boolean',
        'iyzico_supported' => 'boolean',
        'paystack_supported' => 'boolean',
    ];
}
