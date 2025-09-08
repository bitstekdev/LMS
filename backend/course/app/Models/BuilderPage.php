<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuilderPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'html',
        'identifier',
        'is_permanent',
        'status',
        'edit_home_id',
    ];

    protected $casts = [
        'is_permanent' => 'boolean',
        'status' => 'boolean',
    ];
}
