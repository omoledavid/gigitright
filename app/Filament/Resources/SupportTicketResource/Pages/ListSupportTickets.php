<?php

namespace App\Filament\Resources\SupportTicketResource\Pages;

use App\Filament\Resources\SupportTicketResource;
use App\Filament\Resources\SupportTicketResource\Widgets\SupportTicketStat;
use App\Models\SupportTicket;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListSupportTickets extends ListRecords
{
    protected static string $resource = SupportTicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
    public function getHeaderWidgets(): array
    {
        return [
            SupportTicketStat::class
        ];
    }
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(SupportTicket::count()),

            'open' => Tab::make('Open')
                ->modifyQueryUsing(function ($query) {
                    $query->where('in_progress', true);
                })
                ->icon('heroicon-o-check-badge')
                ->badge(badge: SupportTicket::where('status', 'open')->count()),

            'closed' => Tab::make('Closed')
                ->modifyQueryUsing(function ($query) {
                    $query->where('status', 'closed');
                })
                ->icon('heroicon-o-x-circle')
                ->badge(SupportTicket::where('status', 'closed')->count()),
        ];
    }
}
