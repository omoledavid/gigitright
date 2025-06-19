<?php

namespace App\Filament\Resources\PlatformTransactionResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlatformTransactionStat extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Charge Amount', '₦' . number_format(\App\Models\PlatformTransaction::where('type', 'charge')->sum('amount'), 2)),
            Stat::make('Refund Charge Amount', '₦' . number_format(\App\Models\PlatformTransaction::where('type', 'refund')->sum('amount'), 2)),
            Stat::make('Total Transactions', \App\Models\PlatformTransaction::count()),
        ];
    }
}
