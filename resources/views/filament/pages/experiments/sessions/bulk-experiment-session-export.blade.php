<x-filament-panels::page>
    <form wire:submit="export">
        {{ $this->form }}

        <div class="mt-6 flex justify-between items-center">
            <x-filament::button type="submit" size="lg" class="bg-primary-600">
                {{ trans_choice('pages.bulk_experiment_session_export.actions.export_selected_sessions', count($recordIds), ['count' => count($recordIds)]) }}
            </x-filament::button>

            <x-filament::button tag="a"
                href="{{ route('filament.admin.resources.experiment-sessions.index', ['record' => $this->experiment_id]) }}"
                color="gray">
                {{ __('pages.bulk_experiment_session_export.actions.back_to_list') }}
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
