<x-filament-widgets::widget>
    <div class="p-4 space-y-4 bg-danger-50 border border-danger-200 rounded-xl">
        @if(auth()->user()->status === 'banned')
        <div class="flex items-center gap-4">
            <div class="p-3 bg-danger-100 rounded-lg">
                <x-heroicon-o-x-circle class="w-6 h-6 text-danger-500" />
            </div>
            <div>
                <h2 class="text-lg font-bold text-danger-900">{{__('filament.widgets.banned.principal.title')}}</h2>
                <p class="text-danger-600">
                    {{__('filament.widgets.banned.principal.description')}}
                </p>
            </div>
        </div>
        @else
        <div class="flex items-center gap-4">
            <div class="p-3 bg-danger-100 rounded-lg">
                <x-heroicon-o-x-circle class="w-6 h-6 text-danger-500" />
            </div>
            <div>
                <h2 class="text-lg font-bold text-danger-900">{{__('filament.widgets.banned.secondary.title')}}</h2>
                <p class="text-danger-600">
                    {{__('filament.widgets.banned.secondary.description')}}
                </p>
            </div>
        </div>
        @endif
    </div>
</x-filament-widgets::widget>
