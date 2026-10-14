/**
 * Vendors MapLibre + the MapLibre/Leaflet bridge into public/vendor/maplibre/.
 *
 * Why vendor instead of a CDN script:
 *  - The admin panel loads Leaflet from unpkg, but MapLibre's bridge is an ES
 *    module that imports bare specifiers ("leaflet", "maplibre-gl"). Serving
 *    it ourselves lets us point those at concrete local files with no import
 *    map and no build tool, which also keeps a third-party JS bundle out of
 *    the panel's Content-Security-Policy allowlist.
 *
 * Run via: npm run build:maplibres
 */
import { mkdir, copyFile, readFile, writeFile, rm } from 'node:fs/promises';
import { existsSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const outDir = join(root, 'public', 'vendor', 'maplibre');

const MAPLIBRE_DIST = join(root, 'node_modules', 'maplibre-gl', 'dist');
const BRIDGE_DIST = join(root, 'node_modules', '@maplibre', 'maplibre-gl-leaflet', 'dist');

/** Resolved once and cached, so several widgets on one page import it once. */
const SHIM = `/**
 * Bridges the ES-module bridge to the Leaflet instance that is already on the
 * page as the global \`L\` (loaded by AdminPanelProvider).
 *
 * The bridge only ever uses \`LeafletModule.default || LeafletModule\`, so
 * exporting the existing global here means it extends the *same* Leaflet
 * class hierarchy as the map. Handing it a second, separate Leaflet copy would
 * break \`instanceof\` checks inside Leaflet's class system.
 */
const L = globalThis.L;

if (!L) {
    throw new Error('Leaflet (globalThis.L) must be loaded before the MapLibre bridge.');
}

export default L;
`;

const BRIDGE_PATCHES = [
    // 'leaflet' has no ESM build on disk and must not be duplicated.
    ['from "leaflet";', 'from "./leaflet-global.mjs";'],
    // Resolve the bare specifier to the vendored copy.
    ['from "maplibre-gl";', 'from "./maplibre-gl.mjs";'],
];

async function main() {
    if (!existsSync(MAPLIBRE_DIST)) {
        throw new Error('maplibre-gl is not installed. Run: npm install');
    }

    await rm(outDir, { recursive: true, force: true });
    await mkdir(outDir, { recursive: true });

    // maplibre-gl.mjs resolves its worker as `./maplibre-gl-worker.mjs`
    // relative to import.meta.url, so the worker MUST sit beside it.
    for (const file of ['maplibre-gl.mjs', 'maplibre-gl-worker.mjs', 'maplibre-gl.css']) {
        await copyFile(join(MAPLIBRE_DIST, file), join(outDir, file));
    }

    await writeFile(join(outDir, 'leaflet-global.mjs'), SHIM, 'utf8');

    const bridgeSource = await readFile(join(BRIDGE_DIST, 'leaflet-maplibre-gl.mjs'), 'utf8');
    let patched = bridgeSource;

    for (const [from, to] of BRIDGE_PATCHES) {
        if (!patched.includes(from)) {
            throw new Error(`Bridge patch target not found: ${from}`);
        }
        patched = patched.replace(from, to);
    }

    await writeFile(join(outDir, 'leaflet-maplibre-gl.mjs'), patched, 'utf8');

    console.log(`Vendored MapLibre into public/vendor/maplibre (${outDir.replace(root + '/', '')})`);
}

main().catch((error) => {
    console.error(error.message);
    process.exit(1);
});