<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bank extends Model
{
    protected $fillable = [
        'name',
        'type',
        'branch_name',
        'account_number',
        'routing_number',
        'phone',
        'address',
        'total_investment',
    ];

    /**
     * Ensure default values
     */
    protected static function booted()
    {
        static::creating(function ($bank) {
            $bank->total_investment = $bank->total_investment ?? 0;
        });
    }

    /**
     * 🔗 Relationships
     */
    public function fdrs(): HasMany
    {
        return $this->hasMany(Fdr::class);
    }

    /**
     * 🔥 Safe Investment Adjustment (ERP Standard)
     */
    public function adjustInvestment(float $amount, string $type = 'add'): void
    {
        // Ensure precision
        $amount = round($amount, 2);

        // Ensure not null
        $this->total_investment = $this->total_investment ?? 0;

        if ($type === 'add') {

            $this->total_investment += $amount;

        } elseif ($type === 'subtract') {

            // 🚨 Prevent negative investment
            if ($this->total_investment < $amount) {
                throw new \Exception("Bank investment cannot go negative");
            }

            $this->total_investment -= $amount;

        } else {
            throw new \InvalidArgumentException("Invalid investment operation type");
        }

        $this->save();
    }

    /**
     * 🔥 Helpers
     */
    public function isGovt(): bool
    {
        return strtolower($this->type) === 'govt';
    }

    public function isPrivate(): bool
    {
        return strtolower($this->type) === 'private';
    }

    /**
     * 🔥 (Optional) Get total active FDR investment
     */
    public function getActiveInvestmentAttribute(): float
    {
        return $this->fdrs()
            ->where('status', 'active')
            ->sum('amount');
    }
}