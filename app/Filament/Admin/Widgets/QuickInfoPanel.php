<?php

namespace App\Filament\Widgets;

use App\Models\Fdr;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class QuickInfoPanel extends BaseWidget
{
    protected function getStats(): array
    {
        // 📅 Next Month Range
        $nextMonthStart = Carbon::now()->addMonth()->startOfMonth();
        $nextMonthEnd = Carbon::now()->addMonth()->endOfMonth();

        // 📅 Today
        $today = Carbon::today();

        // 🔹 Upcoming Maturity (Next Month)
        $upcomingMaturity = Fdr::whereBetween('maturity_date', [$nextMonthStart, $nextMonthEnd])->count();

        // 🔹 Today Renew
        $todayRenew = Fdr::whereDate('start_date', $today)->count();

        // 🔹 Recent Transactions Count (Last 5)
        $recentTransactions = Transaction::latest()->limit(5)->count();

        // 🔹 Upcoming Tax Deduction (Next 7 Days)
        $taxUpcoming = Fdr::whereBetween('maturity_date', [
            Carbon::today(),
            Carbon::today()->addDays(7)
        ])->count();

        return [
            Stat::make('📅 Next Month Maturity', $upcomingMaturity)
                ->description('আগামী মাসে Mature হবে')
                ->color('warning'),

            Stat::make('🔄 Today Renew', $todayRenew)
                ->description('আজকে Renew হওয়ার কথা')
                ->color('success'),

            Stat::make('💸 Recent Transactions', $recentTransactions)
                ->description('শেষ ৫টি ট্রানজেকশন')
                ->color('info'),

            Stat::make('🧾 Tax Deduction', $taxUpcoming)
                ->description('Upcoming (৭ দিনের মধ্যে)')
                ->color('danger'),
        ];
    }
}