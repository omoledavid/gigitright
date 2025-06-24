<x-filament-panels::page>
    <div class="space-y-6">
        @if ($record->status !== 'closed')
            <form wire:submit.prevent="submitReply">
                {{ $this->form }}
                <div class="mt-4" style="margin-top: 15px">
                    <x-filament::button type="submit">
                        Send Reply
                    </x-filament::button>
                </div>
            </form>
        @else
            <div class="p-4 text-center bg-gray-100 rounded-lg dark:bg-gray-800">
                <p class="text-gray-600 dark:text-gray-400">This ticket is closed. You cannot reply.</p>
            </div>
        @endif

        <div class="mb-4">
            <h2 class="text-xl font-bold">Messages</h2>
            <div class="mt-4 space-y-4 overflow-y-auto max-h-96 rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                @foreach ($record->messages->sortBy('created_at') as $message)
                    <div @class([
                        'flex w-full',
                        'justify-end' => $message->sender?->is_admin,
                        'justify-start' => !$message->sender?->is_admin,
                    ])>
                        <div @class([
                            'p-4 rounded-lg max-w-2xl',
                            'bg-gray-100 dark:bg-gray-800' => $message->sender?->is_admin,
                            'bg-blue-100 dark:bg-gray-900' => !$message->sender?->is_admin,
                        ])>
                            <div class="flex items-center justify-between gap-4">
                                <p class="font-bold">
                                    {{ $message->sender?->name ?? 'User' }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $message->created_at->format('M d, Y H:i A') }}
                                </p>
                            </div>
                            <div class="mt-2 prose dark:prose-invert max-w-none">
                                {!! $message->message !!}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-filament-panels::page>
