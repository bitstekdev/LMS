<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BootcampModule extends Model
{
    /** @use HasFactory<\Database\Factories\BootcampModuleFactory> */
    use HasFactory;

    protected $fillable = [
        'bootcamp_id',
        'title',
        'publish_date',
        'expiry_date',
        'restriction',
        'sort',
    ];

    public function bootcamp()
    {
        return $this->belongsTo(Bootcamp::class);
    }
}
