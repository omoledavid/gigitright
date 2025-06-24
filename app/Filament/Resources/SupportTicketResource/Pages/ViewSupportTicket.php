<?php

namespace App\Filament\Resources\SupportTicketResource\Pages;

use App\Filament\Resources\SupportTicketResource;
use App\Models\TicketMessage;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Auth;

class ViewSupportTicket extends ViewRecord implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = SupportTicketResource::class;
    protected static string $view = 'filament.resources.support-ticket-resource.pages.view-support-ticket';

    public ?array $data = [];

    public function mount($record): void
    {
        parent::mount($record);
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\RichEditor::make('reply')
                ->label('Your Reply')
                ->required()
        ];
    }

    public function getFormStatePath(): ?string
    {
        return 'data';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('close_ticket')
                ->label('Close Ticket')
                ->color('danger')
                ->requiresConfirmation()
                ->action(fn() => $this->closeTicket())
                ->visible(fn() => $this->record->status !== 'closed'),
        ];
    }

    public function closeTicket()
    {
        $this->record->update(['status' => 'closed']);
        $this->record->refresh();
        \Filament\Notifications\Notification::make()
            ->title('Ticket Closed')
            ->success()
            ->send();
    }

    public function submitReply()
    {
        $formData = $this->form->getState();

        TicketMessage::create([
            'support_ticket_id' => $this->record->id,
            'sender_id' => auth()->id(),
            'sender_type' => 'App\Models\User', // or dynamically determine if user/admin
            'message' => $formData['reply'],
        ]);

        // Reset form data and fields
        $this->data = [];
        $this->form->fill();

        $this->record->refresh();

        \Filament\Notifications\Notification::make()
            ->title('Reply Sent')
            ->success()
            ->send();
    }
}
