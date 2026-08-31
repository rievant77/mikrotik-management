<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RouterSetting extends Model
{
    protected $fillable = [
        'name',
        'host',
        'api_port',
        'username',
        'password',
        'use_ssl',
        'is_active',
        'connection_timeout_ms',
        'hotspot_name',
        'login_url',
        'footer_text',
        'last_successful_poll_at',
        'last_error_at',
        'last_error_message',
    ];

    protected $casts = [
        'use_ssl' => 'boolean',
        'is_active' => 'boolean',
        'password' => 'encrypted',
        'last_successful_poll_at' => 'datetime',
        'last_error_at' => 'datetime',
    ];
}
