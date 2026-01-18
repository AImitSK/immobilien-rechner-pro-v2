/**
 * Property Condition Step
 */

import { __ } from '@wordpress/i18n';
import { motion } from 'framer-motion';

// Get plugin URL from WordPress localized settings
const pluginUrl = window.irpSettings?.pluginUrl || '';

const CONDITIONS = [
    {
        id: 'new',
        label: __('Neubau / Kernsaniert', 'immobilien-rechner-pro'),
        description: __('Neu gebaut oder vollständig saniert', 'immobilien-rechner-pro'),
        icon: `${pluginUrl}assets/icon/zustand/neubau.svg`,
    },
    {
        id: 'renovated',
        label: __('Kürzlich renoviert', 'immobilien-rechner-pro'),
        description: __('In den letzten 5 Jahren modernisiert', 'immobilien-rechner-pro'),
        icon: `${pluginUrl}assets/icon/zustand/renoviert.svg`,
    },
    {
        id: 'good',
        label: __('Guter Zustand', 'immobilien-rechner-pro'),
        description: __('Gut gepflegt, bezugsfertig', 'immobilien-rechner-pro'),
        icon: `${pluginUrl}assets/icon/zustand/gut.svg`,
    },
    {
        id: 'needs_renovation',
        label: __('Renovierungsbedürftig', 'immobilien-rechner-pro'),
        description: __('Erfordert Modernisierung oder Reparaturen', 'immobilien-rechner-pro'),
        icon: `${pluginUrl}assets/icon/zustand/reparaturen.svg`,
    },
];

export default function ConditionStep({ data, onChange }) {
    const handleSelect = (conditionId) => {
        onChange({ condition: conditionId });
    };

    return (
        <div className="irp-condition-step">
            <h3>{__('In welchem Zustand ist Ihre Immobilie?', 'immobilien-rechner-pro')}</h3>

            <div className="irp-condition-grid">
                {CONDITIONS.map((condition) => (
                    <motion.button
                        key={condition.id}
                        type="button"
                        className={`irp-condition-card ${data.condition === condition.id ? 'is-selected' : ''}`}
                        onClick={() => handleSelect(condition.id)}
                        whileHover={{ scale: 1.02 }}
                        whileTap={{ scale: 0.98 }}
                    >
                        <span className="irp-condition-icon">
                            <img
                                src={condition.icon}
                                alt={condition.label}
                                className="irp-condition-icon-img"
                            />
                        </span>
                        <span className="irp-condition-label">{condition.label}</span>
                        <span className="irp-condition-description">{condition.description}</span>
                    </motion.button>
                ))}
            </div>
        </div>
    );
}
