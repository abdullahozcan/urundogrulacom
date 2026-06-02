<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Son 10 Okutulma
        </x-slot>

        @php
            $scans = $this->getScans();
        @endphp

        @if ($scans->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Henüz okutulma kaydı bulunmuyor.
            </p>
        @else
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($scans as $scan)
                    <li class="flex items-center gap-x-4 px-2 py-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                {{ $scan->scanned_at?->format('d.m.Y H:i') ?? $scan->created_at->format('d.m.Y H:i') }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                @if ($scan->lat && $scan->lng)
                                    <a href="https://www.openstreetmap.org/?mlat={{ $scan->lat }}&mlon={{ $scan->lng }}&zoom=15" target="_blank" rel="noopener noreferrer" class="hover:text-primary-600 dark:hover:text-primary-400">
                                        📍 {{ number_format($scan->lat, 4) }}, {{ number_format($scan->lng, 4) }}
                                    </a>
                                @else
                                    📍 -
                                @endif
                            </p>
                        </div>
                        <div class="hidden sm:block flex-1 min-w-0">
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate font-mono">
                                {{ $scan->ip_address ?? '-' }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate" title="{{ $scan->browser }}">
                                {{ $scan->browser ?? '-' }}
                            </p>
                        </div>
                        <div class="flex-shrink-0">
                            @if ($scan->device_type === 'Mobile')
                                <span class="inline-flex items-center rounded-md bg-purple-50 dark:bg-purple-900/20 px-2 py-1 text-xs font-medium text-purple-700 dark:text-purple-300 ring-1 ring-inset ring-purple-600/20">
                                    Mobil
                                </span>
                            @elseif ($scan->device_type === 'Tablet')
                                <span class="inline-flex items-center rounded-md bg-blue-50 dark:bg-blue-900/20 px-2 py-1 text-xs font-medium text-blue-700 dark:text-blue-300 ring-1 ring-inset ring-blue-600/20">
                                    Tablet
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-md bg-gray-50 dark:bg-gray-800 px-2 py-1 text-xs font-medium text-gray-600 dark:text-gray-300 ring-1 ring-inset ring-gray-500/20">
                                    Desktop
                                </span>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
