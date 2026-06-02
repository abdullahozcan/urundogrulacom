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

            <x-filament::modal id="osm-modal" heading="OpenStreetMap" width="5xl" close-button>
                <x-slot name="trigger" class="cursor-pointer text-xs text-primary-600 dark:text-primary-400 hover:underline mb-3 inline-block">
                    OpenStreetMap&rsquo;te a&ccedil; &rarr;
                </x-slot>
                <iframe
                    src="https://www.openstreetmap.org/export/embed.html?bbox={{ $scan->lng - 0.01 }}%2C{{ $scan->lat - 0.01 }}%2C{{ $scan->lng + 0.01 }}%2C{{ $scan->lat + 0.01 }}&amp;layer=mapnik&amp;marker={{ $scan->lat }}%2C{{ $scan->lng }}"
                    width="100%"
                    height="500"
                    style="border:0; border-radius: 0.5rem;"
                    allowfullscreen
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                ></iframe>
            </x-filament::modal>

            <div 
                x-data="{
                    lat: {{ $scan->lat }},
                    lng: {{ $scan->lng }},
                    mapLoaded: false,
                    map: null,
                    initMap() {
                        if (typeof L === 'undefined') {
                            setTimeout(() => this.initMap(), 200);
                            return;
                        }
                        try {
                            this.map = L.map(this.$refs.mapEl, { zoomControl: true, scrollWheelZoom: true }).setView([this.lat, this.lng], 16);
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '&copy; OpenStreetMap contributors',
                                maxZoom: 19
                            }).addTo(this.map);
                            L.marker([this.lat, this.lng], {
                                icon: L.divIcon({
                                    className: '',
                                    html: '<div style=&quot;background:#ef4444;width:24px;height:24px;border-radius:50%;border:4px solid white;box-shadow:0 2px 12px rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;&quot;><svg xmlns=&quot;http://www.w3.org/2000/svg&quot; width=&quot;12&quot; height=&quot;12&quot; viewBox=&quot;0 0 24 24&quot; fill=&quot;white&quot;><path d=&quot;M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z&quot;/></svg></div>',
                                    iconSize: [24, 24],
                                    iconAnchor: [12, 24],
                                    popupAnchor: [0, -24],
                                })
                            }).addTo(this.map)
                            .bindPopup('<div style=&quot;font-family:inherit;min-width:120px;&quot;><div style=&quot;font-weight:700;font-size:13px;color:#1f2937;&quot;>Okuma Konumu</div><div style=&quot;font-size:11px;color:#6b7280;&quot;>' + this.lat.toFixed(4) + ', ' + this.lng.toFixed(4) + '</div></div>')
                            .openPopup();

                            this.map.invalidateSize();
                            this.mapLoaded = true;
                        } catch (e) {
                            console.error('ScanLocationWidget error:', e.message);
                            setTimeout(() => this.initMap(), 500);
                        }
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
                <p class="text-sm">Bu okuma i&ccedil;in konum bilgisi bulunmuyor.</p>
            </div>
        @endif
    </x-filament::section>

</x-filament-widgets::widget>
