<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Fdr;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MonthlyInvestmentLineChart extends ChartWidget
{
    protected ?string $heading = 'Monthly Investment Trend';

    protected function getData(): array
    {
        $data = Fdr::select(
                DB::raw('MONTH(maturity_date) as month'),
                DB::raw('SUM(amount) as total')
            )
            ->whereYear('maturity_date', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Investment (BDT)',
                    'data' => $data->pluck('total'),

                    // 🎨 COLORS
                    'borderColor' => '#3b82f6', // blue line
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)', // light fill
                    'fill' => true,

                    // ✨ STYLE
                    'tension' => 0.4, // smooth curve
                    'pointBackgroundColor' => '#2563eb',
                    'pointBorderColor' => '#ffffff',
                    'pointRadius' => 5,
                    'pointHoverRadius' => 7,
                ],
            ],

            // 📅 Month labels (Better format)
            'labels' => $data->pluck('month')->map(fn ($m) =>
                \Carbon\Carbon::create()->month($m)->format('M')
            ),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}