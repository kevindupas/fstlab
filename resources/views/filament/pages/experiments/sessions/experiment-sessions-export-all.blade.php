<x-filament-panels::page>
    <form wire:submit="export">
        {{ $this->form }}

        <div class="flex justify-end mt-6 gap-3">
            <x-filament::button type="submit" color="success">
                {{ __('pages.experiment_sessions_export_all.actions.export_csv') }}
            </x-filament::button>

            <x-filament::button tag="a"
                href="{{ route('filament.admin.resources.experiment-sessions.index', ['record' => $this->experiment_id]) }}"
                color="gray">
                {{ __('pages.experiment_sessions_export_all.actions.back_to_list') }}
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
