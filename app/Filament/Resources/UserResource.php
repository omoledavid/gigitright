<?php

namespace App\Filament\Resources;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-m-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->maxLength(191),
                Forms\Components\TextInput::make('username')
                    ->required()
                    ->maxLength(191),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(191),
                Forms\Components\DateTimePicker::make('email_verified_at'),
                Forms\Components\Select::make('status')
                    ->options([
                        UserStatus::ACTIVE => 'Active',
                        UserStatus::INACTIVE => 'Inactive',
                    ])
                    ->required()
                    ->default(UserStatus::ACTIVE),
                Forms\Components\Toggle::make('ev')
                    ->label('Email Verified')
                    ->required()
                    ->default(false),
                Forms\Components\Select::make('role')
                    ->options([
                        UserRole::CLIENT => 'Client',
                        UserRole::FREELANCER => 'Freelancer',
                    ])
                    ->required()
                    ->default(UserRole::CLIENT),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('username')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(int $state): string => match ($state) {
                        UserStatus::ACTIVE => 'success',
                        UserStatus::INACTIVE => 'danger',
                        UserStatus::BLOCKED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(int $state): string => match ($state) {
                        UserStatus::ACTIVE => 'Active',
                        UserStatus::INACTIVE => 'Inactive',
                        UserStatus::BLOCKED => 'Blocked',
                        default => 'Unknown',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('role')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'client' => 'Client',
                        'freelancer' => 'Freelancer',
                    ])
                    ->label('Role')
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('block')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn(User $record) => $record->status !== UserStatus::BLOCKED)
                        ->action(function (User $record) {
                            $record->update(['status' => UserStatus::BLOCKED]);
                        }),
                    Tables\Actions\Action::make('unblock')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn(User $record) => $record->status === UserStatus::BLOCKED)
                        ->action(function (User $record) {
                            $record->update(['status' => UserStatus::ACTIVE]);
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
            RelationManagers\ExperienceRelationManager::class,
            RelationManagers\EducationRelationManager::class,
            RelationManagers\PortfolioRelationManager::class,
            RelationManagers\CertificateRelationManager::class,
            RelationManagers\JobsRelationManager::class,
            RelationManagers\PostsRelationManager::class,
            RelationManagers\CommunitiesRelationManager::class,
            RelationManagers\NotificationsRelationManager::class,
            RelationManagers\TransactionsRelationManager::class,
            RelationManagers\ReviewsRelationManager::class,
            RelationManagers\JobApplicationsRelationManager::class,
            RelationManagers\OrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
