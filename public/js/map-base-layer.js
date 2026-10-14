/**
 * Base layer selection for the admin map widgets.
 *
 * Tile providers:
 *  - Light: OpenStreetMap standard raster tiles (unchanged).
 *  - Dark:  OpenFreeMap vector tiles (`dark` style).
 *
 * Why the dark theme changed: the previous CARTO endpoint
 * (`basemaps.cartocdn.com/dark_all`) is no longer a sanctioned free use —
 * CARTO now requires an API key and its basemap licence restricts free public
 * use to enterprise/non-profit, which does not cover a commercial SaaS.
 * OpenFreeMap is free with no key, no registration, no quota and no
 * commercial-use restriction, but it only publishes *vector* tiles, which
 * Leaflet cannot render on its own. The MapLibre bridge adds one as a plain
 * Leaflet layer, so markers, popups and fitBounds are unaffected.
 *
 * Everything except the base layer is untouched in the widgets.
 */
(function (global) {
    'use strict';

    var OSM_LIGHT_URL = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
    var OSM_ATTRIBUTION =
        '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';

    var OPENFREEMAP_STYLE = 'https://tiles.openfreemap.org/styles/dark';
    var OPENFREEMAP_ATTRIBUTION =
        '&copy; <a href="https://openfreemap.org">OpenFreeMap</a> ' +
        '&copy; <a href="https://www.openmaptiles.org/">OpenMapTiles</a> ' +
        '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';

    // The bridge is ~1 MB of MapLibre; it must not be fetched in light mode.
    // Cached because a page can host more than one map widget.
    var bridgePromise = null;

    function loadBridge() {
        if (bridgePromise) {
            return bridgePromise;
        }

        bridgePromise = import('/vendor/maplibre/leaflet-maplibre-gl.mjs')
            .then(function (module) {
                return module.maplibreGL;
            })
            .catch(function (error) {
                bridgePromise = null;
                throw error;
            });

        return bridgePromise;
    }

    /**
     * Attach the appropriate base layer to a Leaflet map.
     *
     * Resolves once the layer is on the map and ready. Falls back to a plain
     * dark raster style if MapLibre cannot run (no WebGL, blocked module
     * script, offline) so the widget never renders an empty box.
     *
     * @param {object} map      Leaflet map instance.
     * @param {boolean} isDark  Whether the panel is in dark mode.
     * @returns {Promise<object|null>} The added layer, or null on failure.
     */
    function addBaseLayer(map, isDark) {
        if (!isDark) {
            return Promise.resolve(
                L.tileLayer(OSM_LIGHT_URL, {
                    attribution: OSM_ATTRIBUTION,
                    maxZoom: 19,
                }).addTo(map)
            );
        }

        return loadBridge()
            .then(function (maplibreGL) {
                return maplibreGL({
                    style: OPENFREEMAP_STYLE,
                    attribution: OPENFREEMAP_ATTRIBUTION,
                }).addTo(map);
            })
            .catch(function (error) {
                console.warn(
                    'OpenFreeMap vector layer unavailable, falling back to raster:',
                    error
                );

                return L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                    attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
                    maxZoom: 19,
                }).addTo(map);
            });
    }

    global.UrundogrulaMap = global.UrundogrulaMap || {};
    global.UrundogrulaMap.addBaseLayer = addBaseLayer;
})(window);