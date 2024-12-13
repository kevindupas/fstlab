@php
    $mediaFiles = is_string($media) ? explode(',', $media) : $media;
    $mediaFiles = collect($mediaFiles)->map(fn($path) => trim($path));

    $images = $mediaFiles->filter(fn($path) => str_contains($path, '.jpg') || str_contains($path, '.png'));
    $audio = $mediaFiles->filter(fn($path) => str_contains($path, '.wav') || str_contains($path, '.mp3'));
@endphp

@if($images->isNotEmpty())
    <div class="mb-6">
        <h3 class="text-lg font-medium mb-3 flex items-center">
            <x-heroicon-o-photo class="w-5 h-5 mr-2"/>
            Images
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($images as $path)
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg shadow-sm overflow-hidden">
                    <img src="/storage/{{ $path }}" alt="Media" class="w-full h-48 object-cover" />
                    <div class="p-3">
                        <div class="text-xs text-gray-500">{{ basename($path) }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

@if($audio->isNotEmpty())
    <div class="mb-6">
        <h3 class="text-lg font-medium mb-3 flex items-center">
            <x-heroicon-o-musical-note class="w-5 h-5 mr-2"/>
            Sons
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($audio as $path)
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg shadow-sm p-4">
                    <div class="flex items-center space-x-3 mb-2">
                        <x-heroicon-o-speaker-wave class="w-8 h-8 text-primary-500"/>
                        <div class="flex-1 flex-col justify-center items-center space-y-4">
                            <audio controls class="w-full">
                                <source src="/storage/{{ $path }}" type="audio/mpeg">
                            </audio>
                            <div class="text-sm font-medium mb-1">{{ basename($path) }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

@if(!empty($documents))
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($documents as $path)
            @php
                $isPDF = str_contains($path, '.pdf');
            @endphp
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg shadow-sm p-4">
                <div class="flex items-center space-x-3">
                    @if($isPDF)
                        <x-heroicon-o-document class="w-8 h-8 text-red-500"/>
                    @else
                        <x-heroicon-o-photo class="w-8 h-8 text-blue-500"/>
                    @endif
                    <div class="flex-1">
                        <a href="/storage/{{ $path }}" target="_blank"
                           class="text-primary-600 hover:text-primary-500 font-medium">
                            {{ basename($path) }}
                        </a>
                        <div class="text-xs text-gray-500 mt-1">
                            {{ strtoupper(pathinfo($path, PATHINFO_EXTENSION)) }}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
