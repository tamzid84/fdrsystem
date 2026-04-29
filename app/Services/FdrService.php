<?php

namespace App\Services;

use App\Models\Fdr;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FdrService
{
    /**
     * Calculate Interest
     */
    public static function calculateInterest(float $amount, float $rate, int $tenure): float
    {
        return round(($amount * $rate * $tenure) / 1200, 2);
    }

    /**
     * Calculate Tax
     */
    public static function calculateTax(float $interest, float $taxRate = 10): float
    {
        return round(($interest * $taxRate) / 100, 2);
    }

    /**
     * Net Interest
     */
    public static function netInterest(float $interest, float $tax): float
    {
        return round($interest - $tax, 2);
    }

    /**
     * Maturity Date
     */
    public static function maturityDate($startDate, int $tenure): string
    {
        return Carbon::parse($startDate)
            ->addMonths($tenure)
            ->toDateString();
    }

    /**
     * CREATE FDR
     */
    public static function create(array $data): Fdr
    {
        return DB::transaction(function () use ($data) {

            $data['maturity_date'] = self::maturityDate(
                $data['start_date'],
                $data['tenure']
            );

            $fdr = Fdr::create($data);

            // Fund update
            $fdr->fund->adjustBalance($fdr->amount, 'subtract');

            // Bank update
            $fdr->bank->adjustInvestment($fdr->amount, 'add');

            // 🧾 Transaction log
            Transaction::create([
                'fdr_id' => $fdr->id,
                'type' => 'create',
                'principal' => $fdr->amount,
                'interest' => 0,
                'tax' => 0,
                'duty' => 0,
                'net_amount' => $fdr->amount,
                'transaction_date' => now(),
                'remarks' => 'FDR Created',
            ]);

            return $fdr;
        });
    }

    /**
     * RENEW FDR
     */
    public static function renew(Fdr $fdr, float $taxRate = 10): Fdr
    {
        return DB::transaction(function () use ($fdr, $taxRate) {

            $interest = self::calculateInterest(
                $fdr->amount,
                $fdr->interest_rate,
                $fdr->tenure
            );

            $tax = self::calculateTax($interest, $taxRate);
            $netInterest = self::netInterest($interest, $tax);

            $newAmount = $fdr->amount + $netInterest;

            // Old FDR status
            $fdr->update(['status' => 'renewed']);

            // Fund update (interest added)
            $fdr->fund->adjustBalance($netInterest, 'add');

            // Bank stays same investment (re-invested)

            // 🧾 Transaction log
            Transaction::create([
                'fdr_id' => $fdr->id,
                'type' => 'renew',
                'principal' => $fdr->amount,
                'interest' => $interest,
                'tax' => $tax,
                'duty' => 0,
                'net_amount' => $newAmount,
                'transaction_date' => now(),
                'remarks' => 'FDR Renewed',
            ]);

            // Create new FDR
            return Fdr::create([
                'fdr_number' => 'FDR-' . time(),
                'fund_id' => $fdr->fund_id,
                'bank_id' => $fdr->bank_id,
                'amount' => $newAmount,
                'interest_rate' => $fdr->interest_rate,
                'start_date' => now(),
                'tenure' => $fdr->tenure,
                'maturity_date' => self::maturityDate(now(), $fdr->tenure),
                'status' => 'active',
            ]);
        });
    }

    /**
     * ENCASH FDR
     */
    public static function encash(Fdr $fdr, float $taxRate = 10): array
    {
        return DB::transaction(function () use ($fdr, $taxRate) {

            $interest = self::calculateInterest(
                $fdr->amount,
                $fdr->interest_rate,
                $fdr->tenure
            );

            $tax = self::calculateTax($interest, $taxRate);
            $netInterest = self::netInterest($interest, $tax);

            $totalReturn = $fdr->amount + $netInterest;

            // Fund return
            $fdr->fund->adjustBalance($totalReturn, 'add');

            // Bank investment reduce
            $fdr->bank->adjustInvestment($fdr->amount, 'subtract');

            // Status update
            $fdr->update(['status' => 'encashed']);

            // 🧾 Transaction log
            Transaction::create([
                'fdr_id' => $fdr->id,
                'type' => 'encash',
                'principal' => $fdr->amount,
                'interest' => $interest,
                'tax' => $tax,
                'duty' => 0,
                'net_amount' => $totalReturn,
                'transaction_date' => now(),
                'remarks' => 'FDR Encashed',
            ]);

            return [
                'principal' => $fdr->amount,
                'interest' => $interest,
                'tax' => $tax,
                'net_interest' => $netInterest,
                'total_return' => $totalReturn,
            ];
        });
    }
}