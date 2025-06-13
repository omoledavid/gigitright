<?php

namespace App\Filament\Resources\UserResource\Widgets;

use App\Enums\UserStatus;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserOverview extends BaseWidget
{
    public static function canView(): bool
    {
        return auth()->user()?->can('widget_UserOverview'); 
    }
    protected function getStats(): array
    {
        return [
            Stat::make('All Users', User::query()->count())
                ->description('Total registered users')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('gray')
                ->icon('heroicon-o-users')
                ->chart(User::query()
                    ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->limit(8)
                    ->pluck('count')
                    ->toArray())
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ]),

            Stat::make('Active Users', User::query()->where('status', UserStatus::ACTIVE)->count())
                ->description('Currently active users')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->icon('heroicon-o-check-badge')
                ->chart(User::query()
                    ->where('status', UserStatus::ACTIVE)
                    ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->limit(8)
                    ->pluck('count')
                    ->toArray())
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ]),

            Stat::make('Inactive Users', User::query()->where('status', UserStatus::INACTIVE)->count())
                ->description('Currently inactive users')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('warning')
                ->icon('heroicon-o-clock')
                ->chart(User::query()
                    ->where('status', UserStatus::INACTIVE)
                    ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->limit(8)
                    ->pluck('count')
                    ->toArray())
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ]),

            Stat::make('Banned Users', User::query()->where('status', UserStatus::BLOCKED)->count())
                ->description('Users with banned status')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger')
                ->icon('heroicon-o-trash')
                ->chart(User::query()
                    ->where('status', UserStatus::BLOCKED)
                    ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->limit(8)
                    ->pluck('count')
                    ->toArray())
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ]),
        Stat::make('Total Clients', User::query()->where('role', 'client')->count())
            ->description('Registered clients')
            ->descriptionIcon('heroicon-m-users')
            ->color('primary')
            ->icon('heroicon-o-user-group')
            ->chart(User::query()
                ->where('role', 'client')
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->limit(8)
                ->pluck('count')
                ->toArray())
            ->extraAttributes([
                'class' => 'cursor-pointer',
            ]),

        Stat::make('Total Freelancers', User::query()->where('role', 'freelancer')->count())
            ->description('Registered freelancers')
            ->descriptionIcon('heroicon-m-users')
            ->color('info')
            ->icon('heroicon-o-user')
            ->chart(User::query()
                ->where('role', 'freelancer')
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
