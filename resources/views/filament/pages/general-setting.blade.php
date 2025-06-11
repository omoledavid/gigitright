<x-filament-panels::page>
    <x-filament-panels::form wire:submit="submit" class="space-y-6">
        {{ $this->form }}
        <x-filament::button type="submit">Save Changes</x-filament::button>
    </x-filament-panels::form>
</x-filament-panels::page>
