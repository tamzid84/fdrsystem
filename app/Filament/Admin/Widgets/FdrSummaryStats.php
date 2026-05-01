<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Fdr;
use App\Models\Fund;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class FdrSummaryStats extends BaseWidget
{
    protected function getStats(): array
    {
        // 📊 Core Data
        $totalInvestment = Fdr::where('status', 'active')->sum('amount');
        $totalFunds = Fund::sum('current_balance');

        $funds = Fund::pluck('current_balance', 'name');

        // 🏦 Govt vs Private
        $bankSplit = Fdr::join('banks', 'fdrs.bank_id', '=', 'banks.id')
            ->where('fdrs.status', 'active')
            ->select(
                'banks.type',
                DB::raw('SUM(fdrs.amount) as total')
            )
            ->groupBy('banks.type')
            ->pluck('total', 'type');

        $govt = $bankSplit['govt'] ?? 0;
        $private = $bankSplit['private'] ?? 0;
        $total = $govt + $private;

        $govtPercent = $total > 0 ? round(($govt / $total) * 100, 2) : 0;
        $privatePercent = $total > 0 ? round(($private / $total) * 100, 2) : 0;

        // 📈 Other Metrics
        $activeFdr = Fdr::where('status', 'active')->count();

        $upcoming = Fdr::where('status', 'active')
            ->whereBetween('maturity_date', [now(), now()->addDays(30)])
            ->count();

        $taxYtd = Transaction::whereYear('transaction_date', now()->year)->sum('tax');
        $dutyYear = Transaction::whereYear('transaction_date', now()->year)->sum('duty');

        $netInterest = Transaction::sum(DB::raw('interest - tax - duty'));

        // 🎯 FORMAT FUNCTION (Cr / Lakh)
        $format = function ($amount) {
            if ($amount >= 10000000) {
                return number_format($amount / 10000000, 2) . ' Cr';
            } elseif ($amount >= 100000) {
                return number_format($amount / 100000, 2) . ' Lakh';
            }
            return number_format($amount, 2);
        };

        return [

            // 🔷 KPI
            Stat::make('Total Investment', $format($totalInvestment))
                ->description('Active FDR')
                ->color('primary'),

            Stat::make('Total Funds', $format($totalFunds))
                ->color('success'),

            Stat::make('Net Interest', $format($netInterest))
                ->color('success'),

            // 🔷 FUND WISE
            Stat::make('Membership Fund', $format($funds['Membership Fund'] ?? 0)),
            Stat::make('Replacement Reserve Fund', $format($funds['Replacement Reserve Fund'] ?? 0)),
            Stat::make('Gratuity Fund', $format($funds['Gratuity Fund'] ?? 0)),
            Stat::make('Donation Reserve Fund', $format($funds['Donation Reserve Fund'] ?? 0)),
            Stat::make('Benevolent Fund', $format($funds['Benevolent Fund'] ?? 0)),
            Stat::make('Employee Security Deposit Fund', $format($funds['Employee Security Deposit Fund'] ?? 0)),
            Stat::make('Provident Fund', $format($funds['Provident Fund'] ?? 0)),
            Stat::make("Workmen's Compensation Benefit Fund", $format($funds["Workmen's Compensation Benefit Fund"] ?? 0)),
            Stat::make('Meter Rent Reserve Fund', $format($funds['Meter Rent Reserve Fund'] ?? 0)),
            Stat::make('Temporary Cash Investment', $format($funds['Temporary Cash Investment'] ?? 0)),

            // 🔷 BANK %
            Stat::make('Govt Bank %', $govtPercent . '%')
                ->color('success'),

            Stat::make('Private Bank %', $privatePercent . '%')
                ->color('info'),

            // 🔷 STATUS
            Stat::make('Active FDR', $activeFdr),

            Stat::make('Upcoming (30 Days)', $upcoming)
                ->color('warning'),

            // 🔷 TAX
            Stat::make('Total Tax (YTD)', $format($taxYtd))
                ->color('danger'),

            Stat::make('Excise Duty', $format($dutyYear))
                ->color('gray'),
        ];
    }
}