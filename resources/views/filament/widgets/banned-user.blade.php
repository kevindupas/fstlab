<x-filament-widgets::widget>
    <div class="p-4 space-y-4 bg-danger-50 border border-danger-200 rounded-xl">
        @if(auth()->user()->status === 'banned')
        <div class="flex items-center gap-4">
            <div class="p-3 bg-danger-100 rounded-lg">
                <x-heroicon-o-x-circle class="w-6 h-6 text-danger-500" />
            </div>
            <div>
                <h2 class="text-lg font-bold text-danger-900">Compte banni</h2>
                <p class="text-danger-600">
                    Votre compte a été banni. Si vous pensez qu'il s'agit d'une erreur ou souhaitez faire une demande de débannissement,
                    vous pouvez contacter l'administrateur via la page "Contacter l'administrateur".
                </p>
            </div>
        </div>
        @else
        <div class="flex items-center gap-4">
            <div class="p-3 bg-danger-100 rounded-lg">
                <x-heroicon-o-x-circle class="w-6 h-6 text-danger-500" />
            </div>
            <div>
                <h2 class="text-lg font-bold text-danger-900">Accès restreint</h2>
                <p class="text-danger-600">
                    L'expérimentateur principal de votre compte a été banni. L'accès à vos fonctionnalités est temporairement restreint.
                    Veuillez contacter l'administrateur via la page "Contacter l'administrateur" pour plus d'informations.
                </p>
            </div>
        </div>
        @endif
    </div>
</x-filament-widgets::widget>