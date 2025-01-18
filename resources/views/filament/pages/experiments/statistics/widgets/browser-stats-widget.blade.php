<x-filament-widgets::widget>
    <x-filament::section>
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Navigateur
                        </th>
                        <th class="px-4 py-2 text-right text-sm font-medium text-gray-500 dark:text-gray-400">
                            Utilisations</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Systèmes
                            d'exploitation</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Types
                            d'appareils</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Résolutions
                        </th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Mode
                            sombre/clair</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($browserStats as $browser => $stats)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                                {{ $browser }}
                            </td>
                            <td class="px-4 py-2 text-sm text-right text-gray-900 dark:text-gray-100">
                                {{ $stats['count'] }} ({{ $stats['percentage'] }}%)
                            </td>
                            <td class="px-4 py-2 text-sm">
                                <div class="space-y-1">
                                    @foreach ($stats['operating_systems'] as $os => $count)
                                        <div class="text-gray-600 dark:text-gray-400">
                                            {{ $os }}: {{ $count }}
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-2 text-sm">
                                <div class="space-y-1">
                                    @foreach ($stats['device_types'] as $device => $count)
                                        <div class="text-gray-600 dark:text-gray-400">
                                            {{ $device }}: {{ $count }}
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-2 text-sm">
                                <div class="space-y-1">
                                    @foreach ($stats['screen_sizes'] as $size => $count)
                                        <div class="text-gray-600 dark:text-gray-400">
                                            {{ $size }}: {{ $count }}
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-2 text-sm">
                                <div class="space-y-1">
                                    @if ($stats['light_mode'] > 0)
                                        <div class="text-gray-600 dark:text-gray-400">
                                            Clair: {{ $stats['light_mode'] }}
                                        </div>
                                    @endif
                                    @if ($stats['dark_mode'] > 0)
                                        <div class="text-gray-600 dark:text-gray-400">
                                            Sombre: {{ $stats['dark_mode'] }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-2 text-sm text-center text-gray-500 dark:text-gray-400">
                                Aucune donnée disponible
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
