<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyExpenseEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'name',
        'amount',
        'billing_month',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'billing_month' => 'date',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(MonthlyExpenseTemplate::class, 'template_id');
    }
}
