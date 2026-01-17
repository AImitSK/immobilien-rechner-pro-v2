/**
 * Results Display Component
 * Using ApexCharts for beautiful visualizations
 */

import { useMemo, useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { motion } from 'framer-motion';
import Chart from 'react-apexcharts';

import RentalGauge from './RentalGauge';
import { trackCompleteLead } from '../utils/tracking';

export default function ResultsDisplay({
    mode,
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
                mode: mode,
                city: formData?.city_name || '',
                propertyType: formData?.property_type || '',
                leadId: leadId,
            });
        }
    }, [mode, formData, leadId, showBrokerNotice]);

    const formatCurrency = (value) => {
        return new Intl.NumberFormat('de-DE', {
            style: 'currency',
            currency: 'EUR',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(value);
    };

    const formatCurrencyShort = (value) => {
        if (value >= 1000000) {
            return `${(value / 1000000).toFixed(1)}M €`;
        }
        if (value >= 1000) {
            return `${(value / 1000).toFixed(0)}k €`;
        }
        return `${value} €`;
    };

    if (mode === 'rental') {
        return (
            <RentalResults
                formData={formData}
                results={results}
                formatCurrency={formatCurrency}
                onStartOver={onStartOver}
                showBrokerNotice={showBrokerNotice}
                companyName={settings.companyName}
            />
        );
    }

    return (
        <ComparisonResults
            formData={formData}
            results={results}
            formatCurrency={formatCurrency}
            formatCurrencyShort={formatCurrencyShort}
            onStartOver={onStartOver}
            showBrokerNotice={showBrokerNotice}
            companyName={settings.companyName}
        />
    );
}

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
                        __('Ihre Daten werden jetzt von einem erfahrenen Immobilienexperten bei %s analysiert. Sie erhalten in Kürze eine ausführliche Bewertung mit detaillierten Marktdaten direkt in Ihr E-Mail-Postfach.', 'immobilien-rechner-pro').replace('%s', companyName)
                    ) : (
                        __('Ihre Daten werden jetzt von einem erfahrenen Immobilienexperten analysiert. Sie erhalten in Kürze eine ausführliche Bewertung mit detaillierten Marktdaten direkt in Ihr E-Mail-Postfach.', 'immobilien-rechner-pro')
                    )}
                </p>
            </div>
        </motion.div>
    );
}

function RentalResults({
    formData,
    results,
    formatCurrency,
    onStartOver,
    showBrokerNotice,
    companyName,
}) {
    const { monthly_rent, price_per_sqm, market_position } = results;

    return (
        <div className="irp-results irp-results-rental">
            <motion.div
                className="irp-results-header"
                initial={{ opacity: 0, y: -20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.5 }}
            >
                <h2>{__('Ihre Mietwert-Schätzung', 'immobilien-rechner-pro')}</h2>
                <p>
                    {__('Basierend auf Ihren Immobiliendaten und dem Standort', 'immobilien-rechner-pro')}
                </p>
            </motion.div>

            <div className="irp-results-main">
                <motion.div
                    className="irp-result-card irp-result-primary"
                    initial={{ opacity: 0, scale: 0.9 }}
                    animate={{ opacity: 1, scale: 1 }}
                    transition={{ duration: 0.5, delay: 0.2 }}
                >
                    <span className="irp-result-label">
                        {__('Geschätzte Monatsmiete', 'immobilien-rechner-pro')}
                    </span>
                    <span className="irp-result-value">
                        {formatCurrency(monthly_rent.estimate)}
                    </span>
                    <span className="irp-result-range">
                        {__('Spanne:', 'immobilien-rechner-pro')} {formatCurrency(monthly_rent.low)} – {formatCurrency(monthly_rent.high)}
                    </span>
                </motion.div>

                <div className="irp-result-secondary-grid">
                    <motion.div
                        className="irp-result-card"
                        initial={{ opacity: 0, x: -20 }}
                        animate={{ opacity: 1, x: 0 }}
                        transition={{ duration: 0.5, delay: 0.4 }}
                    >
                        <span className="irp-result-label">
                            {__('Preis pro m²', 'immobilien-rechner-pro')}
                        </span>
                        <span className="irp-result-value-small">
                            {formatCurrency(price_per_sqm)}
                        </span>
                    </motion.div>

                    <motion.div
                        className="irp-result-card"
                        initial={{ opacity: 0, x: 20 }}
                        animate={{ opacity: 1, x: 0 }}
                        transition={{ duration: 0.5, delay: 0.4 }}
                    >
                        <span className="irp-result-label">
                            {__('Jährliche Mieteinnahmen', 'immobilien-rechner-pro')}
                        </span>
                        <span className="irp-result-value-small">
                            {formatCurrency(results.annual_rent)}
                        </span>
                    </motion.div>
                </div>
            </div>

            <motion.div
                className="irp-market-position"
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                transition={{ duration: 0.5, delay: 0.6 }}
            >
                <h3>{__('Marktposition', 'immobilien-rechner-pro')}</h3>
                <RentalGauge percentile={market_position.percentile} />
                <p className="irp-market-label">{market_position.label}</p>
            </motion.div>

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
        </div>
    );
}

function ComparisonResults({
    formData,
    results,
    formatCurrency,
    formatCurrencyShort,
    onStartOver,
    showBrokerNotice,
    companyName,
}) {
    const { rental, sale, rental_scenario, yields, break_even_year, projection, recommendation } = results;

    // ApexCharts options for the projection chart
    const chartOptions = useMemo(() => ({
        chart: {
            type: 'area',
            height: 350,
            fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
            toolbar: {
                show: false
            },
            zoom: {
                enabled: false
            },
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 800,
                animateGradually: {
                    enabled: true,
                    delay: 150
                }
            },
            dropShadow: {
                enabled: true,
                top: 3,
                left: 0,
                blur: 4,
                opacity: 0.1
            }
        },
        colors: ['#2563eb', '#ea580c'],
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.45,
                opacityTo: 0.05,
                stops: [0, 100]
            }
        },
        legend: {
            position: 'top',
            horizontalAlign: 'left',
            fontSize: '14px',
            fontWeight: 500,
            markers: {
                width: 12,
                height: 12,
                radius: 12
            },
            itemMargin: {
                horizontal: 20
            }
        },
        xaxis: {
            categories: projection.map(p => `${p.year}J`),
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            },
            labels: {
                style: {
                    colors: '#6b7280',
                    fontSize: '12px'
                }
            }
        },
        yaxis: {
            labels: {
                formatter: (value) => formatCurrencyShort(value),
                style: {
                    colors: '#6b7280',
                    fontSize: '12px'
                }
            }
        },
        grid: {
            borderColor: '#e5e7eb',
            strokeDashArray: 4,
            padding: {
                left: 10,
                right: 10
            }
        },
        tooltip: {
            shared: true,
            intersect: false,
            theme: 'light',
            y: {
                formatter: (value) => formatCurrency(value)
            },
            style: {
                fontSize: '13px'
            }
        },
        annotations: break_even_year ? {
            xaxis: [{
                x: `${break_even_year}J`,
                borderColor: '#16a34a',
                strokeDashArray: 5,
                label: {
                    borderColor: '#16a34a',
                    style: {
                        color: '#fff',
                        background: '#16a34a',
                        fontSize: '12px',
                        fontWeight: 600,
                        padding: {
                            left: 10,
                            right: 10,
                            top: 5,
                            bottom: 5
                        }
                    },
                    text: 'Break-even'
                }
            }]
        } : {}
    }), [projection, break_even_year, formatCurrency, formatCurrencyShort]);

    const chartSeries = useMemo(() => [
        {
            name: __('Kumulierte Mieteinnahmen', 'immobilien-rechner-pro'),
            data: projection.map(p => p.cumulative_rental_income)
        },
        {
            name: __('Verkaufserlös (wenn in dem Jahr verkauft)', 'immobilien-rechner-pro'),
            data: projection.map(p => p.net_sale_proceeds)
        }
    ], [projection]);

    const getRecommendationColor = () => {
        switch (recommendation.direction) {
            case 'rent':
                return 'var(--irp-success)';
            case 'sell':
                return 'var(--irp-warning)';
            default:
                return 'var(--irp-primary)';
        }
    };

    return (
        <div className="irp-results irp-results-comparison">
            <motion.div
                className="irp-results-header"
                initial={{ opacity: 0, y: -20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.5 }}
            >
                <h2>{__('Verkaufen vs. Vermieten Vergleich', 'immobilien-rechner-pro')}</h2>
            </motion.div>

            <div className="irp-comparison-cards">
                <motion.div
                    className="irp-comparison-card irp-card-sell"
                    initial={{ opacity: 0, x: -30 }}
                    animate={{ opacity: 1, x: 0 }}
                    transition={{ duration: 0.5, delay: 0.2 }}
                >
                    <h3>{__('Wenn Sie jetzt verkaufen', 'immobilien-rechner-pro')}</h3>
                    <div className="irp-comparison-value">
                        {formatCurrency(sale.net_proceeds)}
                    </div>
                    <span className="irp-comparison-label">{__('Nettoerlös', 'immobilien-rechner-pro')}</span>
                    <ul className="irp-comparison-details">
                        <li>
                            <span>{__('Immobilienwert', 'immobilien-rechner-pro')}</span>
                            <span>{formatCurrency(sale.property_value)}</span>
                        </li>
                        <li>
                            <span>{__('Verkaufskosten', 'immobilien-rechner-pro')}</span>
                            <span>-{formatCurrency(sale.sale_costs)}</span>
                        </li>
                        {sale.remaining_mortgage > 0 && (
                            <li>
                                <span>{__('Hypothekenablösung', 'immobilien-rechner-pro')}</span>
                                <span>-{formatCurrency(sale.remaining_mortgage)}</span>
                            </li>
                        )}
                    </ul>
                </motion.div>

                <motion.div
                    className="irp-comparison-card irp-card-rent"
                    initial={{ opacity: 0, x: 30 }}
                    animate={{ opacity: 1, x: 0 }}
                    transition={{ duration: 0.5, delay: 0.2 }}
                >
                    <h3>{__('Wenn Sie vermieten', 'immobilien-rechner-pro')}</h3>
                    <div className="irp-comparison-value">
                        {formatCurrency(rental_scenario.net_annual_income)}
                        <span className="irp-per-year">/{__('Jahr', 'immobilien-rechner-pro')}</span>
                    </div>
                    <span className="irp-comparison-label">{__('Netto-Mieteinnahmen', 'immobilien-rechner-pro')}</span>
                    <ul className="irp-comparison-details">
                        <li>
                            <span>{__('Bruttomiete', 'immobilien-rechner-pro')}</span>
                            <span>{formatCurrency(rental_scenario.gross_annual_rent)}</span>
                        </li>
                        <li>
                            <span>{__('Leerstandsverlust', 'immobilien-rechner-pro')}</span>
                            <span>-{formatCurrency(rental_scenario.vacancy_loss)}</span>
                        </li>
                        <li>
                            <span>{__('Instandhaltung', 'immobilien-rechner-pro')}</span>
                            <span>-{formatCurrency(rental_scenario.maintenance_cost)}</span>
                        </li>
                    </ul>
                    <div className="irp-yield-badges">
                        <span className="irp-yield-badge">
                            {__('Bruttorendite:', 'immobilien-rechner-pro')} {yields.gross.toFixed(1)}%
                        </span>
                        <span className="irp-yield-badge">
                            {__('Nettorendite:', 'immobilien-rechner-pro')} {yields.net.toFixed(1)}%
                        </span>
                    </div>
                </motion.div>
            </div>

            <motion.div
                className="irp-chart-section"
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                transition={{ duration: 0.5, delay: 0.4 }}
            >
                <h3>{__('15-Jahres-Prognose', 'immobilien-rechner-pro')}</h3>
                <div className="irp-chart-container">
                    <Chart
                        options={chartOptions}
                        series={chartSeries}
                        type="area"
                        height={350}
                    />
                </div>

                {break_even_year && (
                    <p className="irp-breakeven-info">
                        {__('Break-even-Punkt:', 'immobilien-rechner-pro')} <strong>{break_even_year} {__('Jahre', 'immobilien-rechner-pro')}</strong>
                    </p>
                )}
            </motion.div>

            <motion.div
                className="irp-recommendation"
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.5, delay: 0.6 }}
                style={{ borderColor: getRecommendationColor() }}
            >
                <h3>{__('Unsere Einschätzung', 'immobilien-rechner-pro')}</h3>
                <p className="irp-recommendation-summary">{recommendation.summary}</p>
                <ul className="irp-recommendation-factors">
                    {recommendation.factors.map((factor, index) => (
                        <li key={index}>{factor}</li>
                    ))}
                </ul>

                {results.speculation_tax_note && (
                    <p className="irp-tax-notice">
                        {results.speculation_tax_note}
                    </p>
                )}
            </motion.div>

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
        </div>
    );
}
