/**
 * Icon Component
 *
 * Loads SVG icons inline via fetch with caching support.
 * Enables CSS variable theming for icon colors.
 *
 * @since 1.0.0
 */

import { useState, useEffect, useRef, useMemo } from '@wordpress/element';
import { ICON_BASE_PATH, getIconUrl } from '../utils/iconPaths';

// Global SVG cache to avoid re-fetching
const svgCache = new Map();

// Pending requests to avoid duplicate fetches
const pendingRequests = new Map();

/**
 * Fetches and caches an SVG file.
 *
 * @param {string} url - The full URL to the SVG file
 * @returns {Promise<string>} The SVG content as a string
 */
async function fetchSvg(url) {
    // Return cached SVG if available
    if (svgCache.has(url)) {
        return svgCache.get(url);
    }

    // Return pending request if already in progress
    if (pendingRequests.has(url)) {
        return pendingRequests.get(url);
    }

    // Create new fetch request
    const request = fetch(url)
        .then((response) => {
            if (!response.ok) {
                throw new Error(`Failed to load icon: ${url}`);
            }
            return response.text();
        })
        .then((svgContent) => {
            // Cache the result
            svgCache.set(url, svgContent);
            pendingRequests.delete(url);
            return svgContent;
        })
        .catch((error) => {
            pendingRequests.delete(url);
            throw error;
        });

    pendingRequests.set(url, request);
    return request;
}

/**
 * Applies CSS variables to SVG content based on irpSettings.
 *
 * @param {string} svgContent - The raw SVG content
 * @returns {string} SVG content with updated CSS variables
 */
function applyCssVariables(svgContent) {
    const settings = window.irpSettings?.settings || {};
    const primaryColor = settings.primaryColor || '#428dff';

    // Generate color variations based on primary color
    const colors = generateColorVariations(primaryColor);

    // Replace the default CSS variables in the style block
    let updatedSvg = svgContent;

    // Update the CSS variables in the inline style block
    const stylePattern =
        /<style>:root\{--irp-icon-primary:#[0-9a-fA-F]{6};--irp-icon-secondary:#[0-9a-fA-F]{6};--irp-icon-light:#[0-9a-fA-F]{6};--irp-icon-bg:#[0-9a-fA-F]{6};\}<\/style>/;

    const newStyleBlock = `<style>:root{--irp-icon-primary:${colors.primary};--irp-icon-secondary:${colors.secondary};--irp-icon-light:${colors.light};--irp-icon-bg:${colors.bg};}</style>`;

    if (stylePattern.test(updatedSvg)) {
        updatedSvg = updatedSvg.replace(stylePattern, newStyleBlock);
    }

    // Also replace hardcoded default colors in SVG elements (case-insensitive)
    // Default colors: primary=#428dff, secondary=#7facfa, light=#a4c2f7, bg=#e8edfc
    // Some SVGs also use: #D4E1F4 (similar to bg/light)
    updatedSvg = updatedSvg.replace(/#428[dD][fF][fF]/g, colors.primary);
    updatedSvg = updatedSvg.replace(/#7[fF][aA][cC][fF][aA]/g, colors.secondary);
    updatedSvg = updatedSvg.replace(/#[aA]4[cC]2[fF]7/g, colors.light);
    updatedSvg = updatedSvg.replace(/#[eE]8[eE][dD][fF][cC]/g, colors.bg);
    updatedSvg = updatedSvg.replace(/#[dD]4[eE]1[fF]4/g, colors.bg);

    return updatedSvg;
}

/**
 * Generates color variations from a primary color.
 *
 * @param {string} primaryColor - The primary color in hex format
 * @returns {Object} Object with primary, secondary, light, and bg colors
 */
function generateColorVariations(primaryColor) {
    // Parse hex color
    const hex = primaryColor.replace('#', '');
    const r = parseInt(hex.substring(0, 2), 16);
    const g = parseInt(hex.substring(2, 4), 16);
    const b = parseInt(hex.substring(4, 6), 16);

    // Generate variations by mixing with white
    const mixWithWhite = (r, g, b, factor) => {
        const newR = Math.round(r + (255 - r) * factor);
        const newG = Math.round(g + (255 - g) * factor);
        const newB = Math.round(b + (255 - b) * factor);
        return `#${newR.toString(16).padStart(2, '0')}${newG.toString(16).padStart(2, '0')}${newB.toString(16).padStart(2, '0')}`;
    };

    return {
        primary: primaryColor,
        secondary: mixWithWhite(r, g, b, 0.5), // 50% lighter
        light: mixWithWhite(r, g, b, 0.7), // 70% lighter
        bg: mixWithWhite(r, g, b, 0.9), // 90% lighter
    };
}

/**
 * Icon Component
 *
 * @param {Object} props - Component props
 * @param {string} props.path - Relative path to the icon (from ICON_PATHS)
 * @param {number|string} props.size - Icon size in pixels (default: 48)
 * @param {number|string} props.width - Icon width (overrides size)
 * @param {number|string} props.height - Icon height (overrides size)
 * @param {string} props.className - Additional CSS class names
 * @param {string} props.alt - Alt text for accessibility
 * @param {boolean} props.applyTheme - Whether to apply theme colors (default: true)
 * @param {Object} props.style - Additional inline styles
 */
export default function Icon({
    path,
    size = 48,
    width,
    height,
    className = '',
    alt = '',
    applyTheme = true,
    style = {},
    ...rest
}) {
    const [svgContent, setSvgContent] = useState(null);
    const [error, setError] = useState(null);
    const [isLoading, setIsLoading] = useState(true);
    const containerRef = useRef(null);

    // Calculate dimensions
    const iconWidth = width || size;
    const iconHeight = height || size;

    // Generate URL from path
    const url = useMemo(() => {
        if (!path) return null;
        return path.startsWith('http') ? path : getIconUrl(path);
    }, [path]);

    useEffect(() => {
        if (!url) {
            setError('No icon path provided');
            setIsLoading(false);
            return;
        }

        let mounted = true;

        setIsLoading(true);
        setError(null);

        fetchSvg(url)
            .then((content) => {
                if (!mounted) return;

                // Apply CSS variables if theme is enabled
                const processedContent = applyTheme
                    ? applyCssVariables(content)
                    : content;
                setSvgContent(processedContent);
                setIsLoading(false);
            })
            .catch((err) => {
                if (!mounted) return;
                setError(err.message);
                setIsLoading(false);
            });

        return () => {
            mounted = false;
        };
    }, [url, applyTheme]);

    // Update SVG dimensions after rendering
    useEffect(() => {
        if (!containerRef.current || !svgContent) return;

        const svg = containerRef.current.querySelector('svg');
        if (svg) {
            svg.setAttribute('width', iconWidth);
            svg.setAttribute('height', iconHeight);
            svg.style.display = 'block';
        }
    }, [svgContent, iconWidth, iconHeight]);

    const containerStyle = {
        display: 'inline-flex',
        alignItems: 'center',
        justifyContent: 'center',
        width: iconWidth,
        height: iconHeight,
        ...style,
    };

    if (error) {
        // Fallback: show a placeholder or nothing
        return (
            <span
                className={`irp-icon irp-icon--error ${className}`}
                style={containerStyle}
                title={error}
                {...rest}
            />
        );
    }

    if (isLoading) {
        return (
            <span
                className={`irp-icon irp-icon--loading ${className}`}
                style={containerStyle}
                {...rest}
            />
        );
    }

    return (
        <span
            ref={containerRef}
            className={`irp-icon ${className}`}
            style={containerStyle}
            role={alt ? 'img' : 'presentation'}
            aria-label={alt || undefined}
            aria-hidden={!alt}
            dangerouslySetInnerHTML={{ __html: svgContent }}
            {...rest}
        />
    );
}

/**
 * Pre-loads icons for better performance.
 *
 * @param {string[]} paths - Array of icon paths to preload
 */
export function preloadIcons(paths) {
    paths.forEach((path) => {
        const url = path.startsWith('http') ? path : getIconUrl(path);
        fetchSvg(url).catch(() => {
            /* Ignore preload errors */
        });
    });
}

/**
 * Clears the SVG cache (useful for testing or forced refresh).
 */
export function clearIconCache() {
    svgCache.clear();
    pendingRequests.clear();
}

/**
 * Helper hook to get themed icon colors.
 *
 * @returns {Object} Object with primary, secondary, light, and bg colors
 */
export function useIconColors() {
    const [colors, setColors] = useState(() => {
        const settings = window.irpSettings?.settings || {};
        return generateColorVariations(settings.primaryColor || '#428dff');
    });

    useEffect(() => {
        const settings = window.irpSettings?.settings || {};
        setColors(generateColorVariations(settings.primaryColor || '#428dff'));
    }, []);

    return colors;
}
