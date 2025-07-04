<?php

namespace App\Filament\Resources\PlatformTransactionResource\Widgets;

use App\Models\PlatformTransaction;
use Filament\Widgets\ChartWidget;

class PlatformTransactionChart extends ChartWidget
{
    protected static ?string $heading = 'Platform Transaction Chart';

    protected function getData(): array
    {
        // Filter by current month
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        $data = PlatformTransaction::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('type, SUM(amount) as total')
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Platform Transaction Amounts',
                    'data' => array_values($data),
                    'backgroundColor' => [
                        '#10b981', // green
                        '#ef4444', // red
                        '#3b82f6', // blue
                        '#f59e42', // orange
                        '#a78bfa', // purple
                    ],
                ],
            ],
            'labels' => array_keys($data),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
