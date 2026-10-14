<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold">Son Okumalar (Harita)</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Toplam {{ count($this->scans) }} koordinatl&imath; okuma
                </p>
            </div>
        </div>
        <div
            x-data="{
                scans: {{ json_encode($this->scans) }},
                mapLoaded: false,
                map: null,
                async initMap() {
                    if (typeof L === 'undefined' || !window.UrundogrulaMap) {
                        setTimeout(() => this.initMap(), 200);
                        return;
                    }
                    try {
                        this.mapLoaded = false;
                        const el = this.$refs.mapEl;
                        const isDark = document.documentElement.classList.contains('dark');
                        this.map = L.map(el, { zoomControl: true, scrollWheelZoom: true }).setView([39.0, 35.0], 5);

                        // Light: OSM raster. Dark: OpenFreeMap vector (MapLibre).
                        await window.UrundogrulaMap.addBaseLayer(this.map, isDark);

                        const icon = L.divIcon({
                            className: '',
                            html: '<div style=&quot;background:#ef4444;width:16px;height:16px;border-radius:50%;border:3px solid white;box-shadow:0 0 6px rgba(0,0,0,0.5);&quot;></div>',
                            iconSize: [16, 16],
                            iconAnchor: [8, 8],
                        });

                        if (this.scans && this.scans.length > 0) {
                            const bounds = L.latLngBounds();
                            this.scans.forEach(scan => {
                                if (scan.lat && scan.lng) {
                                    const marker = L.marker([scan.lat, scan.lng], { icon }).addTo(this.map);
                                    marker.bindPopup(
                                        '<div style=&quot;font-family:inherit;min-width:150px;&quot;>' +
                                        '<div style=&quot;font-weight:700;margin-bottom:4px;color:#1f2937;font-size:13px;&quot;>' + scan.product_name + '</div>' +
                                        '<div style=&quot;font-size:12px;color:#6b7280;margin-bottom:2px;&quot;>Parti: ' + scan.batch_number + '</div>' +
                                        '<div style=&quot;font-size:12px;color:#6b7280;margin-bottom:2px;&quot;>Tarih: ' + scan.date + '</div>' +
                                        '<div style=&quot;font-size:12px;color:#6b7280;&quot;>Cihaz: ' + (scan.device || 'Bilinmiyor') + '</div>' +
                                        '</div>'
                                    );
                                    bounds.extend([scan.lat, scan.lng]);
                                }
                            });
                            if (bounds.isValid()) {
                                this.map.fitBounds(bounds, { padding: [50, 50], maxZoom: 12 });
                            }
                        }

                        this.map.invalidateSize();
                        this.mapLoaded = true;
                    } catch (e) {
                        console.error('MapWidget error:', e.message);
                        setTimeout(() => this.initMap(), 200);
                    }
                }
            }"
            x-init="initMap()"
            class="w-full mt-4 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 z-0 relative"
            style="height: 450px;"
            x-ref="mapEl"
        >
            <div class="absolute inset-0 flex items-center justify-center bg-gray-50 dark:bg-gray-900 z-10" x-show="!mapLoaded">
                <x-filament::loading-indicator class="h-8 w-8 text-primary-500" />
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>