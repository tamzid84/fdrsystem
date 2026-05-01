<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Fdr;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class BankInvestmentBarChart extends ChartWidget
{
    protected ?string $heading = 'Bank-wise Active Investment';

    protected function getData(): array
    {
        $data = Fdr::join('banks', 'fdrs.bank_id', '=', 'banks.id')
            ->where('fdrs.status', 'active') // ✅ ONLY ACTIVE
            ->select(
                'banks.name',
                DB::raw('SUM(fdrs.amount) as total')
            )
            ->groupBy('banks.name')
            ->orderBy('total', 'desc')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Investment (BDT)',
                    'data' => $data->pluck('total'),

                    // 🎨 Colorful bars (auto multiple colors)
                    'backgroundColor' => [
                        '#3b82f6', // blue
                        '#22c55e', // green
                        '#f59e0b', // yellow
                        '#ef4444', // red
                        '#8b5cf6', // purple
                        '#06b6d4', // cyan
                    ],

                    'borderRadius' => 8, // smooth bar corners
                ],
            ],

            'labels' => $data->pluck('name'),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}