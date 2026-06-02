<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Konum
        </x-slot>

        @php
            $scan = $this->record;
            $hasLocation = $scan && $scan->lat && $scan->lng;
        @endphp

        @if ($hasLocation)
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider font-medium">Enlem</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($scan->lat, 6) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider font-medium">Boylam</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($scan->lng, 6) }}</p>
                </div>
            </div>

            <a href="https://www.openstreetmap.org/?mlat={{ $scan->lat }}&mlon={{ $scan->lng }}&zoom=17&layers=M" target="_blank" rel="noopener noreferrer" class="text-xs text-primary-600 dark:text-primary-400 hover:underline mb-3 inline-block">
                OpenStreetMap'te aç &rarr;
            </a>

            <div
                x-data="{
                    mapLoaded: false,
                    initMap() {
                        if (typeof L === 'undefined') {
                            setTimeout(() => this.initMap(), 200);
                            return;
                        }
                        setTimeout(() => {
                            this.mapLoaded = true;
                            const map = L.map($refs.mapEl, { zoomControl: true, scrollWheelZoom: true }).setView([{{ $scan->lat }}, {{ $scan->lng }}], 16);
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a>',
                                maxZoom: 19
                            }).addTo(map);
                            L.marker([{{ $scan->lat }}, {{ $scan->lng }}], {
                                icon: L.divIcon({
                                    className: '',
                                    html: '<div style=\"background:#ef4444;width:24px;height:24px;border-radius:50%;border:4px solid white;box-shadow:0 2px 12px rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;\"><svg xmlns=\"http://www.w3.org/2000/svg\" width=\"12\" height=\"12\" viewBox=\"0 0 24 24\" fill=\"white\"><path d=\"M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z\"/></svg></div>',
                                    iconSize: [24, 24],
                                    iconAnchor: [12, 24],
                                    popupAnchor: [0, -24],
                                })
                            }).addTo(map)
                            .bindPopup(`
                                <div style="font-family:inherit;min-width:120px;">
                                    <div style="font-weight:700;font-size:13px;color:#1f2937;">Okuma Konumu</div>
                                    <div style="font-size:11px;color:#6b7280;">{{ number_format($scan->lat, 4) }}, {{ number_format($scan->lng, 4) }}</div>
                                </div>
                            `)
                            .openPopup();
                        }, 300);
                    }
                }"
                x-init="initMap()"
                class="w-full h-64 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 z-0 relative"
                x-ref="mapEl"
            >
                <div class="absolute inset-0 flex items-center justify-center bg-gray-50 dark:bg-gray-900 z-10" x-show="!mapLoaded">
                    <x-filament::loading-indicator class="h-8 w-8 text-primary-500" />
                </div>
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-8 text-gray-400 dark:text-gray-500">
                <x-filament::icon alias="heroicon-o-map-pin" class="w-12 h-12 mb-3" />
                <p class="text-sm">Bu okuma için konum bilgisi bulunmuyor.</p>
            </div>
        @endif
    </x-filament::section>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
</x-filament-widgets::widget>
