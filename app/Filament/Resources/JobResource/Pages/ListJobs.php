<?php

namespace App\Filament\Resources\JobResource\Pages;

use App\Filament\Resources\JobResource;
use App\Filament\Resources\JobResource\Widgets\JobStatOverview;
use App\Models\Job;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListJobs extends ListRecords
{
    protected static string $resource = JobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    public function getHeaderWidgets(): array
    {
        return [
            JobStatOverview::class
        ];
    }
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(Job::count()),

            'open' => Tab::make('Open')
                ->modifyQueryUsing(function ($query) {
                    $query->where('status', 'open');
                })
                ->icon('heroicon-o-check-badge')
                ->badge(Job::where('status', 'open')->count()),

            'closed' => Tab::make('Closed')
                ->modifyQueryUsing(function ($query) {
                    $query->where('status', 'closed');
                })
                ->icon('heroicon-o-x-circle')
                ->badge(Job::where('status', 'closed')->count()),
        ];
    }
}
