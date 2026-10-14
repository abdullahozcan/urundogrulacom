/**
 * Bridges the ES-module bridge to the Leaflet instance that is already on the
 * page as the global `L` (loaded by AdminPanelProvider).
 *
 * The bridge only ever uses `LeafletModule.default || LeafletModule`, so
 * exporting the existing global here means it extends the *same* Leaflet
 * class hierarchy as the map. Handing it a second, separate Leaflet copy would
 * break `instanceof` checks inside Leaflet's class system.
 */
const L = globalThis.L;

if (!L) {
    throw new Error('Leaflet (globalThis.L) must be loaded before the MapLibre bridge.');
}

export default L;
