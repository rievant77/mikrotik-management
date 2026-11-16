<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HotspotProfile extends Model
{
    protected $fillable = [
        'name',
        'category',
        'shared_users',
        'rate_limit',
        'validity',
        'cost_price_type',
        'cost_price_percent',
        'cost_price',
        'selling_price',
        'data_limit_bytes',
        'expired_mode',
        'fup_enabled',
        'fup_limit_bytes',
        'fup_limit_display',
        'fup_rate_percent',
        'fup_rate_limit',
        'fup_reset_cycle',
        'is_active',
    ];

    protected $casts = [
        'shared_users' => 'integer',
        'cost_price_percent' => 'float',
        'cost_price' => 'float',
        'selling_price' => 'float',
        'data_limit_bytes' => 'integer',
        'fup_enabled' => 'boolean',
        'fup_limit_bytes' => 'integer',
        'fup_rate_percent' => 'integer',
        'is_active' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(HotspotUser::class, 'profile_id');
    }

    public function monthlyCustomers(): HasMany
    {
        return $this->hasMany(MonthlyCustomer::class, 'profile_id');
    }
}
