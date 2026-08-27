<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HotspotSession extends Model
{
    protected $fillable = [
        'hotspot_user_id',
        'username',
        'mac_address',
        'device_name',
        'hostname',
        'ip_address',
        'interface',
        'started_at',
        'last_seen_at',
        'ended_at',
        'status',
        'last_bytes_in',
        'last_bytes_out',
        'total_bytes_in',
        'total_bytes_out',
        'current_rx_bps',
        'current_tx_bps',
    ];

    protected $appends = [
        'device_display_name',
        'device_type',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'ended_at' => 'datetime',
        'last_bytes_in' => 'integer',
        'last_bytes_out' => 'integer',
        'total_bytes_in' => 'integer',
        'total_bytes_out' => 'integer',
        'current_rx_bps' => 'integer',
        'current_tx_bps' => 'integer',
    ];

    public function getDeviceDisplayNameAttribute(): string
    {
        if (!empty($this->attributes['hostname'])) {
            return $this->attributes['hostname'];
        }

        if (!empty($this->attributes['device_name'])) {
            return $this->attributes['device_name'];
        }

        return \App\Support\DeviceHelper::resolveDeviceName(
            $this->attributes['hostname'] ?? null,
            $this->attributes['mac_address'] ?? null
        );
    }

    public function getDeviceTypeAttribute(): string
    {
        return \App\Support\DeviceHelper::getDeviceType($this->device_display_name);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(HotspotUser::class, 'hotspot_user_id');
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(UsageSnapshot::class, 'session_id');
    }
}
