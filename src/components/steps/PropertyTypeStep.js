/**
 * Property Type Selection Step
 */

import { __ } from '@wordpress/i18n';
import { motion } from 'framer-motion';

// Get plugin URL from WordPress localized settings
const pluginUrl = window.irpSettings?.pluginUrl || '';

const PROPERTY_TYPES = [
    {
        id: 'apartment',
        icon: (
            <img
                src={`${pluginUrl}assets/icon/immobilientyp/wohnung.svg`}
                alt="Wohnung"
                className="irp-type-icon-img"
            />
        ),
        label: __('Wohnung', 'immobilien-rechner-pro'),
        description: __('Wohnung in einem Mehrfamilienhaus', 'immobilien-rechner-pro'),
    },
    {
        id: 'house',
        icon: (
            <img
                src={`${pluginUrl}assets/icon/immobilientyp/haus.svg`}
                alt="Haus"
                className="irp-type-icon-img"
            />
        ),
        label: __('Haus', 'immobilien-rechner-pro'),
        description: __('Einfamilienhaus oder Doppelhaushälfte', 'immobilien-rechner-pro'),
    },
    {
        id: 'commercial',
        icon: (
            <img
                src={`${pluginUrl}assets/icon/immobilientyp/gewerbe.svg`}
                alt="Gewerbe"
                className="irp-type-icon-img"
            />
        ),
        label: __('Gewerbe', 'immobilien-rechner-pro'),
        description: __('Büro, Einzelhandel oder Mischnutzung', 'immobilien-rechner-pro'),
    },
];

export default function PropertyTypeStep({ data, onChange }) {
    const handleSelect = (typeId) => {
        onChange({ property_type: typeId });
    };
    
    return (
        <div className="irp-property-type-step">
            <h3>{__('Welche Art von Immobilie haben Sie?', 'immobilien-rechner-pro')}</h3>
            
            <div className="irp-type-grid">
                {PROPERTY_TYPES.map((type) => (
                    <motion.button
                        key={type.id}
                        type="button"
                        className={`irp-type-card ${data.property_type === type.id ? 'is-selected' : ''}`}
                        onClick={() => handleSelect(type.id)}
                        whileHover={{ scale: 1.02 }}
                        whileTap={{ scale: 0.98 }}
                    >
                        <div className="irp-type-icon">
                            {type.icon}
                        </div>
                        <span className="irp-type-label">{type.label}</span>
                        <span className="irp-type-description">{type.description}</span>
                    </motion.button>
                ))}
            </div>
        </div>
    );
}
