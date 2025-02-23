<x-filament-panels::page>
    <form wire:submit="delete" class="space-y-6">
        <div class="p-6 bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <p class="text-gray-600 dark:text-gray-400">
                {{ __('filament.pages.experiments_sessions.delete.confirmation', ['number' => $record->participant_number]) }}
            </p>

            <div class="mt-4 flex justify-end gap-x-3">
                <x-filament::button type="button" color="gray" tag="a" :href="route('filament.admin.resources.experiment-sessions.index', [
                    'record' => $record->experiment_id,
                ])">
                    {{ __('filament.pages.experiments_sessions.actions.cancel') }}
                </x-filament::button>

                <x-filament::button type="submit" color="danger">
                    {{ __('filament.pages.experiments_sessions.actions.confirm_delete') }}
                </x-filament::button>
            </div>
        </div>
    </form>
</x-filament-panels::page>
