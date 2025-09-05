<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TutorCanTeach extends Model
{
    use HasFactory;

    protected $fillable = [
        'instructor_id',
        'category_id',
        'subject_id',
        'description',
        'thumbnail',
        'price',
    ];

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function category()
    {
        return $this->belongsTo(TutorCategory::class, 'category_id');
    }

    public function subject()
    {
        return $this->belongsTo(TutorSubject::class, 'subject_id');
    }
}
