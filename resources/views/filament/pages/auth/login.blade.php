<x-filament-panels::page.simple>
    @if (session('status') === 'verification-link-sent')
        <div class="p-4 mb-4 text-green-800 bg-green-100 rounded">
            {{ __('Un lien de vérification a été envoyé à votre adresse email. Veuillez vérifier votre boîte de réception.') }}
        </div>
    @endif

    @if (session('warning'))
        <div class="p-4 mb-4 text-yellow-800 bg-yellow-100 rounded">
            {{ session('warning') }}
        </div>
    @endif

    <x-filament-panels::form wire:submit="authenticate">
        {{ $this->form }}

        <x-filament-panels::form.actions :actions="$this->getCachedFormActions()" :full-width="$this->hasFullWidthFormActions()" />
    </x-filament-panels::form>
</x-filament-panels::page.simple>
