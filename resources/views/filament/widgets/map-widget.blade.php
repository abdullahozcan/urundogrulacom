<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold">Son Okumalar (Harita)</h2>
        </div>
        
        <!-- Leaflet Map Container -->
        <div 
            x-data="{
                scans: {{ json_encode($this->scans) }},
                mapLoaded: false,
                map: null,
                initMap() {
                    // Check if L (Leaflet) is loaded
                    if (typeof L === 'undefined') {
                        setTimeout(() => this.initMap(), 200);
                        return;
                    }
                    setTimeout(() => {
                        this.mapLoaded = true;
                        this.map = L.map($refs.mapEl).setView([39.0, 35.0], 5);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; <a href=\'https://www.openstreetmap.org/copyright\'>OpenStreetMap</a> contributors',
                            maxZoom: 19
                        }).addTo(this.map);

                        const customIcon = L.divIcon({
                            className: 'custom-pin',
                            html: `<div style='background-color: #ef4444; width: 16px; height: 16px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 6px rgba(0,0,0,0.5);'></div>`,
                            iconSize: [16, 16],
                            iconAnchor: [8, 8]
                        });

                        if (this.scans && this.scans.length > 0) {
                            const bounds = L.latLngBounds();
                            this.scans.forEach(scan => {
                                if (scan.lat && scan.lng) {
                                    const marker = L.marker([scan.lat, scan.lng], { icon: customIcon }).addTo(this.map);
                                    const popupContent = `
                                        <div style='font-family: inherit; min-width: 150px;'>
                                            <div style='font-weight: bold; margin-bottom: 4px; color: #1f2937;'>${scan.product_name}</div>
                                            <div style='font-size: 12px; color: #6b7280; margin-bottom: 2px;'>Parti: ${scan.batch_number}</div>
                                            <div style='font-size: 12px; color: #6b7280; margin-bottom: 2px;'>Tarih: ${scan.date}</div>
                                            <div style='font-size: 12px; color: #6b7280;'>Cihaz: ${scan.device || 'Bilinmiyor'}</div>
                                        </div>
                                    `;
                                    marker.bindPopup(popupContent);
                                    bounds.extend([scan.lat, scan.lng]);
                                }
                            });
                            if (bounds.isValid()) {
                                this.map.fitBounds(bounds, { padding: [50, 50], maxZoom: 12 });
                            }
                        }
                    }, 300);
                }
            }" 
            x-init="initMap()" 
            class="w-full h-96 rounded-xl overflow-hidden shadow-inner border border-gray-200 z-0 relative"
            x-ref="mapEl"
        >
            <div class="absolute inset-0 flex items-center justify-center bg-gray-50 z-10" x-show="!mapLoaded">
                <x-filament::loading-indicator class="h-8 w-8 text-primary-500" />
            </div>
        </div>
    </x-filament::section>

</x-filament-widgets::widget>
