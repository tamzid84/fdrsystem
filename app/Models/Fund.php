<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fund extends Model
{
    protected $fillable = [
        'name',
        'code',
        'opening_balance',
        'current_balance',
        'available_balance',
        'tax_rate',
    ];

    /**
     * Auto সেট করা হবে যখন নতুন Fund তৈরি হবে
     */
    protected static function booted()
    {
        static::creating(function ($fund) {
            $fund->current_balance = $fund->opening_balance;
            $fund->available_balance = $fund->opening_balance;
        });
    }

    /**
     * 🔥 Balance Adjustment (SAFE VERSION)
     */
    public function adjustBalance(float $amount, string $type = 'add'): void
    {
        // Ensure precision
        $amount = round($amount, 2);

        if ($type === 'add') {

            $this->current_balance += $amount;
            $this->available_balance += $amount;

        } elseif ($type === 'subtract') {

            // 🚨 Prevent negative balance
            if ($this->available_balance < $amount) {
                throw new \Exception("Insufficient fund balance");
            }

            $this->current_balance -= $amount;
            $this->available_balance -= $amount;

        } else {
            throw new \InvalidArgumentException("Invalid balance operation type");
        }

        $this->save();
    }

    /**
     * 🔗 Relationships
     */
    public function fdrs(): HasMany
    {
        return $this->hasMany(Fdr::class);
    }

    public function duties(): HasMany
    {
        return $this->hasMany(Duty::class);
    }

    /**
     * 🔥 (Optional) Get total investment
     */
    public function getTotalInvestmentAttribute(): float
    {
        return $this->fdrs()->sum('amount');
    }
}