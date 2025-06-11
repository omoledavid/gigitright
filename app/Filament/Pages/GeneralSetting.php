<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\GeneralSetting as GeneralSettingModel;
use Filament\Forms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class GeneralSetting extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static string $view = 'filament.pages.general-setting';
    protected static ?string $navigationLabel = 'General Settings';
    protected static ?string $title = 'Edit General Settings';

    public ?array $data = [];
    public function mount(): void
    {
        $settings = GeneralSettingModel::first();

        $this->form->fill($settings ? $settings->toArray() : []);
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\Section::make('Basic Information')
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('site_name')
                                        ->label('Site Name')
                                        ->required(),
                                    Forms\Components\TextInput::make('email_from')
                                        ->label('Site Email')
                                        ->email()
                                        ->required(),
                                    Forms\Components\TextInput::make('phone_number')
                                        ->label('Phone Number')
                                        ->tel()
                                        ->nullable(),
                                    Forms\Components\TextInput::make('alt_phone_number')
                                        ->label('Alternate Phone Number')
                                        ->tel()
                                        ->nullable(),
                                    Forms\Components\Textarea::make('address')
                                        ->label('Address')
                                        ->rows(3)
                                        ->nullable(),
                                    Forms\Components\Textarea::make('site_description')
                                        ->label('Site Description')
                                        ->rows(3)
                                        ->nullable(),
                                ]),
                        ]),
                    Forms\Components\Section::make('Logo and Favicon')
                        ->schema([
                            Forms\Components\Grid::make(3)
                                ->schema([
                                    Forms\Components\FileUpload::make('logo')
                                        ->directory('logo')
                                        ->label('Logo'),
                                    Forms\Components\FileUpload::make('dark_logo')
                                        ->directory('logo')
                                        ->label('Dark Logo'),
                                    Forms\Components\FileUpload::make('favicon')
                                        ->directory('logo')
                                        ->label('Favicon'),
                                ]),
                        ]),
                    Forms\Components\Section::make('Social Media Links')
                        ->schema([
                            Forms\Components\Grid::make(3)
                                ->schema([
                                    Forms\Components\TextInput::make('facebook')
                                        ->label('Facebook')
                                        ->url()
                                        ->placeholder('https://facebook.com/your-page')
                                        ->nullable(),
                                    Forms\Components\TextInput::make('twitter')
                                        ->label('Twitter')
                                        ->url()
                                        ->placeholder('https://twitter.com/your-handle')
                                        ->nullable(),
                                    Forms\Components\TextInput::make('instagram')
                                        ->label('Instagram')
                                        ->url()
                                        ->placeholder('https://instagram.com/your-profile')
                                        ->nullable(),
                                    Forms\Components\TextInput::make('linkedin')
                                        ->label('LinkedIn')
                                        ->url()
                                        ->placeholder('https://linkedin.com/in/your-profile')
                                        ->nullable(),
                                    Forms\Components\TextInput::make('youtube')
                                        ->label('YouTube')
                                        ->url()
                                        ->placeholder('https://youtube.com/@your-channel')
                                        ->nullable(),
                                ]),
                        ]),
                    Forms\Components\Section::make('System Configuration')
                        ->schema([
                            Forms\Components\Grid::make(5)
                                ->schema([
                                    Forms\Components\Toggle::make('maintenance_mode')
                                        ->label('Maintenance Mode')
                                        ->default(false)
                                        ->inline(false)
                                        ->onColor('success')
                                        ->offColor('danger')
                                        ->extraAttributes(['class' => 'rounded-lg']),
                                    Forms\Components\Toggle::make('login_status')
                                        ->label('Login Status')
                                        ->default(true)
                                        ->inline(false)
                                        ->onColor('success')
                                        ->offColor('danger')
                                        ->extraAttributes(['class' => 'rounded-lg']),
                                    Forms\Components\Toggle::make('register_status')
                                        ->label('Register Status')
                                        ->default(true)
                                        ->inline(false)
                                        ->onColor('success')
                                        ->offColor('danger')
                                        ->extraAttributes(['class' => 'rounded-lg']),
                                    Forms\Components\Toggle::make('deposit_status')
                                        ->label('Deposit Status')
                                        ->default(true)
                                        ->inline(false)
                                        ->onColor('success')
                                        ->offColor('danger')
                                        ->extraAttributes(['class' => 'rounded-lg']),
                                    Forms\Components\Toggle::make('withdraw_status')
                                        ->label('Withdraw Status')
                                        ->default(true)
                                        ->inline(false)
                                        ->onColor('success')
                                        ->offColor('danger')
                                        ->extraAttributes(['class' => 'rounded-lg']),
                                ]),
                        ]),
                    Forms\Components\ViewField::make('global_shortcodes')
                        ->label('Global Shortcodes')
                        ->view('forms.components.global-shortcodes')
                        ->columnSpanFull(),
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Textarea::make('email_template')
                                ->rows(15)
                                ->label('Email Template')
                                ->columnSpan(1)
                                ->live()
                                ->afterStateUpdated(function ($state) {
                                    Log::info('Email template updated:', ['state' => $state]);
                                }),
                            Forms\Components\ViewField::make('email_preview')
                                ->label('Email Preview')
                                ->view('forms.components.email-preview')
                                ->viewData([
                                    'email_template' => fn() => $this->data['email_template'] ?? '',
                                    'site_name' => fn() => $this->data['site_name'] ?? config('app.name'),
                                    'email_from' => fn() => $this->data['email_from'] ?? config('mail.from.address'),
                                ])
                                ->columnSpan(1),
                        ])
                        ->columnSpanFull(),
                ])
        ];
    }

    protected function getFormModel(): string
    {
        return GeneralSettingModel::class;
    }

    protected function getFormStatePath(): string
    {
        return 'data';
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        $settings = GeneralSettingModel::first();

        if ($settings) {
            $settings->update($data);
        } else {
            GeneralSettingModel::create($data);
        }

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }
}
