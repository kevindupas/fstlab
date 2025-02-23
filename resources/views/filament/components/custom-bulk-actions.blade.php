<div class="flex items-center space-x-2">
    <button type="button" wire:click="$dispatch('open-modal', { id: 'confirm-delete-selected-sessions' })"
        class="inline-flex items-center px-2 py-1 text-sm font-medium text-danger-600 bg-danger-50 rounded hover:bg-danger-100">
        <x-heroicon-o-trash class="w-4 h-4 mr-1" />
        {{ __('Supprimer la sélection') }}
    </button>

    <x-filament::modal id="confirm-delete-selected-sessions" width="xl">
        <x-slot name="heading">
            Confirmer la suppression des sessions sélectionnées
        </x-slot>
        <x-slot name="description">
            <p>Êtes-vous sûr de vouloir supprimer les sessions sélectionnées ? Cette action est irréversible.</p>

            <div class="mt-2 font-medium">
                Sessions sélectionnées :
                @php
                    $selectedIds = session('selected_sessions', []);
                @endphp

                @if (count($selectedIds) > 0)
                    <span class="text-primary-600">{{ implode(', ', $selectedIds) }}</span>
                @else
                    <span class="text-danger-600">Aucune session sélectionnée</span>
                @endif
            </div>
        </x-slot>
        <x-slot name="footer">
            <div class="flex justify-end gap-x-2">
                <x-filament::button type="button" color="gray"
                    wire:click="$dispatch('close-modal', { id: 'confirm-delete-selected-sessions' })">
                    Annuler
                </x-filament::button>
                <x-filament::button type="button" color="danger" wire:click="deleteSelectedSessions">
                    Supprimer la sélection
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>
</div>
