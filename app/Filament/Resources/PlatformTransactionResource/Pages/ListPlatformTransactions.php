<?php

namespace App\Filament\Resources\PlatformTransactionResource\Pages;

use App\Filament\Resources\PlatformTransactionResource;
use App\Filament\Resources\PlatformTransactionResource\Widgets\PlatformTransactionChart;
use App\Filament\Resources\PlatformTransactionResource\Widgets\PlatformTransactionStat;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPlatformTransactions extends ListRecords
{
    protected static string $resource = PlatformTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
    public function getHeaderWidgets(): array
    {
        return [
            PlatformTransactionStat::class,
            PlatformTransactionChart::class
        ];
    }
}
