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
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expired_at' => 'datetime',
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
