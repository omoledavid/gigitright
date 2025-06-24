<?php

namespace App\Filament\Resources\SupportTicketResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SupportTicketStat extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Tickets', \App\Models\SupportTicket::count()),
            Stat::make('Opened Tickets', \App\Models\SupportTicket::where('status', 'opened')->count()),
            Stat::make('Closed Tickets', \App\Models\SupportTicket::where('status', 'closed')->count()),
        ];
    }
}
