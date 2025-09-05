<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'title',
        'type',
        'answer',
        'options',
        'sort',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}
