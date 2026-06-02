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
            <div class="-mx-6 -mb-6 overflow-x-auto">
                <table class="w-full whitespace-nowrap text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="px-4 py-3 text-left font-medium text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tarih</th>
                            <th class="px-4 py-3 text-left font-medium text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Konum</th>
                            <th class="px-4 py-3 text-left font-medium text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">IP</th>
                            <th class="px-4 py-3 text-left font-medium text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tarayıcı</th>
                            <th class="px-4 py-3 text-left font-medium text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cihaz</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($scans as $scan)
                            <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900/50">
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                    {{ $scan->scanned_at?->format('d.m.Y H:i') ?? $scan->created_at->format('d.m.Y H:i') }}
                                </td>
                                <td class="px-4 py-3">
                                    @if ($scan->lat && $scan->lng)
                                        <a href="https://www.openstreetmap.org/?mlat={{ $scan->lat }}&mlon={{ $scan->lng }}&zoom=15" target="_blank" rel="noopener noreferrer" class="text-primary-600 dark:text-primary-400 hover:underline text-xs">
                                            {{ number_format($scan->lat, 2) }}, {{ number_format($scan->lng, 2) }}
                                        </a>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-mono text-xs text-gray-500 dark:text-gray-400">
                                    {{ $scan->ip_address ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-400 max-w-[140px] truncate" title="{{ $scan->browser }}">
                                    {{ $scan->browser ?? '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    @if ($scan->device_type === 'Mobile')
                                        <span class="inline-flex items-center rounded-md bg-purple-50 dark:bg-purple-900/20 px-2 py-0.5 text-xs font-medium text-purple-700 dark:text-purple-300 ring-1 ring-inset ring-purple-600/20">Mobil</span>
                                    @elseif ($scan->device_type === 'Tablet')
                                        <span class="inline-flex items-center rounded-md bg-blue-50 dark:bg-blue-900/20 px-2 py-0.5 text-xs font-medium text-blue-700 dark:text-blue-300 ring-1 ring-inset ring-blue-600/20">Tablet</span>
                                    @else
                                        <span class="inline-flex items-center rounded-md bg-gray-50 dark:bg-gray-800 px-2 py-0.5 text-xs font-medium text-gray-600 dark:text-gray-300 ring-1 ring-inset ring-gray-500/20">Desktop</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
