<?php

namespace App\Filament\Resources\PlatformTransactionResource\Widgets;

use App\Models\PlatformTransaction;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Carbon\Carbon;

class PlatformTransactionChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Platform Transaction Chart';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?string $maxHeight = '400px';

    public ?string $filter = 'month';

    protected function getData(): array
    {
        // Get the active filter
        $activeFilter = $this->filter;
        
        // Determine date range based on filter
        [$startDate, $endDate] = $this->getDateRange($activeFilter);
        
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
                        '#06b6d4', // cyan
                        '#84cc16', // lime
                        '#f97316', // amber
                    ],
                    'borderColor' => [
                        '#059669',
                        '#dc2626',
                        '#2563eb',
                        '#ea580c',
                        '#9333ea',
                        '#0891b2',
                        '#65a30d',
                        '#ea580c',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => array_keys($data),
        ];
    }

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'yesterday' => 'Yesterday', 
            'week' => 'This Week',
            'last_week' => 'Last Week',
            'month' => 'This Month',
            'last_month' => 'Last Month',
            'quarter' => 'This Quarter',
            'last_quarter' => 'Last Quarter',
            'year' => 'This Year',
            'last_year' => 'Last Year',
        ];
    }

    protected function getDateRange(string $filter): array
    {
        $now = now();
        
        return match ($filter) {
            'today' => [
                $now->copy()->startOfDay(),
                $now->copy()->endOfDay(),
            ],
            'yesterday' => [
                $now->copy()->subDay()->startOfDay(),
                $now->copy()->subDay()->endOfDay(),
            ],
            'week' => [
                $now->copy()->startOfWeek(),
                $now->copy()->endOfWeek(),
            ],
            'last_week' => [
                $now->copy()->subWeek()->startOfWeek(),
                $now->copy()->subWeek()->endOfWeek(),
            ],
            'month' => [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
            ],
            'last_month' => [
                $now->copy()->subMonth()->startOfMonth(),
                $now->copy()->subMonth()->endOfMonth(),
            ],
            'quarter' => [
                $now->copy()->startOfQuarter(),
                $now->copy()->endOfQuarter(),
            ],
            'last_quarter' => [
                $now->copy()->subQuarter()->startOfQuarter(),
                $now->copy()->subQuarter()->endOfQuarter(),
            ],
            'year' => [
                $now->copy()->startOfYear(),
                $now->copy()->endOfYear(),
            ],
            'last_year' => [
                $now->copy()->subYear()->startOfYear(),
                $now->copy()->subYear()->endOfYear(),
            ],
            default => [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
            ],
        };
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            return context.label + ": $" + context.parsed.toLocaleString();
                        }'
                    ],
                ],
            ],
        ];
    }

    public function getHeading(): string
    {
        $filterLabel = match ($this->filter) {
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            'week' => 'This Week', 
            'last_week' => 'Last Week',
            'month' => 'This Month',
            'last_month' => 'Last Month',
            'quarter' => 'This Quarter',
            'last_quarter' => 'Last Quarter',
            'year' => 'This Year',
            'last_year' => 'Last Year',
            default => 'This Month',
        };
        
        return "Platform Transactions - {$filterLabel}";
    }
}