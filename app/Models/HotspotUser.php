<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HotspotUser extends Model
{
    protected $fillable = [
        'profile_id',
        'username',
        'password',
        'uptime_limit',
        'comment',
        'is_active',
        'expired_at',
        'fup_custom',
        'fup_limit_bytes',
        'fup_rate_limit',
        'fup_active',
        'fup_triggered_at',
        'fup_usage_bytes',
        'fup_last_reset_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expired_at' => 'datetime',
        'fup_custom' => 'boolean',
        'fup_limit_bytes' => 'integer',
        'fup_active' => 'boolean',
        'fup_triggered_at' => 'datetime',
        'fup_usage_bytes' => 'integer',
        'fup_last_reset_at' => 'datetime',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(HotspotProfile::class, 'profile_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(HotspotSession::class, 'hotspot_user_id');
    }

    public function voucherSales(): HasMany
    {
        return $this->hasMany(VoucherSale::class, 'hotspot_user_id');
    }
}
