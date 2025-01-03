<x-filament-panels::page>
    @if (!empty($searchTerm))
        <div class="mb-4">
            <div
                class="fi-ta-content rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-x-3 px-4 py-3">
                    <div class="flex flex-1 items-center gap-x-3">
                        <div @class([
                            'fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset',
                            'px-1.5 min-w-[theme(spacing.5)] py-0.5',
                            'bg-success-50 text-success-600 ring-success-600/10 dark:bg-success-400/10 dark:text-success-400 dark:ring-success-400/30',
                        ])>
                            {{ $searchResults['count'] }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            @if ($searchResults['count'] > 0)
                                {{ trans_choice('pages.experiments_sessions_details.search_results.occurrences_found', $searchResults['count'], ['term' => $searchTerm]) }}
                                <span class="text-xs">
                                    @if (isset($searchResults['locations']['comments']))
                                        <span
                                            class="inline-flex items-center px-2 py-1 mr-1 rounded-full text-xs bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            {{ trans_choice('pages.experiments_sessions_details.search_results.locations.comments', $searchResults['locations']['comments'], ['count' => $searchResults['locations']['comments']]) }}
                                        </span>
                                    @endif
                                    @if (isset($searchResults['locations']['feedback']))
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                            {{ __('pages.experiments_sessions_details.search_results.locations.feedback') }}
                                        </span>
                                    @endif
                                </span>
                            @else
                                {{ __('pages.experiments_sessions_details.search_results.no_occurrences', ['term' => $searchTerm]) }}
                            @endif
                        </div>
                    </div>
                    <button wire:click="$set('searchTerm', '')"
                        class="fi-icon-btn relative flex items-center justify-center rounded-lg outline-none transition duration-75 hover:bg-gray-50 focus:ring-2 disabled:pointer-events-none disabled:opacity-70 -m-1.5 h-8 w-8 text-gray-400 hover:text-gray-500 focus:ring-primary-600 dark:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-gray-400 dark:focus:ring-primary-500">
                        <x-heroicon-s-x-circle class="h-5 w-5" />
                    </button>
                </div>
            </div>
        </div>
    @endif
    {{ $this->sessionInfolist }}

    @php
        $getFileName = function ($path) {
            return basename($path);
        };

        $isAudioFile = function ($url) {
            $extensions = ['.wav', '.mp3', '.ogg', '.m4a', '.aac'];
            $url = strtolower($url);
            return collect($extensions)->contains(function ($ext) use ($url) {
                return str_ends_with($url, $ext);
            });
        };
    @endphp

    <!-- Notes de l'examinateur -->
    @if ($session->notes)
        <div class="-mt-2">
            <div class="bg-white rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-800 p-6">
                <h2 class="text-lg font-medium mb-4">{{ __('pages.experiments_sessions_details.examiner_notes.title') }}
                </h2>
                <div class="prose dark:prose-invert max-w-none">
                    {{ $session->notes }}
                </div>
            </div>
        </div>
    @endif

    <!-- Groupes -->
    @if (!empty($groups))
        <div class="-mt-2">
            <div x-data="{ expanded: true }"
                class="bg-white rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-800">
                <button @click="expanded = !expanded" class="w-full p-6 flex justify-between items-center">
                    <h2 class="text-lg font-medium">{{ __('pages.experiments_sessions_details.groups.title') }}</h2>
                    <svg x-bind:class="expanded ? 'rotate-180' : ''" class="w-5 h-5 transform transition-transform"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <div x-show="expanded" class="p-6 pt-0 space-y-8">
                    @foreach ($groups as $group)
                        <div class="border rounded-lg overflow-hidden dark:border-gray-700">
                            <!-- En-tête du groupe -->
                            <div class="bg-gray-50 dark:bg-gray-700 p-4 border-b border-gray-200 dark:border-gray-600">
                                <div class="flex items-center space-x-2">
                                    <h4 class="text-lg font-medium">{{ $group->name }}</h4>
                                    <span class="inline-block w-4 h-4 rounded-full"
                                        style="background-color: {{ $group->color }}"></span>
                                </div>
                            </div>

                            <!-- Commentaire du groupe -->
                            @if ($group->comment)
                                <div
                                    class="bg-blue-50 dark:bg-blue-900/30 p-4 border-b border-gray-200 dark:border-gray-600">
                                    <p class="text-sm text-blue-600 dark:text-blue-300">
                                        <span
                                            class="font-medium">{{ __('pages.experiments_sessions_details.groups.comment_label') }}</span>
                                        @if (!empty($searchTerm))
                                            {!! preg_replace(
                                                '/(' . preg_quote($searchTerm, '/') . ')/i',
                                                '<span class="bg-yellow-200 dark:bg-yellow-800 rounded-sm px-1">$1</span>',
                                                e($group->comment),
                                            ) !!}
                                        @else
                                            {{ $group->comment }}
                                        @endif
                                    </p>
                                </div>
                            @endif

                            <!-- Grille des médias -->
                            <div class="p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    @foreach ($group->elements as $element)
                                        <div class="flex flex-col space-y-3">
                                            <div
                                                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-3 border border-gray-200 dark:border-gray-700">
                                                @if ($isAudioFile($element->url))
                                                    <audio controls class="w-full">
                                                        <source src="{{ $element->url }}">
                                                        {{ __('pages.experiments_sessions_details.groups.media.audio_unsupported') }}
                                                    </audio>
                                                @else
                                                    <img src="{{ $element->url }}" alt="Media {{ $element->id }}"
                                                        class="w-full h-auto rounded-lg" />
                                                @endif
                                                <div class="space-y-1 mt-5">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                        {{ __('pages.experiments_sessions_details.groups.media.name') }}
                                                        {{ $getFileName($element->url) }}
                                                    </div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ __('pages.experiments_sessions_details.groups.media.position', ['x' => number_format($element->x, 2), 'y' => number_format($element->y, 2)]) }}
                                                    </div>
                                                    @if (isset($element->interactions) && $element->interactions > 0)
                                                        <div
                                                            class="text-sm font-medium text-blue-600 dark:text-blue-400">
                                                            {{ trans_choice('pages.experiments_sessions_details.groups.media.interactions', $element->interactions, ['count' => $element->interactions]) }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Journal des actions -->
    @if ($actionsLog->isNotEmpty())
        <div class="-mt-2">
            <div x-data="{ expanded: false }"
                class="bg-white rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-800">
                <button @click="expanded = !expanded" class="w-full p-6 flex justify-between items-center">
                    <h2 class="text-lg font-medium">{{ __('pages.experiments_sessions_details.actions_log.title') }}
                    </h2>
                    <svg x-bind:class="expanded ? 'rotate-180' : ''" class="w-5 h-5 transform transition-transform"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <div x-show="expanded" class="p-6 pt-0">
                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700">
                                    <th
                                        class="px-4 py-2 text-start text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ __('pages.experiments_sessions_details.actions_log.headers.time') }}
                                    </th>
                                    <th
                                        class="px-4 py-2 text-start text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ __('pages.experiments_sessions_details.actions_log.headers.action') }}
                                    </th>
                                    <th
                                        class="px-4 py-2 text-start text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ __('pages.experiments_sessions_details.actions_log.headers.details') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($actionsLog as $action)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td
                                            class="px-4 py-2 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                            {{ $action['time'] }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm">
                                            <span
                                                class="px-2 py-1 rounded-full text-xs
                                                @if ($action['type'] === 'move') bg-blue-100 text-blue-800
                                                @elseif($action['type'] === 'sound') bg-green-100 text-green-800
                                                @else bg-purple-100 text-purple-800 @endif">
                                                @if ($action['type'] === 'move')
                                                    {{ __('pages.experiments_sessions_details.actions_log.actions.move') }}
                                                @elseif($action['type'] === 'sound')
                                                    {{ __('pages.experiments_sessions_details.actions_log.actions.sound') }}
                                                @else
                                                    {{ __('pages.experiments_sessions_details.actions_log.actions.image') }}
                                                @endif
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">
                                            @if ($action['type'] === 'move')
                                                {{ __('pages.experiments_sessions_details.actions_log.details.name') }}
                                                {{ $getFileName($action['id']) }}<br>
                                                {{ __('pages.experiments_sessions_details.actions_log.details.position', ['x' => number_format($action['x'], 2), 'y' => number_format($action['y'], 2)]) }}
                                            @else
                                                {{ __('pages.experiments_sessions_details.actions_log.details.name') }}
                                                {{ $getFileName($action['id']) }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

</x-filament-panels::page>
