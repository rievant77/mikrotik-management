<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceWebHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'mac_address',
        'ip_address',
        'username',
        'domain',
        'site_name',
        'category',
        'protocol',
        'port',
        'hit_count',
        'total_bytes',
        'first_seen_at',
        'last_seen_at',
    ];

    protected $casts = [
        'port' => 'integer',
        'hit_count' => 'integer',
        'total_bytes' => 'integer',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];
}
