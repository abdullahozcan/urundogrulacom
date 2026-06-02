<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Okuma Konumlar&imath;
        </x-slot>

        <div 
            x-data="{
                scans: {{ json_encode($this->scans) }},
                mapLoaded: false,
                map: null,
                initMap() {
                    if (typeof L === 'undefined') {
                        setTimeout(() => this.initMap(), 200);
                        return;
                    }
                    this.$nextTick(() => {
                        setTimeout(() => {
                            this.mapLoaded = true;
                            this.map = L.map(this.$refs.mapEl, { zoomControl: true, scrollWheelZoom: true }).setView([39.0, 35.0], 5);
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '&copy; OpenStreetMap contributors',
                                maxZoom: 19
                            }).addTo(this.map);

                            const icon = L.divIcon({
                                className: '',
                                html: '<div style=&quot;background:#0e42ff;width:14px;height:14px;border-radius:50%;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.4);&quot;></div>',
                                iconSize: [14, 14],
                                iconAnchor: [7, 7],
                            });

                            if (this.scans && this.scans.length > 0) {
                                const bounds = L.latLngBounds();
                                this.scans.forEach(scan => {
                                    if (scan.lat && scan.lng) {
                                        const marker = L.marker([scan.lat, scan.lng], { icon }).addTo(this.map);
                                        marker.bindPopup(
                                            '<div style=&quot;font-family:inherit;min-width:160px;&quot;>' +
                                            '<div style=&quot;font-weight:700;margin-bottom:4px;color:#1f2937;font-size:13px;&quot;>' + scan.product_name + '</div>' +
                                            '<div style=&quot;font-size:11px;color:#6b7280;margin-bottom:2px;&quot;>Parti: ' + scan.batch_number + '</div>' +
                                            '<div style=&quot;font-size:11px;color:#6b7280;margin-bottom:2px;&quot;>' + scan.date + '</div>' +
                                            '<div style=&quot;font-size:11px;color:#6b7280;&quot;>' + scan.device + ' &middot; ' + scan.browser + '</div>' +
                                            '</div>'
                                        );
                                        bounds.extend([scan.lat, scan.lng]);
                                    }
                                });
                                if (bounds.isValid()) {
                                    this.map.fitBounds(bounds, { padding: [50, 50], maxZoom: 12 });
                                }
                            }
                        }, 100);
                    });
                }
            }" 
            x-init="initMap()" 
            class="w-full h-80 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 z-0 relative"
            x-ref="mapEl"
        >
            <div class="absolute inset-0 flex items-center justify-center bg-gray-50 dark:bg-gray-900 z-10" x-show="!mapLoaded">
                <x-filament::loading-indicator class="h-8 w-8 text-primary-500" />
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
