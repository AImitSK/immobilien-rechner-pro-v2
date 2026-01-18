/**
 * Central Icon Path Management
 *
 * This module provides a centralized configuration for all icon paths
 * used throughout the Immobilien Rechner Pro plugin.
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
     * Located in: assets/images/ and assets/icon/ausstattung/
     */
    FEATURES: {
        // Exterior features (Aussenbereich)
        balkon: 'assets/images/balkon.svg',
        terrasse: 'assets/images/terrasse.svg',
        garten: 'assets/images/garten.svg',
        garage: 'assets/images/garage.svg',
        stellplatz: 'assets/images/stellplatz.svg',
        solaranlage: 'assets/icon/ausstattung/solaranlage.svg',

        // Interior features (Innenbereich)
        aufzug: 'assets/images/aufzug.svg',
        keller: 'assets/images/keller.svg',
        kueche: 'assets/images/kueche.svg',
        fussbodenheizung: 'assets/images/fussbodenheizung.svg',
        wc: 'assets/images/wc.svg',
        barrierefrei: 'assets/images/barrierefrei.svg',
        dachboden: 'assets/icon/ausstattung/dachboden.svg',
        kamin: 'assets/icon/ausstattung/kamin.svg',
        parkettboden: 'assets/icon/ausstattung/parkettboden.svg',
    },

    /**
     * Property type icons (Immobilientypen)
     * Located in: assets/images/ and assets/icon/immobilientyp/
     */
    PROPERTY_TYPES: {
        wohnung: 'assets/images/wohnung.svg',
        haus: 'assets/images/haus.svg',
        gewerbe: 'assets/images/gewerbe.svg',
        grundstueck: 'assets/icon/immobilientyp/grundstueck.svg',
    },

    /**
     * Condition icons (Zustand)
     * Located in: assets/images/
     */
    CONDITIONS: {
        neubau: 'assets/images/neubau.svg',
        renoviert: 'assets/images/renoviert.svg',
        gut: 'assets/images/gut.svg',
        reparaturen: 'assets/images/reparaturen.svg',
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
 * // Returns: "https://example.com/wp-content/plugins/immobilien-rechner-pro/assets/images/balkon.svg"
 */
export function getIconUrl(relativePath) {
    return `${ICON_BASE_PATH}${relativePath}`;
}

/**
 * Helper function to get all icons in a category as an array with full URLs.
 *
 * @param {Object} category - A category object from ICON_PATHS (e.g., ICON_PATHS.FEATURES)
 * @returns {Array<{id: string, url: string}>} Array of icon objects with id and full URL
 *
 * @example
 * const featureIcons = getIconsInCategory(ICON_PATHS.FEATURES);
 * // Returns: [{ id: 'balkon', url: 'https://...' }, { id: 'terrasse', url: 'https://...' }, ...]
 */
export function getIconsInCategory(category) {
    return Object.entries(category).map(([id, path]) => ({
        id,
        url: getIconUrl(path),
    }));
}

export default ICON_PATHS;
