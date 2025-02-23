<div class="flex items-center space-x-2">
    @if ($getRecord()->status === 'completed')
        <a href="{{ \App\Filament\Pages\Experiments\Sessions\ExperimentSessionExport::getUrl(['record' => $getRecord()->id]) }}"
            class="inline-flex items-center px-2 py-1 text-sm font-medium text-success-600 bg-success-50 rounded hover:bg-success-100">
            <x-heroicon-o-arrow-down-tray class="w-4 h-4 mr-1" />
            {{ __('filament.pages.experiments_sessions.actions.export') }}
        </a>

        <a href="{{ \App\Filament\Pages\Experiments\Sessions\ExperimentSessionDetails::getUrl(['record' => $getRecord()->id]) }}"
            class="inline-flex items-center px-2 py-1 text-sm font-medium text-blue-600 bg-blue-50 rounded hover:bg-blue-100">
            <x-heroicon-o-eye class="w-4 h-4 mr-1" />
            {{ __('filament.pages.experiments_sessions.actions.details') }}
        </a>
    @endif

    @if ($getRecord()->experiment && auth()->id() === $getRecord()->experiment->created_by)
        <button type="button"
            wire:click="$dispatch('open-modal', { id: 'confirm-delete-session-{{ $getRecord()->id }}' })"
            class="inline-flex items-center px-2 py-1 text-sm font-medium text-danger-600 bg-danger-50 rounded hover:bg-danger-100">
            <x-heroicon-o-trash class="w-4 h-4 mr-1" />
            {{ __('filament.pages.experiments_sessions.actions.delete') }}
        </button>

        <x-filament::modal id="confirm-delete-session-{{ $getRecord()->id }}" width="xl">
            <x-slot name="heading">
                Confirmer la suppression - {{ $getRecord()->participant_number }}
            </x-slot>
            <x-slot name="description">
                Êtes-vous sûr de vouloir supprimer cette session ? Cette action est irréversible.
            </x-slot>
            <x-slot name="footer">
                <div class="flex justify-end gap-x-2">
                    <x-filament::button type="button" color="gray"
                        wire:click="$dispatch('close-modal', { id: 'confirm-delete-session-{{ $getRecord()->id }}' })">
                        Annuler
                    </x-filament::button>
                    <x-filament::button type="button" color="danger"
                        wire:click="deleteSession({{ $getRecord()->id }})">
                        Supprimer
                    </x-filament::button>
                </div>
            </x-slot>
        </x-filament::modal>
    @endif
</div>
