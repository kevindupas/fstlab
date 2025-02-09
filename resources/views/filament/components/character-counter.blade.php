<div class="flex items-center space-x-2">
    <div class="text-sm transition-colors duration-300">
        <span class="{{ $progressColor }} transition-colors duration-300">{{ $current }}/50</span>
        @if ($remaining > 0)
            <span class="text-gray-500">
                ({{ __('pages.auth.register.registration_reason.remaining', ['count' => $remaining]) }})
            </span>
        @else
            <span class="text-success-500 transition-opacity duration-300">
                <svg class="inline-block w-4 h-4 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                {{ __('pages.auth.register.registration_reason.complete') }}
            </span>
        @endif
    </div>
</div>
