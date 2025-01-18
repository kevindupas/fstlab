<x-filament-widgets::widget>
    <x-filament::section>
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Dimensions
                            (cm)</th>
                        <th class="px-4 py-2 text-right text-sm font-medium text-gray-500 dark:text-gray-400">
                            Utilisations</th>
                        <th class="px-4 py-2 text-right text-sm font-medium text-gray-500 dark:text-gray-400">Ratio</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Dimensions
                            (px)</th>
                        <th class="px-4 py-2 text-right text-sm font-medium text-gray-500 dark:text-gray-400">DPI</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Tailles
                            d'écran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($canvasStats as $size => $canvases)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                                {{ $size }} cm
                            </td>
                            <td class="px-4 py-2 text-sm text-right text-gray-900 dark:text-gray-100">
                                {{ $canvases->count() }}
                            </td>
                            <td class="px-4 py-2 text-sm text-right text-gray-900 dark:text-gray-100">
                                {{ $canvases->first()['ratio'] }}
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">
                                {{ $canvases->first()['dimensions_px']['width'] }}x{{ $canvases->first()['dimensions_px']['height'] }}
                                px
                            </td>
                            <td class="px-4 py-2 text-sm text-right text-gray-600 dark:text-gray-400">
                                {{ $canvases->first()['dpi'] }}
                            </td>
                            <td class="px-4 py-2 text-sm">
                                <div class="space-y-1">
                                    @foreach ($canvases->groupBy(fn($c) => "{$c['screen']['width']}x{$c['screen']['height']}") as $screenSize => $count)
                                        <div class="text-gray-600 dark:text-gray-400">
                                            {{ $screenSize }}: {{ $count->count() }}
                                        </div>
                                    @endforeach
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
