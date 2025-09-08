<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'is_editable',
        'addon_identifier',
        'user_types',
        'system_notification',
        'email_notification',
        'subject',
        'template',
        'setting_title',
        'setting_sub_title',
        'date_updated',
    ];

    protected $casts = [
        'is_editable' => 'boolean',
    ];
}
