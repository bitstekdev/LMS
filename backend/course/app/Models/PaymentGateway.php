<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $fillable = [
        'identifier',
        'currency',
        'title',
        'model_name',
        'description',
        'keys',
        'status',
        'test_mode',
        'is_addon',
    ];

    protected $casts = [
        'keys' => 'array',
        'status' => 'boolean',
        'test_mode' => 'boolean',
        'is_addon' => 'boolean',
    ];
}
