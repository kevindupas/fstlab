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

                                                    <!-- Résumé détaillé des interactions -->
                                                    <div class="mt-3 space-y-2">
                                                        <!-- Lectures/Vues -->
                                                        @if ($element->detailed_interactions['plays'] > 0)
                                                            <div class="flex items-center gap-2">
                                                                <span class="flex items-center gap-1">
                                                                    @if ($isAudioFile($element->url))
                                                                        <svg class="w-4 h-4 text-green-500"
                                                                            fill="none" stroke="currentColor"
                                                                            viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round" stroke-width="2"
                                                                                d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z">
                                                                            </path>
                                                                        </svg>
                                                                    @else
                                                                        <svg class="w-4 h-4 text-blue-500"
                                                                            fill="none" stroke="currentColor"
                                                                            viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round" stroke-width="2"
                                                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z">
                                                                            </path>
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round" stroke-width="2"
                                                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                                            </path>
                                                                        </svg>
                                                                    @endif
                                                                </span>
                                                                <span class="text-sm font-medium">
                                                                    {{ trans_choice('pages.experiments_sessions_details.groups.media.play_count', $element->detailed_interactions['plays'], ['count' => $element->detailed_interactions['plays']]) }}
                                                                </span>
                                                            </div>
                                                        @endif

                                                        <!-- Déplacements -->
                                                        @if ($element->detailed_interactions['moves'] > 0)
                                                            <div class="flex items-center gap-2">
                                                                <svg class="w-4 h-4 text-orange-500" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4">
                                                                    </path>
                                                                </svg>
                                                                <span class="text-sm font-medium">
                                                                    {{ trans_choice('pages.experiments_sessions_details.groups.media.move_count', $element->detailed_interactions['moves'], ['count' => $element->detailed_interactions['moves']]) }}
                                                                </span>
                                                            </div>
                                                        @endif

                                                        <!-- Changements de groupe -->
                                                        @if (!empty($element->detailed_interactions['group_changes']))
                                                            <div class="mt-2 text-sm flex items-center">
                                                                <div
                                                                    class="font-medium text-purple-600 dark:text-purple-400">
                                                                    {{ trans_choice('pages.experiments_sessions_details.groups.media.group_changes', count($element->detailed_interactions['group_changes']), ['count' => count($element->detailed_interactions['group_changes'])]) }}:
                                                                </div>
                                                                <div class="pl-2 space-y-1">
                                                                    @foreach ($element->detailed_interactions['group_changes'] as $change)
                                                                        <div
                                                                            class="flex items-center gap-1 text-gray-600 dark:text-gray-400">
                                                                            <span>{{ $change['from'] }}</span>
                                                                            <svg class="w-3 h-3" fill="none"
                                                                                stroke="currentColor"
                                                                                viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round"
                                                                                    stroke-linejoin="round"
                                                                                    stroke-width="2" d="M9 5l7 7-7 7">
                                                                                </path>
                                                                            </svg>
                                                                            <span>{{ $change['to'] }}</span>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                        </path>
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
            @switch($action['type'])
                @case('move')
                    bg-blue-100 text-blue-800
                    @break
                @case('sound')
                    bg-green-100 text-green-800
                    @break
                @case('group_created')
                    bg-purple-100 text-purple-800
                    @break
                @case('item_moved_between_groups')
                    bg-yellow-100 text-yellow-800
                    @break
                @default
                    bg-gray-100 text-gray-800
            @endswitch
        ">
                                                @switch($action['type'])
                                                    @case('move')
                                                        {{ __('pages.experiments_sessions_details.actions_log.actions.move') }}
                                                    @break

                                                    @case('sound')
                                                        {{ __('pages.experiments_sessions_details.actions_log.actions.sound') }}
                                                    @break

                                                    @case('group_created')
                                                        {{ __('pages.experiments_sessions_details.actions_log.actions.simple_group_created') }}
                                                    @break

                                                    @case('item_moved_between_groups')
                                                        {{ __('pages.experiments_sessions_details.actions_log.actions.simple_group_change') }}
                                                    @break

                                                    @default
                                                        {{ $action['type'] }}
                                                @endswitch
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">
                                            @switch($action['type'])
                                                @case('move')
                                                    {{ $getFileName($action['id']) }}<br>
                                                    {{ __('pages.experiments_sessions_details.actions_log.details.position', ['x' => number_format($action['x'], 2), 'y' => number_format($action['y'], 2)]) }}
                                                @break

                                                @case('sound')
                                                    {{ $getFileName($action['id']) }}
                                                @break

                                                @case('group_created')
                                                    {{ __('pages.experiments_sessions_details.actions_log.details.group_created_details', [
                                                        'name' => $action['group_name'],
                                                        'color' => $action['group_color'],
                                                    ]) }}
                                                @break

                                                @case('item_moved_between_groups')
                                                    {{ __('pages.experiments_sessions_details.actions_log.details.item_moved_details', [
                                                        'name' => $getFileName($action['item_id']),
                                                        'from' => $action['from_group'],
                                                        'to' => $action['to_group'],
                                                    ]) }}
                                                @break
                                            @endswitch
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
