<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fund extends Model
{
    protected $fillable = [
        'name',
        'code',
        'opening_balance',
        'current_balance',
        'available_balance'
    ];

    protected static function booted()
    {
        static::creating(function ($fund) {
            $fund->current_balance = $fund->opening_balance;
            $fund->available_balance = $fund->opening_balance;
        });
    }
     // Balance adjustment helper
    public function adjustBalance(float $amount, string $type = 'add'): void
    {
        if ($type === 'add') {
            $this->current_balance += $amount;
            $this->available_balance += $amount;
        } else {
            $this->current_balance -= $amount;
            $this->available_balance -= $amount;
        }

        $this->save();
    }
    public function fdrs()
    {
        return $this->hasMany(Fdr::class);
    }

    public function duties()
    {
        return $this->hasMany(Duty::class);
    }
}
