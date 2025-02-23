<x-filament-panels::page>
    <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800">
        <div class="flex items-center mb-4 text-danger-600">
            <x-heroicon-o-exclamation-triangle class="w-6 h-6 mr-2" />
            <h2 class="text-xl font-bold">Confirmation de suppression</h2>
        </div>

        <p class="mb-4">Vous êtes sur le point de supprimer <strong>{{ count($recordIds) }}</strong> session(s). Cette
            action est irréversible.</p>

        <div class="p-4 mb-6 bg-gray-100 rounded-lg dark:bg-gray-700">
            <h3 class="mb-2 text-sm font-medium">Sessions sélectionnées :</h3>
            <div class="max-h-48 overflow-y-auto">
                <ul class="pl-4 list-disc">
                    @foreach ($recordIds as $id)
                        @php
                            $session = \App\Models\ExperimentSession::find($id);
                        @endphp
                        @if ($session)
                            <li class="mb-1">
                                {{ $session->participant_number }}
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    (ID: {{ $id }}, Status: {{ $session->status }})
                                </span>
                            </li>
                        @else
                            <li class="mb-1 text-danger-500">Session ID {{ $id }} introuvable</li>
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <x-filament::button wire:click="deleteSelected" color="danger">
                Supprimer {{ count($recordIds) }} session(s)
            </x-filament::button>

            <x-filament::button tag="a"
                href="{{ route('filament.admin.resources.experiment-sessions.index', ['record' => $experiment_id]) }}"
                color="gray">
                Annuler
            </x-filament::button>
        </div>
    </div>
</x-filament-panels::page>
