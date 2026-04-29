<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'fdr_id',
        'type',
        'principal',
        'interest',
        'tax',
        'duty',
        'net_amount',
        'transaction_date',
        'remarks'
    ];

    public function fdr()
    {
        return $this->belongsTo(Fdr::class);
    }
}
