<x-filament-panels::page>
    <form wire:submit="submit">
        {{ $this->form }}

        <x-filament::button
            type="submit"
            class="mt-4">
            {{__('filament.pages.admin_contact.form.submit')}}
        </x-filament::button>
    </form>
</x-filament-panels::page>
