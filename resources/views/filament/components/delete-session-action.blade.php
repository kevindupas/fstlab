<x-filament::modal id="delete-session-{{ $experimentSession->id }}" width="md">
    <x-slot name="heading">
        {{ __('filament.pages.experiments_sessions.actions.delete') }}
    </x-slot>

    <x-slot name="description">
        {{ __('filament.pages.experiments_sessions.actions.delete_confirmation', ['participant_number' => $experimentSession->participant_number]) }}
    </x-slot>

    <x-slot name="footer">
        <x-filament::button type="button" color="gray" x-on:click="close">
            {{ __('filament.forms.actions.cancel') }}
        </x-filament::button>

        <form action="{{ route('filament.admin.resources.experiment-sessions.delete', $experimentSession) }}"
            method="POST" class="inline">
            @csrf
            @method('DELETE')
            <x-filament::button type="submit" color="danger">
                {{ __('filament.forms.actions.confirm') }}
            </x-filament::button>
        </form>
    </x-slot>
</x-filament::modal>
