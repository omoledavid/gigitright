<?php

namespace App\Filament\Resources\JobResource\Widgets;

use App\Models\Job;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class JobStatOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('All Jobs', Job::query()->count())
                ->description('Total posted jobs')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('gray')
                ->icon('heroicon-o-briefcase')
                ->chart(Job::query()
                    ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->limit(8)
                    ->pluck('count')
                    ->toArray())
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ]),

            Stat::make('Open Jobs', Job::query()->where('status', 'open')->count())
                ->description('Currently open jobs')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->icon('heroicon-o-check-badge')
                ->chart(Job::query()
                    ->where('status', 'open')
                    ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->limit(8)
                    ->pluck('count')
                    ->toArray())
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ]),

            Stat::make('Closed Jobs', Job::query()->where('status', 'closed')->count())
                ->description('Currently closed jobs')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->chart(Job::query()
                    ->where('status', 'closed')
                    ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->limit(8)
                    ->pluck('count')
                    ->toArray())
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ]),
        ];
    }
}
