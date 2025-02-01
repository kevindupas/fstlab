<x-filament-panels::page>
    <form wire:submit="export">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            {{-- Format Matrice --}}
            <button type="button" wire:click="$set('export_format', 'matrix')"
                class="p-4 border-2 rounded-lg flex flex-col items-center space-y-4 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors {{ $export_format === 'matrix' ? 'border-primary-500 bg-primary-50 dark:bg-primary-950' : 'border-gray-200 dark:border-gray-700' }}">

                <x-filament::icon icon="heroicon-o-table-cells" class="h-12 w-12 text-black dark:text-white" />

                <div class="w-full space-y-2">
                    <h3 class="font-medium text-lg text-center">Format Matrice</h3>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <div
                            class="font-mono bg-gray-100 dark:bg-gray-800 p-2 rounded border dark:border-gray-700 overflow-x-auto">
                            <table class="w-full text-xs">
                                <tr>
                                    <td class="text-left pr-4"></td>
                                    <td class="text-left pr-4">P01</td>
                                    <td class="text-left pr-4">P02</td>
                                    <td class="text-left pr-4">P03</td>
                                    <td class="text-left">P04</td>
                                </tr>
                                <tr>
                                    <td class="text-left pr-4">Nom_son_1</td>
                                    <td class="text-left pr-4">1</td>
                                    <td class="text-left pr-4">2</td>
                                    <td class="text-left pr-4">1</td>
                                    <td class="text-left">1</td>
                                </tr>
                                <tr>
                                    <td class="text-left pr-4">Nom_son_2</td>
                                    <td class="text-left pr-4">2</td>
                                    <td class="text-left pr-4">1</td>
                                    <td class="text-left pr-4">2</td>
                                    <td class="text-left">2</td>
                                </tr>
                                <tr>
                                    <td class="text-left pr-4">Nom_son_3</td>
                                    <td class="text-left pr-4">3</td>
                                    <td class="text-left pr-4">3</td>
                                    <td class="text-left pr-4">3</td>
                                    <td class="text-left">1</td>
                                </tr>
                                <tr>
                                    <td class="text-left pr-4">Nom_son_4</td>
                                    <td class="text-left pr-4">1</td>
                                    <td class="text-left pr-4">2</td>
                                    <td class="text-left pr-4">1</td>
                                    <td class="text-left">2</td>
                                </tr>
                            </table>
                        </div>
                        <p class="mt-2 text-center">Pour analyse statistique (R, Python, SPSS...)</p>
                    </div>
                </div>
            </button>

            {{-- Format Fichiers Individuels --}}
            <button type="button" wire:click="$set('export_format', 'individual')"
                class="p-4 border-2 rounded-lg flex flex-col items-center space-y-4 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors {{ $export_format === 'individual' ? 'border-primary-500 bg-primary-50 dark:bg-primary-950' : 'border-gray-200 dark:border-gray-700' }}">

                <x-filament::icon icon="heroicon-s-document-duplicate" class="h-12 w-12 text-black dark:text-white" />


                <div class="w-full space-y-2">
                    <h3 class="font-medium text-lg text-center">Format Fichiers Individuels</h3>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <div class="grid gap-2">
                            <div>
                                <div class="text-xs font-medium mb-1 text-left">P01.csv</div>
                                <div
                                    class="font-mono bg-gray-100 dark:bg-gray-800 p-2 rounded border dark:border-gray-700 text-left">
                                    <table class="w-full text-xs">
                                        <tr>
                                            <td>P01</td>
                                        </tr>
                                        <tr>
                                            <td>Nom_son_1,Nom_son_4</td>
                                        </tr>
                                        <tr>
                                            <td>Nom_son_3</td>
                                        </tr>
                                        <tr>
                                            <td>Nom_son_2</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div>
                                <div class="text-xs font-medium mb-1 text-left">P01-comment.csv</div>
                                <div
                                    class="font-mono bg-gray-100 dark:bg-gray-800 p-2 rounded border dark:border-gray-700 text-left">
                                    <table class="w-full text-xs">
                                        <tr>
                                            <td>P01</td>
                                        </tr>
                                        <tr>
                                            <td>Nom_son_1,Nom_son_4</td>
                                        </tr>
                                        <tr>
                                            <td>Commentaire sur ce groupe (sans retour à la ligne)</td>
                                        </tr>
                                        <tr>
                                            <td>Nom_son_3</td>
                                        </tr>
                                        <tr>
                                            <td>Un autre commentaire (sans retour à la ligne)</td>
                                        </tr>
                                        <tr>
                                            <td>Nom_son_2</td>
                                        </tr>
                                        <tr>
                                            <td>Le dernier commentaire (sans retour à la ligne)</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <p class="mt-2 text-center">Pour analyse qualitative et vérification</p>
                    </div>
                </div>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-1 gap-4 mb-6">
            <button type="button" wire:click="$set('export_format', 'both')"
                class="p-4 border-2 rounded-lg flex flex-col items-center space-y-4 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors {{ $export_format === 'both' ? 'border-primary-500 bg-primary-50 dark:bg-primary-950' : 'border-gray-200 dark:border-gray-700' }}">
                <div class="flex items-center justify-center">

                    <x-filament::icon icon="heroicon-o-table-cells" class="h-12 w-12 text-black dark:text-white" />
                    +
                    <x-filament::icon icon="heroicon-s-document-duplicate"
                        class="h-12 w-12 text-black dark:text-white" />


                </div>

                <div class="w-full space-y-2">
                    <h3 class="font-medium text-lg text-center">Les Deux Formats</h3>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <p class="text-center">Exporter à la fois la matrice et les fichiers individuels dans une
                            archive zip</p>
                    </div>
                </div>
            </button>
        </div>

        <div class="p-4 border rounded-lg bg-gray-50 dark:bg-gray-900 dark:border-gray-700 mb-6">
            <div class="text-sm font-medium mb-2">Options d'export avancées</div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <div class="text-sm font-medium mb-2">Délimiteur</div>
                    <div class="flex flex-wrap gap-4">
                        <label class="inline-flex items-center">
                            <input type="radio" wire:model.live="csv_delimiter" value="tab"
                                class="text-primary-600 border-gray-300 dark:border-gray-700 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:focus:border-primary-600">
                            <span class="ml-2 text-sm">Tabulation</span>
                        </label>

                        <label class="inline-flex items-center">
                            <input type="radio" wire:model.live="csv_delimiter" value="comma"
                                class="text-primary-600 border-gray-300 dark:border-gray-700 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:focus:border-primary-600">
                            <span class="ml-2 text-sm">Virgule (,)</span>
                        </label>

                        <label class="inline-flex items-center">
                            <input type="radio" wire:model.live="csv_delimiter" value="semicolon"
                                class="text-primary-600 border-gray-300 dark:border-gray-700 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:focus:border-primary-600">
                            <span class="ml-2 text-sm">Point-virgule (;)</span>
                        </label>
                    </div>
                </div>

                <div class="text-xs text-gray-600 dark:text-gray-400 flex items-end">
                    <p class="pb-2">
                        @if ($csv_delimiter === 'tab')
                            Pour R/Python : <code
                                class="bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded">read.delim('fichier.csv')</code>
                        @elseif($csv_delimiter === 'comma')
                            Pour R/Python : <code
                                class="bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded">read.csv('fichier.csv')</code>
                        @else
                            Pour R/Python : <code
                                class="bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded">read.csv2('fichier.csv')</code>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- {{ $this->form }} --}}

        <div class="flex justify-end mt-6 gap-3">
            <x-filament::button type="submit" color="success">
                {{ $export_format === 'matrix' ? 'Exporter matrice CSV' : 'Exporter fichiers CSV (.zip)' }}
            </x-filament::button>

            <x-filament::button tag="a"
                href="{{ route('filament.admin.resources.experiment-sessions.index', ['record' => $this->experiment_id]) }}"
                color="gray">
                Retour à la liste
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
