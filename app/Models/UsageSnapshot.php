<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageSnapshot extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'recorded_at',
        'bytes_in',
        'bytes_out',
        'delta_bytes_in',
        'delta_bytes_out',
        'upload_bps',
        'download_bps',
        'uptime_seconds',
        'collector_run_id',
        'created_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'created_at' => 'datetime',
        'bytes_in' => 'integer',
        'bytes_out' => 'integer',
        'delta_bytes_in' => 'integer',
        'delta_bytes_out' => 'integer',
        'upload_bps' => 'integer',
        'download_bps' => 'integer',
        'uptime_seconds' => 'integer',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(HotspotSession::class, 'session_id');
    }
}
