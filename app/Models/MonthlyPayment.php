<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyPayment extends Model
{
    protected $fillable = [
        'monthly_customer_id',
        'monthly_invoice_id',
        'billing_month',
        'monthly_price',
        'amount_paid',
        'paid_at',
        'payment_method',
        'status',
        'recorded_by_user_id',
        'shift_id',
        'notes',
    ];

    protected $casts = [
        'billing_month' => 'date',
        'paid_at' => 'date',
        'monthly_price' => 'float',
        'amount_paid' => 'float',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(MonthlyCustomer::class, 'monthly_customer_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(MonthlyInvoice::class, 'monthly_invoice_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(CashierShift::class, 'shift_id');
    }
}
