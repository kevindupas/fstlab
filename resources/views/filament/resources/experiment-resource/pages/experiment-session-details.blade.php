<x-filament-panels::page>
    {{ $this->sessionInfolist }}

    <!-- Groupes -->
    @if (!empty($groups))
        <div class="-mt-2">
            <div class="bg-white rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-800 p-6">
                <h2 class="text-lg font-medium mb-4">Groupes d'éléments</h2>
                <div class="space-y-4">
                    @foreach ($groups as $group)
                        <div class="border rounded-lg p-4 dark:border-gray-700">
                            <h4 class="text-lg font-medium mb-2 flex items-center">
                                {{ $group->name }}
                                <span class="inline-block w-4 h-4 rounded-full ml-2"
                                    style="background-color: {{ $group->color }}">
                                </span>
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach ($group->elements as $element)
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-md p-3">
                                        <div class="text-sm space-y-2">
                                            <div class="flex flex-col space-y-2">
                                                @if ($session->experiment->type === 'sound')
                                                    <div class="flex items-center space-x-2">
                                                        <audio controls class="w-full">
                                                            <source src="{{ $element->url }}" type="audio/wav">
                                                            Votre navigateur ne supporte pas l'élément audio.
                                                        </audio>
                                                    </div>
                                                @elseif ($session->experiment->type === 'image')
                                                    <div class="flex items-center justify-center">
                                                        <img src="{{ $element->url }}" alt="Image {{ $element->id }}"
                                                            class="max-w-full h-auto rounded-lg shadow-sm" />
                                                    </div>
                                                @endif
                                                <div class="text-xs text-gray-500">
                                                    Position: X={{ $element->x }}, Y={{ $element->y }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div class="-mt-2">
            <div class="bg-white rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-800 p-6">
                <h2 class="text-lg font-medium mb-4">Groupes d'éléments</h2>
                <p class="text-gray-500">Session en cours - Aucun groupe d'éléments disponible pour le moment.</p>
            </div>
        </div>
    @endif


    <!-- Log des actions -->
    @if ($actionsLog->isNotEmpty())
        <div class="-mt-2">
            <div class="bg-white rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-800 p-6">
                <h2 class="text-lg font-medium mb-4">Journal des actions</h2>
                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-700">
                                <th class="px-4 py-2 text-start text-sm font-medium text-gray-500 dark:text-gray-400">ID
                                </th>
                                <th class="px-4 py-2 text-start text-sm font-medium text-gray-500 dark:text-gray-400">
                                    Position X</th>
                                <th class="px-4 py-2 text-start text-sm font-medium text-gray-500 dark:text-gray-400">
                                    Position Y</th>
                                <th class="px-4 py-2 text-start text-sm font-medium text-gray-500 dark:text-gray-400">
                                    Temps
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($actionsLog as $action)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                        {{ $action['id'] }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                        {{ $action['x'] }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                        {{ $action['y'] }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                        {{ $action['time'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="-mt-2">
            <div class="bg-white rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-800 p-6">
                <h2 class="text-lg font-medium mb-4">Journal des actions</h2>
                <p class="text-gray-500">Session en cours - Aucune action enregistrée pour le moment.</p>
            </div>
        </div>
    @endif

    <!-- Notes de l'examinateur -->
    @if ($session->notes)
        <div class="-mt-2">
            <div class="bg-white rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-800 p-6">
                <h2 class="text-lg font-medium mb-4">Notes de l'examinateur</h2>
                <div class="prose dark:prose-invert max-w-none">
                    {{ $session->notes }}
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
