<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fdr extends Model
{
    protected $fillable = [
        'fdr_number',
        'fdr_account_number',
        'fund_id',
        'bank_id',
        'branch_name',
        'amount',
        'interest_rate',
        'start_date',
        'maturity_date',
        'tenure',
        'renewal_type',
        'status'
    ];

    public function fund()
    {
        return $this->belongsTo(Fund::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function taxes()
    {
        return $this->hasMany(Tax::class);
    }
}
