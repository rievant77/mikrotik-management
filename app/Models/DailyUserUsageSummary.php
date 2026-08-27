<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyUserUsageSummary extends Model
{
    protected $fillable = [
        'hotspot_user_id',
        'username',
        'usage_date',
        'total_bytes_in',
        'total_bytes_out',
        'total_bytes',
        'total_uptime_seconds',
        'session_count',
    ];

    protected $casts = [
        'usage_date' => 'date:Y-m-d',
        'total_bytes_in' => 'integer',
        'total_bytes_out' => 'integer',
        'total_bytes' => 'integer',
        'total_uptime_seconds' => 'integer',
        'session_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(HotspotUser::class, 'hotspot_user_id');
    }
}
