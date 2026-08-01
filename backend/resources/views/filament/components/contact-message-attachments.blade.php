<div class="space-y-2">
    @if (is_array($record->attachments) && count($record->attachments) > 0)
        @foreach ($record->attachments as $path)
            <div class="flex items-center gap-3">
                <x-filament::icon
                    icon="heroicon-m-paper-clip"
                    class="w-5 h-5 text-gray-500"
                />
                <a
                    href="{{ Storage::url($path) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-primary-600 hover:text-primary-500 hover:underline text-sm"
                >
                    {{ basename($path) }}
                </a>
            </div>
        @endforeach
    @else
        <p class="text-sm text-gray-500">No attachments.</p>
    @endif
</div>
