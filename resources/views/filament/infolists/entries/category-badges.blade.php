<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <div class="flex flex-col gap-2">
        @forelse ($getRecord()->categories as $category)
            <a href="{{ \App\Filament\Resources\Categories\CategoryResource::getUrl('view', ['record' => $category]) }}">
                <x-filament::badge>
                    {{ $category->full_path }}
                </x-filament::badge>
            </a>
        @empty
            <span class="text-sm text-gray-500 dark:text-gray-400">—</span>
        @endforelse
    </div>
</x-dynamic-component>
