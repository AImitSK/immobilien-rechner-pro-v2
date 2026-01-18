/**
 * Sale Value Calculator - Purpose Step
 * Combines usage type, sale intention, and timeframe
 */

import { __ } from '@wordpress/i18n';
import { motion } from 'framer-motion';

// Get plugin URL from WordPress localized settings
const pluginUrl = window.irpSettings?.pluginUrl || '';

const USAGE_TYPES = [
    {
        id: 'owner_occupied',
        icon: `${pluginUrl}assets/icon/nutzung/selbstgenutzt.svg`,
        label: __('Selbstgenutzt', 'immobilien-rechner-pro'),
        description: __('Sie wohnen selbst in der Immobilie', 'immobilien-rechner-pro'),
    },
    {
        id: 'rented',
        icon: `${pluginUrl}assets/icon/nutzung/vermietet.svg`,
        label: __('Vermietet', 'immobilien-rechner-pro'),
        description: __('Die Immobilie ist aktuell vermietet', 'immobilien-rechner-pro'),
    },
    {
        id: 'vacant',
        icon: `${pluginUrl}assets/icon/nutzung/leerstand.svg`,
        label: __('Leerstand', 'immobilien-rechner-pro'),
        description: __('Die Immobilie steht derzeit leer', 'immobilien-rechner-pro'),
    },
];

const SALE_INTENTIONS = [
    {
        id: 'sell',
        icon: `${pluginUrl}assets/icon/nutzung/verkaufen.svg`,
        label: __('Verkaufen', 'immobilien-rechner-pro'),
        description: __('Ich möchte die Immobilie verkaufen', 'immobilien-rechner-pro'),
    },
    {
        id: 'buy',
        icon: `${pluginUrl}assets/icon/nutzung/kaufen.svg`,
        label: __('Kaufen', 'immobilien-rechner-pro'),
        description: __('Ich möchte eine Immobilie kaufen', 'immobilien-rechner-pro'),
    },
];

const TIMEFRAMES = [
    {
        id: 'immediately',
        label: __('Sofort', 'immobilien-rechner-pro'),
        description: __('So schnell wie möglich', 'immobilien-rechner-pro'),
    },
    {
        id: '3_months',
        label: __('In 3 Monaten', 'immobilien-rechner-pro'),
        description: __('Kurzfristig', 'immobilien-rechner-pro'),
    },
    {
        id: '6_months',
        label: __('In 6 Monaten', 'immobilien-rechner-pro'),
        description: __('Mittelfristig', 'immobilien-rechner-pro'),
    },
    {
        id: '12_months',
        label: __('In 12 Monaten', 'immobilien-rechner-pro'),
        description: __('Langfristig', 'immobilien-rechner-pro'),
    },
    {
        id: 'undecided',
        label: __('Noch offen', 'immobilien-rechner-pro'),
        description: __('Kein fester Zeitplan', 'immobilien-rechner-pro'),
    },
];

export default function SalePurposeStep({ data, onChange }) {
    const propertyType = data.property_type || 'house';
    const isLand = propertyType === 'land';

    const handleUsageSelect = (usageId) => {
        onChange({ usage_type: usageId });
    };

    const handleIntentionSelect = (intentionId) => {
        onChange({ sale_intention: intentionId });
    };

    const handleTimeframeSelect = (timeframeId) => {
        onChange({ timeframe: timeframeId });
    };

    return (
        <div className="irp-sale-purpose-step">
            {/* Usage Type - only for buildings */}
            {!isLand && (
                <div className="irp-usage-section">
                    <h3>{__('Wie wird die Immobilie aktuell genutzt?', 'immobilien-rechner-pro')}</h3>

                    <div className="irp-usage-grid">
                        {USAGE_TYPES.map((usage) => (
                            <motion.button
                                key={usage.id}
                                type="button"
                                className={`irp-usage-card ${data.usage_type === usage.id ? 'is-selected' : ''}`}
                                onClick={() => handleUsageSelect(usage.id)}
                                whileHover={{ scale: 1.02 }}
                                whileTap={{ scale: 0.98 }}
                            >
                                <div className="irp-usage-icon">
                                    <img
                                        src={usage.icon}
                                        alt={usage.label}
                                        className="irp-usage-icon-img"
                                    />
                                </div>
                                <span className="irp-usage-label">{usage.label}</span>
                                <span className="irp-usage-description">{usage.description}</span>
                            </motion.button>
                        ))}
                    </div>
                </div>
            )}

            {/* Sale Intention */}
            <div className="irp-intention-section">
                <h4>
                    {isLand
                        ? __('Was ist Ihr Ziel?', 'immobilien-rechner-pro')
                        : __('Was möchten Sie tun?', 'immobilien-rechner-pro')
                    }
                </h4>

                <div className="irp-intention-grid">
                    {SALE_INTENTIONS.map((intention) => (
                        <motion.button
                            key={intention.id}
                            type="button"
                            className={`irp-intention-card ${data.sale_intention === intention.id ? 'is-selected' : ''}`}
                            onClick={() => handleIntentionSelect(intention.id)}
                            whileHover={{ scale: 1.02 }}
                            whileTap={{ scale: 0.98 }}
                        >
                            <div className="irp-intention-icon">
                                <img
                                    src={intention.icon}
                                    alt={intention.label}
                                    className="irp-intention-icon-img"
                                />
                            </div>
                            <span className="irp-intention-label">{intention.label}</span>
                            <span className="irp-intention-description">{intention.description}</span>
                        </motion.button>
                    ))}
                </div>
            </div>

            {/* Timeframe */}
            <div className="irp-timeframe-section">
                <h4>{__('Wann möchten Sie aktiv werden?', 'immobilien-rechner-pro')}</h4>

                <div className="irp-timeframe-grid">
                    {TIMEFRAMES.map((timeframe) => (
                        <motion.button
                            key={timeframe.id}
                            type="button"
                            className={`irp-timeframe-card ${data.timeframe === timeframe.id ? 'is-selected' : ''}`}
                            onClick={() => handleTimeframeSelect(timeframe.id)}
                            whileHover={{ scale: 1.02 }}
                            whileTap={{ scale: 0.98 }}
                        >
                            <img
                                src={`${pluginUrl}assets/icon/zeitrahmen/zeitrahmen.svg`}
                                alt=""
                                className="irp-timeframe-icon"
                            />
                            <span className="irp-timeframe-label">{timeframe.label}</span>
                            <span className="irp-timeframe-description">{timeframe.description}</span>
                        </motion.button>
                    ))}
                </div>
            </div>
        </div>
    );
}
