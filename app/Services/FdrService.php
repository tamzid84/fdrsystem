<?php

namespace App\Services;

use App\Models\Fdr;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FdrService
{
    /**
     * 🔥 Interest Calculation
     */
    public static function calculateInterest(float $amount, float $rate, int $tenure): float
    {
        return round(($amount * $rate * $tenure) / 1200, 2);
    }

    /**
     * 🔥 Tax (Dynamic from Fund)
     */
    public static function calculateTax(Fdr $fdr, float $interest): float
    {
        $rate = $fdr->fund->tax_rate ?? 10;
        return round(($interest * $rate) / 100, 2);
    }

    /**
     * 🔥 Net Calculation
     */
    public static function calculateNet(float $principal, float $interest, float $tax, float $charge): float
    {
        return round($principal + $interest - $tax - $charge, 2);
    }

    /**
     * 🔥 Maturity Date
     */
    public static function maturityDate($startDate, int $tenure): string
    {
        return Carbon::parse($startDate)->addMonths($tenure)->toDateString();
    }

    /**
     * ==========================================
     * 🔥 CREATE FDR
     * ==========================================
     */
    public static function create(array $data): Fdr
    {
        return DB::transaction(function () use ($data) {

            $data['charge'] = $data['charge'] ?? 0;

            $data['maturity_date'] = self::maturityDate(
                $data['start_date'],
                $data['tenure']
            );

            $data['version'] = 1;

            $fdr = Fdr::create($data);

            // Fund deduction
            $fdr->fund->adjustBalance($fdr->amount, 'subtract');

            // Transaction log
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
     * ==========================================
     * 🔥 RENEW (Principal / Principal+Interest)
     * ==========================================
     */
    public static function renew(Fdr $fdr): Fdr
    {
        return DB::transaction(function () use ($fdr) {

            $interest = self::calculateInterest(
                $fdr->amount,
                $fdr->interest_rate,
                $fdr->tenure
            );

            $tax = self::calculateTax($fdr, $interest);
            $charge = $fdr->charge ?? 0;

            $net = self::calculateNet($fdr->amount, $interest, $tax, $charge);

            // determine new principal
            $newAmount = ($fdr->renewal_type === 'principal')
                ? $fdr->amount
                : $fdr->amount;

            // profit handling
            $profit = $net - $fdr->amount;
            if ($profit > 0) {
                $fdr->fund->adjustBalance($profit, 'add');
            }

            $fdr->update(['status' => 'renewed']);

            // transaction for old FDR close
            Transaction::create([
                'fdr_id' => $fdr->id,
                'type' => 'renew',
                'principal' => $fdr->amount,
                'interest' => $interest,
                'tax' => $tax,
                'duty' => $charge,
                'net_amount' => $net,
                'transaction_date' => now(),
                'remarks' => 'FDR Renewed',
            ]);

            // NEW FDR (same FDR number)
            $newFdr = Fdr::create([
                'fdr_number' => $fdr->fdr_number, // ✅ SAME NUMBER
                'fund_id' => $fdr->fund_id,
                'bank_id' => $fdr->bank_id,
                'amount' => $newAmount,
                'interest_rate' => $fdr->interest_rate,
                'start_date' => now(),
                'tenure' => $fdr->tenure,
                'charge' => $charge,
                'renewal_type' => $fdr->renewal_type,
                'version' => $fdr->version + 1,
                'maturity_date' => self::maturityDate(now(), $fdr->tenure),
                'status' => 'active',
            ]);

            // 🔥 transaction for NEW FDR
            Transaction::create([
                'fdr_id' => $newFdr->id,
                'type' => 'create',
                'principal' => $newFdr->amount,
                'interest' => 0,
                'tax' => 0,
                'duty' => $charge,
                'net_amount' => $newFdr->amount,
                'transaction_date' => now(),
                'remarks' => 'FDR Created after Renewal',
            ]);

            return $newFdr;
        });
    }

    /**
     * ==========================================
     * 🔥 RENEW WITH NET (COMPOUND)
     * ==========================================
     */
    public static function renewWithNetAmount(Fdr $fdr): Fdr
    {
        return DB::transaction(function () use ($fdr) {

            $interest = self::calculateInterest(
                $fdr->amount,
                $fdr->interest_rate,
                $fdr->tenure
            );

            $tax = self::calculateTax($fdr, $interest);
            $charge = $fdr->charge ?? 0;

            $net = self::calculateNet($fdr->amount, $interest, $tax, $charge);

            $fdr->update(['status' => 'renewed']);

            Transaction::create([
                'fdr_id' => $fdr->id,
                'type' => 'renew',
                'principal' => $fdr->amount,
                'interest' => $interest,
                'tax' => $tax,
                'duty' => $charge,
                'net_amount' => $net,
                'transaction_date' => now(),
                'remarks' => 'Renewed with Net Amount',
            ]);

            $newFdr = Fdr::create([
                'fdr_number' => $fdr->fdr_number, // ✅ SAME NUMBER
                'fund_id' => $fdr->fund_id,
                'bank_id' => $fdr->bank_id,
                'amount' => $net,
                'interest_rate' => $fdr->interest_rate,
                'start_date' => now(),
                'tenure' => $fdr->tenure,
                'charge' => $charge,
                'renewal_type' => 'principal_interest',
                'version' => $fdr->version + 1,
                'maturity_date' => self::maturityDate(now(), $fdr->tenure),
                'status' => 'active',
            ]);

            // 🔥 transaction for NEW FDR
            Transaction::create([
                'fdr_id' => $newFdr->id,
                'type' => 'create',
                'principal' => $newFdr->amount,
                'interest' => 0,
                'tax' => 0,
                'duty' => $charge,
                'net_amount' => $newFdr->amount,
                'transaction_date' => now(),
                'remarks' => 'FDR Created (Compound Renewal)',
            ]);

            return $newFdr;
        });
    }

    /**
     * ==========================================
     * 🔥 ENCASH FDR
     * ==========================================
     */
    public static function encash(Fdr $fdr): array
    {
        return DB::transaction(function () use ($fdr) {

            $interest = self::calculateInterest(
                $fdr->amount,
                $fdr->interest_rate,
                $fdr->tenure
            );

            $tax = self::calculateTax($fdr, $interest);
            $charge = $fdr->charge ?? 0;

            $net = self::calculateNet($fdr->amount, $interest, $tax, $charge);

            // Fund return
            $fdr->fund->adjustBalance($net, 'add');

            $fdr->update(['status' => 'encashed']);

            Transaction::create([
                'fdr_id' => $fdr->id,
                'type' => 'encash',
                'principal' => $fdr->amount,
                'interest' => $interest,
                'tax' => $tax,
                'duty' => $charge,
                'net_amount' => $net,
                'transaction_date' => now(),
                'remarks' => 'FDR Encashed',
            ]);

            return [
                'principal' => $fdr->amount,
                'interest' => $interest,
                'tax' => $tax,
                'charge' => $charge,
                'net_amount' => $net,
            ];
        });
    }
}