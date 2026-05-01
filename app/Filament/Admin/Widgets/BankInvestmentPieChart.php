<?php
namespace App\Filament\Admin\Widgets;

use App\Models\Fdr;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class BankInvestmentPieChart extends ChartWidget
{
    protected ?string $heading = 'Govt vs Private Bank Investment';

    protected function getData(): array
    {
        $data = Fdr::join('banks', 'fdrs.bank_id', '=', 'banks.id')
            ->select(
                'banks.type',
                DB::raw('SUM(fdrs.amount) as total')
            )
            ->groupBy('banks.type')
            ->pluck('total', 'banks.type');

        $govt = $data['govt'] ?? 0;
        $private = $data['private'] ?? 0;

        return [
            'datasets' => [
                [
                    'data' => [$govt, $private],

                    // 🎨 COLORFUL (Gradient style)
                    'backgroundColor' => [
                        '#22c55e', // green (Govt)
                        '#3b82f6', // blue (Private)
                    ],

                    // ✨ Border + hover effect
                    'borderColor' => ['#16a34a', '#1d4ed8'],
                    'borderWidth' => 2,
                    'hoverOffset' => 10,
                ],
            ],
            'labels' => ['Govt Banks', 'Private Banks'],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}