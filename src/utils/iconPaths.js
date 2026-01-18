/**
 * Central Icon Path Management
 *
 * This module provides a centralized configuration for all icon paths
 * used throughout the Immobilien Rechner Pro plugin.
 *
 * All icons are now consolidated in assets/icon/ with category subdirectories:
 * - ausstattung/     (Feature icons)
 * - immobilientyp/   (Property type icons)
 * - zustand/         (Condition icons)
 * - haustypen/       (House type icons)
 * - qualitaetsstufen/ (Quality level icons)
 * - nutzung/         (Usage icons)
 * - modernisierung/  (Modernization icons)
 * - zeitrahmen/      (Timeframe icons)
 *
 * Usage:
 *   import { ICON_BASE_PATH, ICON_PATHS, getIconUrl } from './utils/iconPaths';
 *   const balkonIcon = getIconUrl(ICON_PATHS.FEATURES.balkon);
 *
 * @since 1.0.0
 */

/**
 * Base path for all plugin assets.
 * Uses WordPress localized settings (irpSettings.pluginUrl).
 */
export const ICON_BASE_PATH = window.irpSettings?.pluginUrl || '';

/**
 * Icon path definitions organized by category.
 * All paths are relative to the plugin root directory.
 */
export const ICON_PATHS = {
    /**
     * Feature icons (Ausstattungsmerkmale)
     * Located in: assets/icon/ausstattung/
     */
    FEATURES: {
        // Exterior features (Aussenbereich)
        balkon: 'assets/icon/ausstattung/balkon.svg',
        terrasse: 'assets/icon/ausstattung/terrasse.svg',
        garten: 'assets/icon/ausstattung/garten.svg',
        garage: 'assets/icon/ausstattung/garage.svg',
        stellplatz: 'assets/icon/ausstattung/stellplatz.svg',
        solaranlage: 'assets/icon/ausstattung/solaranlage.svg',

        // Interior features (Innenbereich)
        aufzug: 'assets/icon/ausstattung/aufzug.svg',
        keller: 'assets/icon/ausstattung/keller.svg',
        kueche: 'assets/icon/ausstattung/kueche.svg',
        fussbodenheizung: 'assets/icon/ausstattung/fussbodenheizung.svg',
        wc: 'assets/icon/ausstattung/wc.svg',
        barrierefrei: 'assets/icon/ausstattung/barrierefrei.svg',
        dachboden: 'assets/icon/ausstattung/dachboden.svg',
        kamin: 'assets/icon/ausstattung/kamin.svg',
        parkettboden: 'assets/icon/ausstattung/parkettboden.svg',
    },

    /**
     * Property type icons (Immobilientypen)
     * Located in: assets/icon/immobilientyp/
     */
    PROPERTY_TYPES: {
        wohnung: 'assets/icon/immobilientyp/wohnung.svg',
        haus: 'assets/icon/immobilientyp/haus.svg',
        gewerbe: 'assets/icon/immobilientyp/gewerbe.svg',
        grundstueck: 'assets/icon/immobilientyp/grundstueck.svg',
    },

    /**
     * Condition icons (Zustand)
     * Located in: assets/icon/zustand/
     */
    CONDITIONS: {
        neubau: 'assets/icon/zustand/neubau.svg',
        renoviert: 'assets/icon/zustand/renoviert.svg',
        gut: 'assets/icon/zustand/gut.svg',
        reparaturen: 'assets/icon/zustand/reparaturen.svg',
    },

    /**
     * Quality level icons (Qualitaetsstufen)
     * Located in: assets/icon/qualitaetsstufen/
     */
    QUALITY: {
        einfach: 'assets/icon/qualitaetsstufen/einfach.svg',
        normal: 'assets/icon/qualitaetsstufen/normal.svg',
        gehoben: 'assets/icon/qualitaetsstufen/gehoben.svg',
        luxurioes: 'assets/icon/qualitaetsstufen/luxurioes.svg',
    },

    /**
     * Usage icons (Nutzungsart)
     * Located in: assets/icon/nutzung/
     */
    USAGE: {
        kaufen: 'assets/icon/nutzung/kaufen.svg',
        verkaufen: 'assets/icon/nutzung/verkaufen.svg',
        selbstgenutzt: 'assets/icon/nutzung/selbstgenutzt.svg',
        vermietet: 'assets/icon/nutzung/vermietet.svg',
        leerstand: 'assets/icon/nutzung/leerstand.svg',
    },

    /**
     * House type icons (Haustypen)
     * Located in: assets/icon/haustypen/
     */
    HOUSE_TYPES: {
        einfamilienhaus: 'assets/icon/haustypen/einfamilienhaus.svg',
        mehrfamilienhaus: 'assets/icon/haustypen/mehrfamilienhaus.svg',
        doppelhaushaelfte: 'assets/icon/haustypen/doppelhaushaelfte.svg',
        bungalow: 'assets/icon/haustypen/bungalow.svg',
        endreihenhaus: 'assets/icon/haustypen/endreihenhaus.svg',
        mittelreihenhaus: 'assets/icon/haustypen/mittelreihenhaus.svg',
    },

    /**
     * Miscellaneous icons
     * Located in: assets/icon/
     */
    MISC: {
        modernisierung: 'assets/icon/modernisierung/modernisierung.svg',
        zeitrahmen: 'assets/icon/zeitrahmen/zeitrahmen.svg',
    },
};

/**
 * Helper function to get the full URL for an icon.
 *
 * @param {string} relativePath - The relative path from ICON_PATHS
 * @returns {string} The full URL to the icon
 *
 * @example
 * const url = getIconUrl(ICON_PATHS.FEATURES.balkon);
 * // Returns: "https://example.com/wp-content/plugins/immobilien-rechner-pro/assets/icon/ausstattung/balkon.svg"
 */
export function getIconUrl(relativePath) {
    return `${ICON_BASE_PATH}${relativePath}`;
}

/**
 * Helper function to get all icons in a category as an array with full URLs.
 *
 * @param {Object} category - A category object from ICON_PATHS (e.g., ICON_PATHS.FEATURES)
 * @returns {Array<{id: string, url: string, path: string}>} Array of icon objects with id, full URL, and relative path
 *
 * @example
 * const featureIcons = getIconsInCategory(ICON_PATHS.FEATURES);
 * // Returns: [{ id: 'balkon', url: 'https://...', path: 'assets/icon/ausstattung/balkon.svg' }, ...]
 */
export function getIconsInCategory(category) {
    return Object.entries(category).map(([id, path]) => ({
        id,
        url: getIconUrl(path),
        path,
    }));
}

/**
 * Get all icon paths as a flat array (useful for preloading).
 *
 * @returns {string[]} Array of all icon relative paths
 */
export function getAllIconPaths() {
    const paths = [];
    Object.values(ICON_PATHS).forEach((category) => {
        Object.values(category).forEach((path) => {
            paths.push(path);
        });
    });
    return paths;
}

export default ICON_PATHS;
