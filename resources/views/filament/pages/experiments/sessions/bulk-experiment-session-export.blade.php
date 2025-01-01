<x-filament-panels::page>
    <form wire:submit="export">
        {{ $this->form }}

        <div class="mt-6 flex justify-between items-center">
            <x-filament::button type="submit" size="lg" class="bg-primary-600">
                Exporter {{ count($recordIds) }} session{{ count($recordIds) > 1 ? 's' : '' }}
            </x-filament::button>

            <x-filament::button tag="a"
                href="{{ route('filament.admin.resources.experiment-sessions.index', ['record' => $this->experiment_id]) }}"
                color="gray">
                Retour à la liste
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
