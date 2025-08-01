<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Page as Mypage;
use Filament\Forms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Section as FormSection;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;

class Terms extends Page implements HasForms
{
    use InteractsWithForms;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.terms';
    protected static ?string $navigationGroup = 'Pages';
    public ?array $data = [];
    public static function canAccess(): bool
    {
        return auth()->user()?->can('page_Terms'); 
    }

    public function mount(): void
    {
        $settings = Mypage::where('page_key', 'terms')->first();

        $this->form->fill($settings ? $settings->toArray() : []);
    }
    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Grid::make(2)
                ->schema([
                    FormSection::make('Terms and Condition information')
                        ->columns(2)
                        ->schema([
                            TextInput::make('content.title')
                                ->label('Title')
                                ->maxLength(255),

                            TextInput::make('content.subtitle')
                                ->label('Subtitle')
                                ->maxLength(500),
                            Textarea::make('content.description')
                                ->label('Description')
                                ->rows(3)
                                ->columnSpanFull()
                                ->maxLength(500),
                            RichEditor::make('content.body')
                                ->label('Content body')
                                ->columnSpanFull()
                        ]),
                ])
        ];
    }
    protected function getFormModel(): string
    {
        return Mypage::class;
    }
    protected function getFormStatePath(): string
    {
        return 'data';
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        $settings = Mypage::where('page_key', 'terms')->first();

        if ($settings) {
            $settings->update($data);
        } else {
            Mypage::create($data);
        }

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }
}
