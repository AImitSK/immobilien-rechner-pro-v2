/**
 * Sale Value Results Display Component
 * Shows different result layouts based on property type:
 * - Apartment: Comparative value method (Vergleichswertverfahren)
 * - House: Asset value method (Sachwertverfahren)
 * - Land: Pure land value (Bodenwert)
 */

import { useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { motion } from 'framer-motion';
import { trackCompleteLead } from '../utils/tracking';
import Icon from './Icon';

export default function SaleResultsDisplay({
    formData,
    results,
    onStartOver,
    showBrokerNotice = false,
    leadId = null,
}) {
    const settings = window.irpSettings?.settings || {};
    const hasTracked = useRef(false);

    // Track complete lead conversion when results are displayed
    useEffect(() => {
        if (!hasTracked.current && showBrokerNotice) {
            hasTracked.current = true;
            trackCompleteLead({
                mode: 'sale_value',
                city: formData?.city_name || formData?.property_location || '',
                propertyType: formData?.property_type || '',
                leadId: leadId,
            });
        }
    }, [formData, leadId, showBrokerNotice]);

    const calculationType = results?.calculation_type || formData?.property_type || 'house';

    // Route to appropriate display component
    switch (calculationType) {
        case 'apartment':
            return (
                <ApartmentResults
                    formData={formData}
                    results={results}
                    onStartOver={onStartOver}
                    showBrokerNotice={showBrokerNotice}
                    companyName={settings.companyName}
                />
            );
        case 'land':
            return (
                <LandResults
                    formData={formData}
                    results={results}
                    onStartOver={onStartOver}
                    showBrokerNotice={showBrokerNotice}
                    companyName={settings.companyName}
                />
            );
        case 'house':
        default:
            return (
                <HouseResults
                    formData={formData}
                    results={results}
                    onStartOver={onStartOver}
                    showBrokerNotice={showBrokerNotice}
                    companyName={settings.companyName}
                />
            );
    }
}

/**
 * Format currency in German locale
 */
function formatCurrency(value, decimals = 0) {
    return new Intl.NumberFormat('de-DE', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    }).format(value);
}

/**
 * Format number with thousand separators
 */
function formatNumber(value) {
    return new Intl.NumberFormat('de-DE').format(value);
}

/**
 * Broker Notice Component
 */
function BrokerNotice({ companyName }) {
    return (
        <motion.div
            className="irp-broker-notice"
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.4 }}
        >
            <div className="irp-broker-notice-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                    <polyline points="22 4 12 14.01 9 11.01" />
                </svg>
            </div>
            <div className="irp-broker-notice-content">
                <strong>{__('Vielen Dank für Ihre Anfrage!', 'immobilien-rechner-pro')}</strong>
                <p>
                    {companyName ? (
                        __('Ein Immobilienexperte von %s wird sich in Kürze mit Ihnen in Verbindung setzen, um eine detaillierte Bewertung Ihrer Immobilie vorzunehmen.', 'immobilien-rechner-pro').replace('%s', companyName)
                    ) : (
                        __('Ein Immobilienexperte wird sich in Kürze mit Ihnen in Verbindung setzen, um eine detaillierte Bewertung Ihrer Immobilie vorzunehmen.', 'immobilien-rechner-pro')
                    )}
                </p>
            </div>
        </motion.div>
    );
}

/**
 * Price Range Display Component
 */
function PriceRangeDisplay({ priceMin, priceMax, priceEstimate, title }) {
    return (
        <motion.div
            className="irp-sale-price-display"
            initial={{ opacity: 0, scale: 0.9 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ duration: 0.5, delay: 0.2 }}
        >
            <span className="irp-sale-price-label">{title}</span>
            <div className="irp-sale-price-range">
                <span className="irp-sale-price-min">{formatCurrency(priceMin)}</span>
                <span className="irp-sale-price-separator">–</span>
                <span className="irp-sale-price-max">{formatCurrency(priceMax)}</span>
            </div>
            <div className="irp-sale-price-estimate">
                <span className="irp-sale-price-estimate-label">{__('Mittelwert:', 'immobilien-rechner-pro')}</span>
                <span className="irp-sale-price-estimate-value">{formatCurrency(priceEstimate)}</span>
            </div>
        </motion.div>
    );
}

/**
 * Factor Display Item
 */
function FactorItem({ label, value, highlight = false }) {
    return (
        <div className={`irp-factor-item ${highlight ? 'irp-factor-highlight' : ''}`}>
            <span className="irp-factor-label">{label}</span>
            <span className="irp-factor-value">{value}</span>
        </div>
    );
}

/**
 * House Results - Sachwertverfahren (Asset Value Method)
 */
function HouseResults({ formData, results, onStartOver, showBrokerNotice, companyName }) {
    const factors = results?.factors || {};
    const breakdown = results?.breakdown || {};
    const city = results?.city || {};

    return (
        <div className="irp-results irp-results-sale irp-results-sale-house">
            <motion.div
                className="irp-results-header"
                initial={{ opacity: 0, y: -20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.5 }}
            >
                <h2>{__('Geschätzter Verkaufswert Ihrer Immobilie', 'immobilien-rechner-pro')}</h2>
                <p className="irp-results-subtitle">
                    {__('Ermittelt nach dem Sachwertverfahren', 'immobilien-rechner-pro')}
                </p>
            </motion.div>

            <PriceRangeDisplay
                priceMin={results?.price_min || 0}
                priceMax={results?.price_max || 0}
                priceEstimate={results?.price_estimate || 0}
                title={__('Geschätzter Verkaufspreis', 'immobilien-rechner-pro')}
            />

            {/* Value Breakdown */}
            <motion.div
                className="irp-sale-breakdown"
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.5, delay: 0.4 }}
            >
                <h3>{__('Wertermittlung', 'immobilien-rechner-pro')}</h3>
                <div className="irp-breakdown-list">
                    <FactorItem
                        label={__('Grundstückswert', 'immobilien-rechner-pro')}
                        value={formatCurrency(results?.land_value || breakdown?.land_value || 0)}
                    />
                    <FactorItem
                        label={__('Gebäudewert', 'immobilien-rechner-pro')}
                        value={formatCurrency(results?.building_value || breakdown?.building_adjusted || 0)}
                    />
                    {(results?.features_value > 0) && (
                        <FactorItem
                            label={__('Ausstattung', 'immobilien-rechner-pro')}
                            value={`+${formatCurrency(results?.features_value || 0)}`}
                        />
                    )}
                    <FactorItem
                        label={__('Marktanpassung', 'immobilien-rechner-pro')}
                        value={`×${(factors?.market || 1).toFixed(2)}`}
                        highlight={true}
                    />
                </div>
            </motion.div>

            {/* Key Metrics */}
            <motion.div
                className="irp-sale-metrics"
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.5, delay: 0.5 }}
            >
                <h3>{__('Kennzahlen', 'immobilien-rechner-pro')}</h3>
                <div className="irp-metrics-grid">
                    {results?.price_per_sqm_living && (
                        <div className="irp-metric-card">
                            <span className="irp-metric-value">{formatCurrency(results.price_per_sqm_living)}</span>
                            <span className="irp-metric-label">{__('pro m² Wohnfläche', 'immobilien-rechner-pro')}</span>
                        </div>
                    )}
                    {results?.price_per_sqm_land && (
                        <div className="irp-metric-card">
                            <span className="irp-metric-value">{formatCurrency(results.price_per_sqm_land)}</span>
                            <span className="irp-metric-label">{__('pro m² Grundstück', 'immobilien-rechner-pro')}</span>
                        </div>
                    )}
                </div>
            </motion.div>

            {/* Applied Factors */}
            <motion.div
                className="irp-sale-factors"
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.5, delay: 0.6 }}
            >
                <h3>{__('Angewandte Faktoren', 'immobilien-rechner-pro')}</h3>
                <div className="irp-factors-grid">
                    {factors?.house_type_name && (
                        <div className="irp-factor-badge">
                            <span className="irp-factor-badge-label">{__('Haustyp', 'immobilien-rechner-pro')}</span>
                            <span className="irp-factor-badge-value">{factors.house_type_name}</span>
                            <span className="irp-factor-badge-multiplier">×{factors.house_type?.toFixed(2)}</span>
                        </div>
                    )}
                    {factors?.quality_name && (
                        <div className="irp-factor-badge">
                            <span className="irp-factor-badge-label">{__('Qualität', 'immobilien-rechner-pro')}</span>
                            <span className="irp-factor-badge-value">{factors.quality_name}</span>
                            <span className="irp-factor-badge-multiplier">×{factors.quality?.toFixed(2)}</span>
                        </div>
                    )}
                    {factors?.location_rating && (
                        <div className="irp-factor-badge">
                            <span className="irp-factor-badge-label">{__('Lage', 'immobilien-rechner-pro')}</span>
                            <span className="irp-factor-badge-value">{'★'.repeat(factors.location_rating)}{'☆'.repeat(5 - factors.location_rating)}</span>
                            <span className="irp-factor-badge-multiplier">×{factors.location?.toFixed(2)}</span>
                        </div>
                    )}
                    {factors?.effective_build_year && (
                        <div className="irp-factor-badge">
                            <span className="irp-factor-badge-label">{__('Fikt. Baujahr', 'immobilien-rechner-pro')}</span>
                            <span className="irp-factor-badge-value">{factors.effective_build_year}</span>
                            <span className="irp-factor-badge-multiplier">×{factors.age?.toFixed(2)}</span>
                        </div>
                    )}
                </div>
            </motion.div>

            {/* Map with Price Marker */}
            <ResultsMap
                formData={formData}
                priceEstimate={results?.price_estimate || 0}
                propertyType="house"
            />

            {showBrokerNotice && <BrokerNotice companyName={companyName} />}

            <motion.div
                className="irp-results-footer"
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.5, delay: 0.8 }}
            >
                <button
                    type="button"
                    className="irp-btn irp-btn-secondary"
                    onClick={onStartOver}
                >
                    {__('Neue Berechnung starten', 'immobilien-rechner-pro')}
                </button>
            </motion.div>

            <Disclaimer />
        </div>
    );
}

/**
 * Apartment Results - Vergleichswertverfahren (Comparative Value Method)
 */
function ApartmentResults({ formData, results, onStartOver, showBrokerNotice, companyName }) {
    const factors = results?.factors || {};
    const breakdown = results?.breakdown || {};
    const city = results?.city || {};

    return (
        <div className="irp-results irp-results-sale irp-results-sale-apartment">
            <motion.div
                className="irp-results-header"
                initial={{ opacity: 0, y: -20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.5 }}
            >
                <h2>{__('Geschätzter Verkaufswert Ihrer Wohnung', 'immobilien-rechner-pro')}</h2>
                <p className="irp-results-subtitle">
                    {__('Ermittelt nach dem Vergleichswertverfahren', 'immobilien-rechner-pro')}
                </p>
            </motion.div>

            <PriceRangeDisplay
                priceMin={results?.price_min || 0}
                priceMax={results?.price_max || 0}
                priceEstimate={results?.price_estimate || 0}
                title={__('Geschätzter Verkaufspreis', 'immobilien-rechner-pro')}
            />

            {/* Calculation Basis */}
            <motion.div
                className="irp-sale-breakdown"
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.5, delay: 0.4 }}
            >
                <h3>{__('Basierend auf', 'immobilien-rechner-pro')}</h3>
                <div className="irp-breakdown-list">
                    <FactorItem
                        label={`${formatNumber(formData?.living_space || formData?.property_size || 0)} m² × ${formatCurrency(results?.base_price_per_sqm || city?.average_price_sqm || 0)}/m²`}
                        value={formatCurrency(breakdown?.base_value || 0)}
                    />
                    {factors?.quality_name && (
                        <FactorItem
                            label={`${__('Qualität:', 'immobilien-rechner-pro')} ${factors.quality_name}`}
                            value={factors.quality > 1 ? `+${Math.round((factors.quality - 1) * 100)}%` : `${Math.round((factors.quality - 1) * 100)}%`}
                        />
                    )}
                    {factors?.location_rating && (
                        <FactorItem
                            label={`${__('Lage:', 'immobilien-rechner-pro')} ${'★'.repeat(factors.location_rating)}${'☆'.repeat(5 - factors.location_rating)}`}
                            value={factors.location > 1 ? `+${Math.round((factors.location - 1) * 100)}%` : `${Math.round((factors.location - 1) * 100)}%`}
                        />
                    )}
                    {(results?.features_value > 0) && (
                        <FactorItem
                            label={__('Ausstattung', 'immobilien-rechner-pro')}
                            value={`+${formatCurrency(results?.features_value || 0)}`}
                        />
                    )}
                </div>
            </motion.div>

            {/* Regional Comparison */}
            <motion.div
                className="irp-sale-comparison"
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.5, delay: 0.5 }}
            >
                <h3>{__('Vergleich Region', 'immobilien-rechner-pro')}</h3>
                <div className="irp-comparison-stats">
                    <div className="irp-comparison-stat">
                        <span className="irp-comparison-stat-label">
                            {__('Ø Preis/m² in', 'immobilien-rechner-pro')} {city?.name || formData?.property_location || __('der Region', 'immobilien-rechner-pro')}
                        </span>
                        <span className="irp-comparison-stat-value">
                            {formatCurrency(city?.average_price_sqm || results?.base_price_per_sqm || 0)}
                        </span>
                    </div>
                    <div className="irp-comparison-stat irp-comparison-stat-highlight">
                        <span className="irp-comparison-stat-label">{__('Ihre Wohnung', 'immobilien-rechner-pro')}</span>
                        <span className="irp-comparison-stat-value">
                            {formatCurrency(results?.price_per_sqm_living || 0)}/m²
                        </span>
                    </div>
                </div>
            </motion.div>

            {/* Map with Price Marker */}
            <ResultsMap
                formData={formData}
                priceEstimate={results?.price_estimate || 0}
                propertyType="apartment"
            />

            {showBrokerNotice && <BrokerNotice companyName={companyName} />}

            <motion.div
                className="irp-results-footer"
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.5, delay: 0.8 }}
            >
                <button
                    type="button"
                    className="irp-btn irp-btn-secondary"
                    onClick={onStartOver}
                >
                    {__('Neue Berechnung starten', 'immobilien-rechner-pro')}
                </button>
            </motion.div>

            <Disclaimer />
        </div>
    );
}

/**
 * Land Results - Bodenwertverfahren (Land Value Method)
 */
function LandResults({ formData, results, onStartOver, showBrokerNotice, companyName }) {
    const factors = results?.factors || {};
    const breakdown = results?.breakdown || {};
    const city = results?.city || {};

    return (
        <div className="irp-results irp-results-sale irp-results-sale-land">
            <motion.div
                className="irp-results-header"
                initial={{ opacity: 0, y: -20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.5 }}
            >
                <h2>{__('Geschätzter Wert Ihres Grundstücks', 'immobilien-rechner-pro')}</h2>
                <p className="irp-results-subtitle">
                    {__('Ermittelt auf Basis des Bodenrichtwerts', 'immobilien-rechner-pro')}
                </p>
            </motion.div>

            <PriceRangeDisplay
                priceMin={results?.price_min || 0}
                priceMax={results?.price_max || 0}
                priceEstimate={results?.price_estimate || 0}
                title={__('Geschätzter Grundstückswert', 'immobilien-rechner-pro')}
            />

            {/* Value Breakdown */}
            <motion.div
                className="irp-sale-breakdown"
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.5, delay: 0.4 }}
            >
                <h3>{__('Wertermittlung', 'immobilien-rechner-pro')}</h3>
                <div className="irp-breakdown-list">
                    <FactorItem
                        label={`${formatNumber(formData?.land_size || 0)} m² × ${formatCurrency(city?.land_price_sqm || 0)}/m²`}
                        value={formatCurrency(breakdown?.land_base || results?.land_value || 0)}
                    />
                    {factors?.location_rating && (
                        <FactorItem
                            label={`${__('Lagefaktor', 'immobilien-rechner-pro')} (${'★'.repeat(factors.location_rating)}${'☆'.repeat(5 - factors.location_rating)})`}
                            value={`×${factors.location?.toFixed(2)}`}
                        />
                    )}
                    <FactorItem
                        label={__('Marktanpassung', 'immobilien-rechner-pro')}
                        value={`×${(factors?.market || 1).toFixed(2)}`}
                        highlight={true}
                    />
                </div>
            </motion.div>

            {/* Key Metric */}
            <motion.div
                className="irp-sale-metrics"
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.5, delay: 0.5 }}
            >
                <h3>{__('Kennzahlen', 'immobilien-rechner-pro')}</h3>
                <div className="irp-metrics-grid">
                    <div className="irp-metric-card irp-metric-card-wide">
                        <span className="irp-metric-value">{formatCurrency(results?.price_per_sqm_land || city?.land_price_sqm || 0)}</span>
                        <span className="irp-metric-label">{__('Bodenrichtwert pro m²', 'immobilien-rechner-pro')}</span>
                    </div>
                </div>
            </motion.div>

            {/* Map with Price Marker */}
            <ResultsMap
                formData={formData}
                priceEstimate={results?.price_estimate || 0}
                propertyType="land"
            />

            {/* Location Info - only show if no map */}
            {city?.name && !formData?.address_lat && (
                <motion.div
                    className="irp-sale-location-info"
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 0.5, delay: 0.6 }}
                >
                    <div className="irp-location-info-card">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" width="20" height="20">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                            <circle cx="12" cy="10" r="3" />
                        </svg>
                        <span>{__('Standort:', 'immobilien-rechner-pro')} {city.name}</span>
                    </div>
                </motion.div>
            )}

            {showBrokerNotice && <BrokerNotice companyName={companyName} />}

            <motion.div
                className="irp-results-footer"
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.5, delay: 0.8 }}
            >
                <button
                    type="button"
                    className="irp-btn irp-btn-secondary"
                    onClick={onStartOver}
                >
                    {__('Neue Berechnung starten', 'immobilien-rechner-pro')}
                </button>
            </motion.div>

            <Disclaimer />
        </div>
    );
}

/**
 * Disclaimer Component
 */
function Disclaimer() {
    return (
        <motion.div
            className="irp-sale-disclaimer"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ duration: 0.5, delay: 1 }}
        >
            <p>
                {__('Hinweis: Diese Schätzung dient nur zur Orientierung und ersetzt keine professionelle Immobilienbewertung. Der tatsächliche Verkaufspreis kann aufgrund individueller Objektmerkmale, aktueller Marktbedingungen und Verhandlungen abweichen.', 'immobilien-rechner-pro')}
            </p>
        </motion.div>
    );
}

/**
 * Results Map Component with Custom Price Marker
 */
function ResultsMap({ formData, priceEstimate, propertyType }) {
    const mapRef = useRef(null);
    const mapInstanceRef = useRef(null);
    const overlayRef = useRef(null);
    const [mapLoaded, setMapLoaded] = useState(false);

    const settings = window.irpSettings?.settings || {};
    const googleMapsApiKey = settings.googleMapsApiKey || window.irpSettings?.googleMapsApiKey || '';
    const showMap = googleMapsApiKey && formData?.address_lat && formData?.address_lng;

    // Get icon path based on property type
    const getIconPath = () => {
        switch (propertyType) {
            case 'apartment':
                return 'assets/icon/immobilientyp/wohnung.svg';
            case 'land':
                return 'assets/icon/immobilientyp/grundstueck.svg';
            case 'house':
            default:
                return 'assets/icon/immobilientyp/haus.svg';
        }
    };

    useEffect(() => {
        if (!showMap || !window.google?.maps) return;

        const initMap = () => {
            if (!mapRef.current || mapInstanceRef.current) return;

            const position = {
                lat: parseFloat(formData.address_lat),
                lng: parseFloat(formData.address_lng),
            };

            mapInstanceRef.current = new window.google.maps.Map(mapRef.current, {
                center: position,
                zoom: 15,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: false,
                zoomControl: true,
            });

            // Create custom overlay for price marker
            class PriceMarkerOverlay extends window.google.maps.OverlayView {
                constructor(position, price, iconPath) {
                    super();
                    this.position = position;
                    this.price = price;
                    this.iconPath = iconPath;
                    this.div = null;
                }

                onAdd() {
                    this.div = document.createElement('div');
                    this.div.className = 'irp-map-price-marker';
                    this.div.innerHTML = `
                        <div class="irp-map-marker-content">
                            <div class="irp-map-marker-icon">
                                <img src="${window.irpSettings?.pluginUrl || ''}${this.iconPath}" alt="" />
                            </div>
                            <div class="irp-map-marker-price">${this.price}</div>
                        </div>
                        <div class="irp-map-marker-arrow"></div>
                    `;

                    const panes = this.getPanes();
                    panes.floatPane.appendChild(this.div);
                }

                draw() {
                    const overlayProjection = this.getProjection();
                    const pos = overlayProjection.fromLatLngToDivPixel(this.position);

                    if (this.div) {
                        // Center the marker horizontally, position above the point
                        this.div.style.left = pos.x + 'px';
                        this.div.style.top = pos.y + 'px';
                    }
                }

                onRemove() {
                    if (this.div) {
                        this.div.parentNode.removeChild(this.div);
                        this.div = null;
                    }
                }
            }

            // Create and add the overlay
            const priceFormatted = formatCurrency(priceEstimate);
            overlayRef.current = new PriceMarkerOverlay(
                new window.google.maps.LatLng(position.lat, position.lng),
                priceFormatted,
                getIconPath()
            );
            overlayRef.current.setMap(mapInstanceRef.current);

            setMapLoaded(true);
        };

        // Small delay to ensure DOM is ready
        setTimeout(initMap, 100);

        return () => {
            if (overlayRef.current) {
                overlayRef.current.setMap(null);
            }
        };
    }, [showMap, formData?.address_lat, formData?.address_lng, priceEstimate, propertyType]);

    if (!showMap) {
        return null;
    }

    return (
        <motion.div
            className="irp-results-map-section"
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5, delay: 0.7 }}
        >
            <h3>{__('Standort', 'immobilien-rechner-pro')}</h3>
            <div className="irp-results-map-container">
                <div ref={mapRef} className="irp-results-google-map" />
            </div>
            {formData?.street_address && (
                <p className="irp-results-address">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" width="16" height="16">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                        <circle cx="12" cy="10" r="3" />
                    </svg>
                    {formData.street_address}
                    {formData.zip_code && `, ${formData.zip_code}`}
                    {formData.property_location && ` ${formData.property_location}`}
                </p>
            )}
        </motion.div>
    );
}
