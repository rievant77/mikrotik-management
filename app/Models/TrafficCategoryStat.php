<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrafficCategoryStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'recorded_date',
        'recorded_hour',
        'category',
        'platform',
        'bytes_in',
        'bytes_out',
        'total_bytes',
    ];

    protected $casts = [
        'recorded_date' => 'date',
        'recorded_hour' => 'integer',
        'bytes_in' => 'integer',
        'bytes_out' => 'integer',
        'total_bytes' => 'integer',
    ];
}
