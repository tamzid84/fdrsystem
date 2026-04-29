<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Duty extends Model
{
    protected $fillable = [
        'fund_id',
        'year',
        'total_balance',
        'duty_amount',
        'deduction_date'
    ];

    public function fund()
    {
        return $this->belongsTo(Fund::class);
    }
}
