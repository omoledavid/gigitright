<?php

namespace App\Filament\Resources;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Filament\Resources\AdminUserResource\Pages;
use App\Filament\Resources\AdminUserResource\RelationManagers;
use App\Models\AdminUser;
use App\Models\User;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class AdminUserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'Admins';
    protected static ?string $navigationGroup = 'User Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
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
                    Tables\Actions\Action::make('remove_admin')
                        ->icon('heroicon-o-shield-exclamation')
                        ->color('secondary')
                        ->requiresConfirmation()
                        ->label('Remove Admin')
                        ->visible(fn(User $record) => $record->is_admin)
                        ->action(function (User $record) {
                            $record->update(['is_admin' => false, 'role' => UserRole::FREELANCER]);
                        }),
                    Tables\Actions\Action::make('reset_password')
                        ->icon('heroicon-o-key')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->label('Reset Password')
                        ->visible(fn(User $record) => true)
                        ->action(function (User $record) {
                            $newPassword = Str::random(10);
                            $record->update(['password' => bcrypt($newPassword)]);
                            \Filament\Notifications\Notification::make()
                                ->title('Password Reset')
                                ->body("The new password for {$record->username} is: {$newPassword}")
                                ->success()
                                ->send();
                        }),
                        Tables\Actions\Action::make('assign_shield_role')
                            ->icon('heroicon-o-user-plus')
                            ->color('primary')
                            ->requiresConfirmation()
                            ->label('Assign Shield Role')
                            ->visible(fn(User $record) => true)
                            ->form([
                                Forms\Components\Select::make('role')
                                    ->label('Shield Role')
                                    ->options(
                                        collect(Utils::getRoleModel()::all())
                                            ->pluck('name', 'id')
                                            ->toArray()
                                    )
                                    ->required(),
                            ])
                            ->action(function (array $data, User $record) {
                                $roleModel = Utils::getRoleModel()::find($data['role']);
                                if ($roleModel) {
                                    $record->syncRoles([$roleModel->name]);
                                    \Filament\Notifications\Notification::make()
                                        ->title('Role Assigned')
                                        ->body("Shield role '{$roleModel->name}' assigned to {$record->username}.")
                                        ->success()
                                        ->send();
                                }
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
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_admin', true);
    }
    public static function getNavigationLabel(): string
    {
        return 'Admins';
    }
    public static function getNavigationBadge(): ?string
    {
        return Utils::isResourceNavigationBadgeEnabled()
            ? strval(static::getEloquentQuery()->count())
            : null;
    }

    // public static function getNavigationGroup(): ?string
    // {
    //     return 'User Management'; // Optional: custom group
    // }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdminUsers::route('/'),
            'create' => Pages\CreateAdminUser::route('/create'),
            'edit' => Pages\EditAdminUser::route('/{record}/edit'),
        ];
    }
}
