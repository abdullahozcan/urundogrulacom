<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold">Son Okumalar (Harita)</h2>
        </div>
        
        <!-- Leaflet Map Container -->
        <div 
            x-data="mapWidget({ scans: {{ json_encode($this->scans) }} })" 
            x-init="initMap()" 
            class="w-full h-96 rounded-xl overflow-hidden shadow-inner border border-gray-200 z-0 relative"
            id="scans-map"
        >
            <div class="absolute inset-0 flex items-center justify-center bg-gray-50 z-10" x-show="!mapLoaded">
                <x-filament::loading-indicator class="h-8 w-8 text-primary-500" />
            </div>
        </div>
    </x-filament::section>

    <!-- Leaflet CSS & JS via CDN -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('mapWidget', ({ scans }) => ({
                scans: scans,
                mapLoaded: false,
                map: null,
                
                initMap() {
                    // Give a small delay to ensure container is fully rendered and sized
                    setTimeout(() => {
                        this.mapLoaded = true;
                        
                        // Default center (Turkey roughly)
                        this.map = L.map('scans-map').setView([39.0, 35.0], 5);

                        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                            attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
                            subdomains: 'abcd',
                            maxZoom: 20
                        }).addTo(this.map);

                        const customIcon = L.divIcon({
                            className: 'custom-pin',
                            html: `<div style="background-color: #10b981; width: 14px; height: 14px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 4px rgba(0,0,0,0.4);"></div>`,
                            iconSize: [14, 14],
                            iconAnchor: [7, 7]
                        });

                        // Add markers
                        if (this.scans && this.scans.length > 0) {
                            const bounds = L.latLngBounds();
                            
                            this.scans.forEach(scan => {
                                if (scan.lat && scan.lng) {
                                    const marker = L.marker([scan.lat, scan.lng], { icon: customIcon }).addTo(this.map);
                                    
                                    // Tooltip/Popup content
                                    const popupContent = `
                                        <div style="font-family: inherit; min-width: 150px;">
                                            <div style="font-weight: bold; margin-bottom: 4px; color: #1f2937;">${scan.product_name}</div>
                                            <div style="font-size: 12px; color: #6b7280; margin-bottom: 2px;">Parti: ${scan.batch_number}</div>
                                            <div style="font-size: 12px; color: #6b7280; margin-bottom: 2px;">Tarih: ${scan.date}</div>
                                            <div style="font-size: 12px; color: #6b7280;">Cihaz: ${scan.device || 'Bilinmiyor'}</div>
                                        </div>
                                    `;
                                    
                                    marker.bindPopup(popupContent);
                                    bounds.extend([scan.lat, scan.lng]);
                                }
                            });
                            
                            // Fit bounds to show all markers if any exist
                            if (bounds.isValid()) {
                                this.map.fitBounds(bounds, { padding: [50, 50], maxZoom: 12 });
                            }
                        }
                    }, 100);
                }
            }));
        });
    </script>
</x-filament-widgets::widget>
