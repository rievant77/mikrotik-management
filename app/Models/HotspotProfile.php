<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HotspotProfile extends Model
{
    protected $fillable = [
        'name',
        'shared_users',
        'rate_limit',
        'validity',
        'cost_price',
        'selling_price',
        'data_limit_bytes',
        'expired_mode',
        'is_active',
    ];

    protected $casts = [
        'shared_users' => 'integer',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'data_limit_bytes' => 'integer',
        'is_active' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(HotspotUser::class, 'profile_id');
    }
}
