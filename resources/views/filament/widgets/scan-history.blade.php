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
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">Tarih</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">IP Adresi</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">Tarayıcı</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">Cihaz</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">Konum</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($scans as $scan)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="px-3 py-2 whitespace-nowrap">{{ $scan->scanned_at?->format('d.m.Y H:i') ?? $scan->created_at->format('d.m.Y H:i') }}</td>
                                <td class="px-3 py-2 font-mono text-xs">{{ $scan->ip_address ?? '-' }}</td>
                                <td class="px-3 py-2">{{ $scan->browser ?? '-' }}</td>
                                <td class="px-3 py-2">{{ $scan->device_type ?? '-' }}</td>
                                <td class="px-3 py-2">
                                    @if ($scan->lat && $scan->lng)
                                        <a href="https://www.google.com/maps?q={{ $scan->lat }},{{ $scan->lng }}" target="_blank" class="text-primary-600 hover:underline">
                                            {{ number_format($scan->lat, 4) }}, {{ number_format($scan->lng, 4) }}
                                        </a>
                                    @else
                                        -
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
