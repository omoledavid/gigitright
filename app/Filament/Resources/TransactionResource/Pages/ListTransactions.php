<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Filament\Widgets\FinancialStat;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
    public function getHeaderWidgets(): array
    {
        return [
            FinancialStat::class
        ];
    }
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(fn() => \App\Models\Transaction::count()),

            'Pending' => Tab::make('Pending')
                ->modifyQueryUsing(function ($query) {
                    $query->where('status', 'pending');
                })
                ->icon('heroicon-o-clock')
                ->badge(fn() => \App\Models\Transaction::where('status', 'pending')->count()),

            'Completed' => Tab::make('Completed')
                ->modifyQueryUsing(function ($query) {
                    $query->where('status', 'completed');
                })
                ->icon('heroicon-o-check-badge')
                ->badge(fn() => \App\Models\Transaction::where('status', 'completed')->count()),

            'Failed' => Tab::make('Failed')
                ->modifyQueryUsing(function ($query) {
                    $query->where('status', 'failed');
                })
                ->icon('heroicon-o-x-circle')
                ->badge(fn() => \App\Models\Transaction::where('status', 'failed')->count()),
            'Refunded' => Tab::make('Refunded')
                ->modifyQueryUsing(function ($query) {
                    $query->where('status', 'refunded');
                })
                ->icon('heroicon-o-arrow-uturn-left')
                ->badge(fn() => \App\Models\Transaction::where('status', 'refunded')->count()),
            'Chargeback' => Tab::make('Chargeback')
                ->modifyQueryUsing(function ($query) {
                    $query->where('status', 'chargeback');
                })
                ->icon('heroicon-o-arrow-uturn-left')
                ->badge(fn() => \App\Models\Transaction::where('status', 'chargeback')->count()),
        ];
    }
}
