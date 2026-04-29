<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    protected $fillable = [
        'fdr_id',
        'interest_amount',
        'tax_rate',
        'tax_amount',
        'deduction_date'
    ];

    public function fdr()
    {
        return $this->belongsTo(Fdr::class);
    }
}
