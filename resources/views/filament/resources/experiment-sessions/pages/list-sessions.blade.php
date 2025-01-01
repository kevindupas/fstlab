<x-filament-panels::page>
    {{-- Search Form --}}
    <form method="GET" action="{{ url()->current() }}" class="mb-4">
        <input type="hidden" name="record" value="{{ request()->query('record') }}">
        @if (request()->query('tab'))
            <input type="hidden" name="tab" value="{{ request()->query('tab') }}">
        @endif
        <div class="flex gap-2">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request()->query('search') }}"
                    placeholder="Rechercher un mot (ex: jaune, animal, etc.)"
                    class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
            <button type="submit"
                class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:hover:bg-primary-800">
                Rechercher
            </button>
            @if (request()->query('search'))
                <a href="{{ url()->current() }}?record={{ request()->query('record') }}{{ request()->query('tab') ? '&tab=' . request()->query('tab') : '' }}"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700">
                    Réinitialiser
                </a>
            @endif
        </div>
    </form>

    @if (request()->query('search'))
        @php
            $searchTerm = request()->query('search');
            $experimentId = request()->query('record');

            // Base query pour compter les résultats
            $query = \App\Models\ExperimentSession::where('experiment_id', $experimentId)->where(function ($q) use (
                $searchTerm,
            ) {
                $q->where('group_data', 'like', "%{$searchTerm}%")->orWhere('feedback', 'like', "%{$searchTerm}%");
            });

            // Appliquer les filtres par onglet pour le comptage
            if (request()->query('tab') === 'creator') {
                $query->where('is_creator_session', true);
            } elseif (request()->query('tab') === 'mine') {
                $query->whereHas('experimentLink', fn($q) => $q->where('user_id', auth()->id()));
            } elseif (request()->query('tab') === 'collaborators') {
                $query->whereHas(
                    'experimentLink',
                    fn($q) => $q->whereNotIn('user_id', [auth()->id(), $experiment->created_by]),
                );
            }

            $count = $query->count();

            // Analyse des occurrences
            $locations = [];
            foreach ($query->get() as $session) {
                if (str_contains(strtolower($session->feedback), strtolower($searchTerm))) {
                    $locations['feedback'] = ($locations['feedback'] ?? 0) + 1;
                }

                $groupData = json_decode($session->group_data, true);
                foreach ($groupData as $group) {
                    if (
                        isset($group['comment']) &&
                        str_contains(strtolower($group['comment']), strtolower($searchTerm))
                    ) {
                        $locations['comments'] = ($locations['comments'] ?? 0) + 1;
                    }
                }
            }
        @endphp

        <div class="mb-4">
            <div
                class="fi-ta-content rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-x-3 px-4 py-3">
                    <div class="flex flex-1 items-center gap-x-3">
                        <div
                            class="fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-1.5 min-w-[theme(spacing.5)] py-0.5 bg-success-50 text-success-600 ring-success-600/10 dark:bg-success-400/10 dark:text-success-400 dark:ring-success-400/30">
                            {{ $count }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            @if ($count > 0)
                                Occurrence{{ $count > 1 ? 's' : '' }} de "{{ $searchTerm }}"
                                trouvée{{ $count > 1 ? 's' : '' }}
                                <span class="text-xs">
                                    @if (isset($locations['comments']))
                                        <span
                                            class="inline-flex items-center px-2 py-1 mr-1 rounded-full text-xs bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            {{ $locations['comments'] }} commentaire(s)
                                        </span>
                                    @endif
                                    @if (isset($locations['feedback']))
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                            {{ $locations['feedback'] }} dans le feedback
                                        </span>
                                    @endif
                                </span>
                            @else
                                Aucune occurrence de "{{ $searchTerm }}" trouvée
                            @endif
                        </div>
                    </div>
                    <button
                        onclick="window.location.href='{{ url()->current() }}?record={{ $experimentId }}{{ request()->query('tab') ? '&tab=' . request()->query('tab') : '' }}'"
                        class="fi-icon-btn relative flex items-center justify-center rounded-lg outline-none transition duration-75 hover:bg-gray-50 focus:ring-2 disabled:pointer-events-none disabled:opacity-70 -m-1.5 h-8 w-8 text-gray-400 hover:text-gray-500 focus:ring-primary-600 dark:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-gray-400 dark:focus:ring-primary-500">
                        <x-heroicon-s-x-circle class="h-5 w-5" />
                    </button>
                </div>
            </div>
        </div>
    @endif

    @php
        $experimentId = request()->query('record');
        if (!$experimentId) {
            return;
        }

        $experiment = \App\Models\Experiment::find($experimentId);
        $isCreator = $experiment?->created_by === auth()->id();
        $isSecondaryAccount = auth()->user()->created_by === $experiment->created_by;
        $currentTab = request()->query('tab');

        // Préparation des compteurs avec filtre de recherche si présent
        $searchTerm = request()->query('search');

        $baseQuery = \App\Models\ExperimentSession::where('experiment_id', $experimentId);
        if ($searchTerm) {
            $baseQuery->where(function ($q) use ($searchTerm) {
                $q->where('group_data', 'like', "%{$searchTerm}%")->orWhere('feedback', 'like', "%{$searchTerm}%");
            });
        }

        $allQuery = clone $baseQuery;

        $creatorQuery = clone $baseQuery;
        $creatorQuery->whereHas('experimentLink', function ($q) use ($experiment) {
            $q->where(function ($subQ) {
                $subQ->where('is_creator', true)->orWhere('is_secondary', true);
            });
        });

        $myQuery = clone $baseQuery;
        $myQuery->whereHas('experimentLink', function ($q) {
            $q->where('user_id', auth()->id());
        });

        $collaboratorsQuery = clone $baseQuery;
        $collaboratorsQuery->whereHas('experimentLink', function ($q) {
            $q->where('is_collaborator', true)->where('user_id', '!=', Auth::id());
        });

        $counts = [
            'all' => $allQuery->count(),
            'creator' => $creatorQuery->count(),
            'mine' => $myQuery->count(),
            'collaborators' => $collaboratorsQuery->count(),
        ];
    @endphp

    <nav class="fi-tabs flex max-w-full gap-x-1 overflow-x-auto mx-auto rounded-xl bg-white p-2 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
        role="tablist">
        <a href="{{ url()->current() }}?record={{ $experimentId }}{{ request()->query('search') ? '&search=' . request()->query('search') : '' }}"
            @class([
                'fi-tabs-item group flex items-center justify-center gap-x-2 rounded-lg px-3 py-2 text-sm font-medium outline-none transition duration-75',
                'fi-active fi-tabs-item-active bg-gray-50 dark:bg-white/5' => !$currentTab,
                'hover:bg-gray-50 focus-visible:bg-gray-50 dark:hover:bg-white/5 dark:focus-visible:bg-white/5' => $currentTab,
            ]) role="tab">
            <span
                class="fi-tabs-item-label transition duration-75 @if (!$currentTab) text-primary-600 dark:text-primary-400 @else text-gray-500 group-hover:text-gray-700 @endif">
                Tous les résultats
            </span>

            <span
                class="fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-1.5 min-w-[theme(spacing.5)] py-0.5 tracking-tight bg-primary-50 text-primary-600 ring-primary-600/10 dark:bg-primary-400/10 dark:text-primary-400 dark:ring-primary-400/30">
                {{ $counts['all'] }}
            </span>
        </a>

        @if ($isCreator || $isSecondaryAccount)
            <a href="{{ url()->current() }}?record={{ $experimentId }}&tab=creator{{ request()->query('search') ? '&search=' . request()->query('search') : '' }}"
                @class([
                    'fi-tabs-item group flex items-center justify-center gap-x-2 rounded-lg px-3 py-2 text-sm font-medium outline-none transition duration-75',
                    'fi-active fi-tabs-item-active bg-gray-50 dark:bg-white/5' =>
                        $currentTab === 'creator',
                    'hover:bg-gray-50 focus-visible:bg-gray-50 dark:hover:bg-white/5 dark:focus-visible:bg-white/5' =>
                        $currentTab !== 'creator',
                ]) role="tab">
                <span
                    class="fi-tabs-item-label transition duration-75 @if ($currentTab === 'creator') text-primary-600 dark:text-primary-400 @else text-gray-500 group-hover:text-gray-700 @endif">
                    Mes résultats (Créateur)
                </span>

                <span
                    class="fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-1.5 min-w-[theme(spacing.5)] py-0.5 tracking-tight bg-success-50 text-success-600 ring-success-600/10 dark:bg-success-400/10 dark:text-success-400 dark:ring-success-400/30">
                    {{ $counts['creator'] }}
                </span>
            </a>
        @else
            @if (!$isSecondaryAccount)
                {{-- Affichage des Mes Résultats en premier pour les collaborateurs --}}
                <a href="{{ url()->current() }}?record={{ $experimentId }}&tab=mine{{ request()->query('search') ? '&search=' . request()->query('search') : '' }}"
                    @class([
                        'fi-tabs-item group flex items-center justify-center gap-x-2 rounded-lg px-3 py-2 text-sm font-medium outline-none transition duration-75',
                        'fi-active fi-tabs-item-active bg-gray-50 dark:bg-white/5' =>
                            $currentTab === 'mine',
                        'hover:bg-gray-50 focus-visible:bg-gray-50 dark:hover:bg-white/5 dark:focus-visible:bg-white/5' =>
                            $currentTab !== 'mine',
                    ]) role="tab">
                    <span
                        class="fi-tabs-item-label transition duration-75 @if ($currentTab === 'mine') text-primary-600 dark:text-primary-400 @else text-gray-500 group-hover:text-gray-700 @endif">
                        Mes résultats
                    </span>

                    <span
                        class="fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-1.5 min-w-[theme(spacing.5)] py-0.5 tracking-tight bg-info-50 text-info-600 ring-info-600/10 dark:bg-info-400/10 dark:text-info-400 dark:ring-info-400/30">
                        {{ $counts['mine'] }}
                    </span>
                </a>

                {{-- Puis l'onglet des résultats du créateur --}}
                @if ($creatorQuery->count() > 0)
                    <a href="{{ url()->current() }}?record={{ $experimentId }}&tab=creator{{ request()->query('search') ? '&search=' . request()->query('search') : '' }}"
                        @class([
                            'fi-tabs-item group flex items-center justify-center gap-x-2 rounded-lg px-3 py-2 text-sm font-medium outline-none transition duration-75',
                            'fi-active fi-tabs-item-active bg-gray-50 dark:bg-white/5' =>
                                $currentTab === 'mine',
                            'hover:bg-gray-50 focus-visible:bg-gray-50 dark:hover:bg-white/5 dark:focus-visible:bg-white/5' =>
                                $currentTab !== 'mine',
                        ]) role="tab">
                        <span
                            class="fi-tabs-item-label transition duration-75 @if ($currentTab === 'creator') text-primary-600 dark:text-primary-400 @else text-gray-500 group-hover:text-gray-700 @endif">
                            Résultats du créateur
                        </span>
                        <span
                            class="fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-1.5 min-w-[theme(spacing.5)] py-0.5 tracking-tight bg-success-50 text-success-600 ring-success-600/10 dark:bg-success-400/10 dark:text-success-400 dark:ring-success-400/30">
                            {{ $counts['creator'] }}
                        </span>
                    </a>
                @endif
            @endif
        @endif

        <a href="{{ url()->current() }}?record={{ $experimentId }}&tab=collaborators{{ request()->query('search') ? '&search=' . request()->query('search') : '' }}"
            @class([
                'fi-tabs-item group flex items-center justify-center gap-x-2 rounded-lg px-3 py-2 text-sm font-medium outline-none transition duration-75',
                'fi-active fi-tabs-item-active bg-gray-50 dark:bg-white/5' =>
                    $currentTab === 'collaborators',
                'hover:bg-gray-50 focus-visible:bg-gray-50 dark:hover:bg-white/5 dark:focus-visible:bg-white/5' =>
                    $currentTab !== 'collaborators',
            ]) role="tab">
            <span
                class="fi-tabs-item-label transition duration-75 @if ($currentTab === 'collaborators') text-primary-600 dark:text-primary-400 @else text-gray-500 group-hover:text-gray-700 @endif">
                Autres collaborateurs
            </span>

            <span
                class="fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-1.5 min-w-[theme(spacing.5)] py-0.5 tracking-tight bg-warning-50 text-warning-600 ring-warning-600/10 dark:bg-warning-400/10 dark:text-warning-400 dark:ring-warning-400/30">
                {{ $counts['collaborators'] }}
            </span>
        </a>
    </nav>

    <div class="flex justify-end mb-4">
        @php
            $experimentId = request()->query('record');
            $currentTab = request()->query('tab');
            $searchTerm = request()->query('search');

            // Construire l'URL d'export avec tous les paramètres
            $exportUrl = url(
                '/admin/export-sessions?' .
                    http_build_query([
                        'record' => $experimentId,
                        'tab' => $currentTab,
                        'search' => $searchTerm,
                    ]),
            );
        @endphp
        <a href="{{ route('filament.admin.pages.experiment-sessions-export-all', [
            'record' => $experimentId,
            'tab' => $currentTab,
        ]) }}"
            class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:outline-none focus:border-primary-700 focus:ring ring-primary-200 active:bg-primary-600 disabled:opacity-25 transition gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Exporter la liste
        </a>
    </div>

    {{ $this->table }}

</x-filament-panels::page>
