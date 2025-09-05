<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LanguagePhrase extends Model
{
    protected $fillable = [
        'language_id',
        'phrase',
        'translated',
    ];

    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
