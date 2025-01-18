<x-filament-widgets::widget>
    <x-filament::section>
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Nom</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Type</th>
                        <th class="px-4 py-2 text-right text-sm font-medium text-gray-500 dark:text-gray-400">
                            Lectures/Vues</th>
                        <th class="px-4 py-2 text-right text-sm font-medium text-gray-500 dark:text-gray-400">
                            Déplacements</th>
                        <th class="px-4 py-2 text-right text-sm font-medium text-gray-500 dark:text-gray-400">Chang.
                            groupes</th>
                        <th class="px-4 py-2 text-right text-sm font-medium text-gray-500 dark:text-gray-400">Total
                            interactions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($mediaStats as $media)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                                {{ basename($media['name']) }}
                            </td>
                            <td class="px-4 py-2 text-sm">
                                <span
                                    class="px-2 py-1 rounded-full text-xs
                                    @if ($media['type'] === 'son') bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100
                                    @else
                                        bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100 @endif">
                                    {{ ucfirst($media['type']) }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-sm text-right text-gray-900 dark:text-gray-100">
                                {{ $media['view_count'] }}
                            </td>
                            <td class="px-4 py-2 text-sm text-right text-gray-900 dark:text-gray-100">
                                {{ $media['move_count'] }}
                            </td>
                            <td class="px-4 py-2 text-sm text-right text-gray-900 dark:text-gray-100">
                                {{ $media['group_changes'] }}
                            </td>
                            <td class="px-4 py-2 text-sm text-right font-medium text-gray-900 dark:text-gray-100">
                                {{ $media['total_interactions'] }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-2 text-sm text-center text-gray-500 dark:text-gray-400">
                                Aucune donnée disponible
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
