{{-- resources/views/filament/components/terms-checkbox.blade.php --}}
@php
    use League\CommonMark\Environment\Environment;
    use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
    use League\CommonMark\MarkdownConverter;

    $environment = new Environment();
    $environment->addExtension(new CommonMarkCoreExtension());
    $converter = new MarkdownConverter($environment);

    $markdown = File::get(resource_path('/terms/' . app()->getLocale() . '.md'));
    $html = $converter->convert($markdown);
@endphp

<div class="flex items-center gap-2 pt-3">
    <span class="text-sm font-medium">{{ __('pages.auth.register.terms_accept') }}</span>
    <x-filament::modal width="xl">
        <x-slot name="trigger">
            <button type="button" class="text-primary-600 hover:text-primary-500 text-sm font-bold mt-0.5">
                {{ __('pages.auth.register.terms_read') }}
            </button>
        </x-slot>

        <x-slot name="heading">
            {{ __('pages.auth.register.terms.title') }}
        </x-slot>

        <div class="prose prose-sm dark:prose-invert max-w-none overflow-y-auto" style="max-height: 24rem;">
            <div class="w-full rounded-lg bg-gray-50 dark:bg-gray-800 p-4">
                {!! $html !!}
            </div>
        </div>

        <x-slot name="footer">
            <x-filament::button color="gray" x-on:click="isOpen = false">
                {{ __('Close') }}
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
    <span class="text-sm font-medium">{{ __('pages.auth.register.terms_end') }}</span>
</div>
