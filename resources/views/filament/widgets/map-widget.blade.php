<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold">Son Okumalar (Harita)</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Toplam {{ count($this->scans) }} koordinatl&imath; okuma
                </p>
            </div>
            <x-filament::button x-on:click="$dispatch('open-modal', { id: 'dashboard-map-modal' })">
                Haritay&imath; G&ouml;r
            </x-filament::button>
        </div>
    </x-filament::section>

    <x-filament::modal id="dashboard-map-modal" heading="Okuma Konumlar&imath;" width="7xl" close-button>
        <div
            x-data="{
                scans: {{ json_encode($this->scans) }},
                mapLoaded: true,
                map: null,
                initMap() {
                    if (typeof L === 'undefined') {
                        setTimeout(() => this.initMap(), 200);
                        return;
                    }
                    try {
                        this.mapLoaded = false;
                        this.map = L.map(this.$refs.mapEl, { zoomControl: true, scrollWheelZoom: true }).setView([39.0, 35.0], 5);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; OpenStreetMap contributors',
                            maxZoom: 19
                        }).addTo(this.map);

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
                        setTimeout(() => this.initMap(), 500);
                    }
                }
            }"
            x-on:open-modal.window="if ($event.detail.id === 'dashboard-map-modal') { $nextTick(() => initMap()); }"
            class="w-full h-[500px] rounded-xl overflow-hidden z-0 relative"
            x-ref="mapEl"
        >
            <div class="absolute inset-0 flex items-center justify-center bg-gray-50 dark:bg-gray-900 z-10" x-show="!mapLoaded">
                <x-filament::loading-indicator class="h-8 w-8 text-primary-500" />
            </div>
        </div>
    </x-filament::modal>
</x-filament-widgets::widget>