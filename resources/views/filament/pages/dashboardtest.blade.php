<x-filament-panels::page>
    <div class="space-y-4">
        <h2 class="text-2xl font-bold">Tableau de bord des expérimentations</h2>

        <table class="table-auto w-full bg-white shadow-md rounded">
            <thead>
                <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                    <th class="py-3 px-6 text-left">Nom</th>
                    <th class="py-3 px-6 text-left">État</th>
                    <th class="py-3 px-6 text-center">Nombre de participants</th>
                    <th class="py-3 px-6 text-center">Lien</th>
                    <th class="py-3 px-6 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-sm font-light">
                @foreach ($experiments as $experiment)
                    <tr class="border-b border-gray-200 hover:bg-gray-100">
                        <td class="py-3 px-6 text-left">{{ $experiment->name }}</td>
                        <td class="py-3 px-6 text-left">{{ $experiment->status }}</td>
                        <td class="py-3 px-6 text-center">{{ $experiment->sessions->count() }}</td>
                        <td class="py-3 px-6 text-center">
                            @if ($experiment->link)
                                <a href="{{ $experiment->link }}" class="text-blue-500" target="_blank">Lien
                                    d'expérimentation</a>
                            @else
                                Aucun lien
                            @endif
                        </td>
                        <td class="py-3 px-6 text-center">
                            <!-- Ajouter des actions comme Export ou voir les détails -->
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
