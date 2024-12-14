<x-filament-panels::page>
    <form wire:submit="export">
        {{ $this->form }}

        <div class="flex justify-end mt-6 gap-3">
            <x-filament::button
                type="submit"
                color="success"
            >
                Exporter en CSV
            </x-filament::button>


        </div>
    </form>
</x-filament-panels::page>
