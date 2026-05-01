<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Setting extends Model
{
    protected $fillable = [
        'tax_rate',
        'tax_effective_date',
        'duty_slabs',
        'auto_tax_enabled',
        'auto_duty_enabled',
    ];

    protected $casts = [
        'duty_slabs' => 'array',
        'auto_tax_enabled' => 'boolean',
        'auto_duty_enabled' => 'boolean',
        'tax_effective_date' => 'date',
    ];

    /**
     * Get system settings (always returns a row)
     */
    public static function getSettings(): self
    {
        return self::first() ?? self::create([
            'tax_rate' => 10,
            'tax_effective_date' => now(),
            'auto_tax_enabled' => true,
            'auto_duty_enabled' => true,
            'duty_slabs' => [
                ['min' => 0, 'max' => 100000, 'amount' => 0],
                ['min' => 100001, 'max' => 500000, 'amount' => 500],
                ['min' => 500001, 'max' => 1000000, 'amount' => 1500],
                ['min' => 1000001, 'max' => null, 'amount' => 2500],
            ],
        ]);
    }

    /**
     * ✅ Get dynamic tax rate (with effective date)
     */
    public static function getTaxRate(): float
    {
        $settings = self::getSettings();

        // Tax disabled
        if (!$settings->auto_tax_enabled) {
            return 0;
        }

        // If effective date is set and in future → don't apply yet
        if ($settings->tax_effective_date &&
            Carbon::parse($settings->tax_effective_date)->isFuture()) {
            return 0;
        }

        return $settings->tax_rate ?? 10;
    }

    /**
     * ✅ Get duty slabs safely
     */
    public static function getDutySlabs(): array
    {
        $settings = self::getSettings();

        if (!$settings->auto_duty_enabled) {
            return [];
        }

        return $settings->duty_slabs ?? [];
    }
}