<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherSale extends Model
{
    protected $fillable = [
        'hotspot_user_id',
        'username',
        'profile_name',
        'cost_price',
        'selling_price',
        'profit',
        'activated_at',
        'recorded_by_user_id',
        'shift_id',
        'status',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'profit' => 'decimal:2',
        'activated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(HotspotUser::class, 'hotspot_user_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(CashierShift::class, 'shift_id');
    }
}
