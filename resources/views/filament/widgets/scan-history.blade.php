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
            <div class="-mx-6 -mb-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="px-4 py-3 text-left font-semibold text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tarih</th>
                                <th class="px-4 py-3 text-left font-semibold text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">IP</th>
                                <th class="px-4 py-3 text-left font-semibold text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tarayıcı</th>
                                <th class="px-4 py-3 text-left font-semibold text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cihaz</th>
                                <th class="px-4 py-3 text-left font-semibold text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Konum</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($scans as $scan)
                                <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-600 dark:text-gray-300">{{ $scan->scanned_at?->format('d.m.Y H:i') ?? $scan->created_at->format('d.m.Y H:i') }}</td>
                                    <td class="px-4 py-3 font-mono text-xs text-gray-500 dark:text-gray-400">{{ $scan->ip_address ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300 max-w-[120px] truncate" title="{{ $scan->browser }}">{{ $scan->browser ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        @if ($scan->device_type === 'Mobile')
                                            <span class="inline-flex items-center gap-1 rounded-full bg-purple-100 dark:bg-purple-900/30 px-2 py-0.5 text-xs font-medium text-purple-700 dark:text-purple-300">
                                                <x-filament::icon alias="heroicon-o-device-phone-mobile" class="w-3 h-3" /> Mobil
                                            </span>
                                        @elseif ($scan->device_type === 'Tablet')
                                            <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 dark:bg-blue-900/30 px-2 py-0.5 text-xs font-medium text-blue-700 dark:text-blue-300">
                                                <x-filament::icon alias="heroicon-o-tablet" class="w-3 h-3" /> Tablet
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 dark:bg-gray-800 px-2 py-0.5 text-xs font-medium text-gray-700 dark:text-gray-300">
                                                <x-filament::icon alias="heroicon-o-computer-desktop" class="w-3 h-3" /> Masaüstü
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($scan->lat && $scan->lng)
                                            <a href="https://www.openstreetmap.org/?mlat={{ $scan->lat }}&mlon={{ $scan->lng }}&zoom=15" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-sm text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-300 underline underline-offset-2">
                                                <x-filament::icon alias="heroicon-o-map-pin" class="w-4 h-4" />
                                                {{ number_format($scan->lat, 4) }}, {{ number_format($scan->lng, 4) }}
                                            </a>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
