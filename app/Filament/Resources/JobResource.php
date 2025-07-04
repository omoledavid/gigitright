<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobResource\Pages;
use App\Filament\Resources\JobResource\RelationManagers;
use App\Models\Job;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JobResource extends Resource
{
    protected static ?string $model = Job::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(191),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('budget')
                    ->numeric(),
                Forms\Components\DatePicker::make('deadline'),
                Forms\Components\TextInput::make('skills')
                    ->maxLength(191),
                Forms\Components\TextInput::make('category_id')
                    ->maxLength(191),
                Forms\Components\TextInput::make('sub_category_id')
                    ->maxLength(191),
                Forms\Components\TextInput::make('duration')
                    ->maxLength(191),
                Forms\Components\Textarea::make('skill_requirements')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('attachments')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('location')
                    ->maxLength(191),
                Forms\Components\TextInput::make('job_type')
                    ->maxLength(191),
                Forms\Components\TextInput::make('visibility')
                    ->required()
                    ->maxLength(191)
                    ->default('public'),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(191)
                    ->default('open'),
                Forms\Components\DateTimePicker::make('start_date'),
                Forms\Components\DateTimePicker::make('end_date'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            Tables\Columns\TextColumn::make('user.name')
                ->numeric()
                ->sortable(),
            Tables\Columns\TextColumn::make('title')
                ->searchable(),
            Tables\Columns\TextColumn::make('budget')
                ->numeric()
                ->prefix('₦')
                ->sortable(),
            Tables\Columns\TextColumn::make('deadline')
                ->date()
                ->sortable(),
            Tables\Columns\TextColumn::make('category.name')
                ->searchable()
                ->sortable()
                ->color('primary')
                ->badge()
                ->extraAttributes([
                'class' => 'font-medium',
                ]),
            Tables\Columns\TextColumn::make('duration')
                ->searchable(),
            Tables\Columns\TextColumn::make('job_type')
                ->searchable(),
            Tables\Columns\TextColumn::make('visibility')
                ->searchable(),
            Tables\Columns\TextColumn::make('status')
                ->searchable()
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                'open' => 'success',
                'closed' => 'danger',
                default => 'gray',
                }),
            Tables\Columns\TextColumn::make('start_date')
                ->dateTime()
                ->toggleable(isToggledHiddenByDefault: true)
                ->sortable(),
            Tables\Columns\TextColumn::make('end_date')
                ->dateTime()
                ->toggleable(isToggledHiddenByDefault: true)
                ->sortable(),
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('updated_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
            //
            ])
            ->actions([
            Tables\Actions\ActionGroup::make([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('close')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn ($record) => $record->status === 'open')
                ->action(function ($record) {
                    $record->update(['status' => 'closed']);
                }),
                Tables\Actions\Action::make('open')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn ($record) => $record->status === 'closed')
                ->action(function ($record) {
                    $record->update(['status' => 'open']);
                }),
            ]),
            ])
            ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
            ]);
        }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobs::route('/'),
            'create' => Pages\CreateJob::route('/create'),
            'edit' => Pages\EditJob::route('/{record}/edit'),
        ];
    }
}
