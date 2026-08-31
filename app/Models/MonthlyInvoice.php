<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonthlyInvoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'monthly_customer_id',
        'billing_month',
        'amount',
        'cost_price',
        'amount_paid',
        'balance_due',
        'due_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'billing_month' => 'date',
        'due_date' => 'date',
        'amount' => 'float',
        'cost_price' => 'float',
        'amount_paid' => 'float',
        'balance_due' => 'float',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(MonthlyCustomer::class, 'monthly_customer_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(MonthlyPayment::class, 'monthly_invoice_id');
    }

    public function getProgressPercentAttribute(): int
    {
        if ($this->amount <= 0) return 100;
        return (int) min(100, round(($this->amount_paid / $this->amount) * 100));
    }
}
