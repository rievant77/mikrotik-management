<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonthlyCustomer extends Model
{
    protected $fillable = [
        'name',
        'hotspot_user_id',
        'contact',
        'address',
        'notes',
        'monthly_price',
        'cost_price',
        'billing_day',
        'is_active',
    ];

    protected $casts = [
        'monthly_price' => 'float',
        'cost_price' => 'float',
        'billing_day' => 'integer',
        'is_active' => 'boolean',
    ];

    public function hotspotUser(): BelongsTo
    {
        return $this->belongsTo(HotspotUser::class, 'hotspot_user_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(MonthlyPayment::class, 'monthly_customer_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(MonthlyInvoice::class, 'monthly_customer_id');
    }
}
