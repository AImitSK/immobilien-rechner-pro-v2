/**
 * Sale Value Calculator - Property Type Step
 * Combines property type selection with house type (if applicable)
 */

import { __ } from '@wordpress/i18n';
import { motion, AnimatePresence } from 'framer-motion';

// Get plugin URL from WordPress localized settings
const pluginUrl = window.irpSettings?.pluginUrl || '';

const PROPERTY_TYPES = [
    {
        id: 'land',
        icon: `${pluginUrl}assets/icon/immobilientyp/grundstueck.svg`,
        label: __('Grundstück', 'immobilien-rechner-pro'),
        description: __('Unbebautes Grundstück', 'immobilien-rechner-pro'),
    },
    {
        id: 'apartment',
        icon: `${pluginUrl}assets/icon/immobilientyp/wohnung.svg`,
        label: __('Wohnung', 'immobilien-rechner-pro'),
        description: __('Eigentumswohnung in einem Mehrfamilienhaus', 'immobilien-rechner-pro'),
    },
    {
        id: 'house',
        icon: `${pluginUrl}assets/icon/immobilientyp/haus.svg`,
        label: __('Haus', 'immobilien-rechner-pro'),
        description: __('Einfamilienhaus, Reihenhaus oder Mehrfamilienhaus', 'immobilien-rechner-pro'),
    },
];

const HOUSE_TYPES = [
    {
        id: 'single_family',
        icon: `${pluginUrl}assets/icon/haustypen/einfamilienhaus.svg`,
        label: __('Einfamilienhaus', 'immobilien-rechner-pro'),
    },
    {
        id: 'semi_detached',
        icon: `${pluginUrl}assets/icon/haustypen/doppelhaushaelfte.svg`,
        label: __('Doppelhaushälfte', 'immobilien-rechner-pro'),
    },
    {
        id: 'townhouse_end',
        icon: `${pluginUrl}assets/icon/haustypen/endreihenhaus.svg`,
        label: __('Endreihenhaus', 'immobilien-rechner-pro'),
    },
    {
        id: 'townhouse_middle',
        icon: `${pluginUrl}assets/icon/haustypen/mittelreihenhaus.svg`,
        label: __('Mittelreihenhaus', 'immobilien-rechner-pro'),
    },
    {
        id: 'multi_family',
        icon: `${pluginUrl}assets/icon/haustypen/mehrfamilienhaus.svg`,
        label: __('Mehrfamilienhaus', 'immobilien-rechner-pro'),
    },
    {
        id: 'bungalow',
        icon: `${pluginUrl}assets/icon/haustypen/bungalow.svg`,
        label: __('Bungalow', 'immobilien-rechner-pro'),
    },
];

export default function SalePropertyTypeStep({ data, onChange }) {
    const handlePropertyTypeSelect = (typeId) => {
        const updates = { property_type: typeId };

        // Reset house_type if not a house
        if (typeId !== 'house') {
            updates.house_type = null;
        }

        onChange(updates);
    };

    const handleHouseTypeSelect = (houseTypeId) => {
        onChange({ house_type: houseTypeId });
    };

    const showHouseTypes = data.property_type === 'house';

    return (
        <div className="irp-sale-property-type-step">
            <h3>{__('Welche Art von Immobilie möchten Sie bewerten?', 'immobilien-rechner-pro')}</h3>

            <div className="irp-type-grid irp-type-grid-3">
                {PROPERTY_TYPES.map((type) => (
                    <motion.button
                        key={type.id}
                        type="button"
                        className={`irp-type-card ${data.property_type === type.id ? 'is-selected' : ''}`}
                        onClick={() => handlePropertyTypeSelect(type.id)}
                        whileHover={{ scale: 1.02 }}
                        whileTap={{ scale: 0.98 }}
                    >
                        <div className="irp-type-icon">
                            <img
                                src={type.icon}
                                alt={type.label}
                                className="irp-type-icon-img"
                            />
                        </div>
                        <span className="irp-type-label">{type.label}</span>
                        <span className="irp-type-description">{type.description}</span>
                    </motion.button>
                ))}
            </div>

            <AnimatePresence>
                {showHouseTypes && (
                    <motion.div
                        className="irp-house-type-section"
                        initial={{ opacity: 0, height: 0 }}
                        animate={{ opacity: 1, height: 'auto' }}
                        exit={{ opacity: 0, height: 0 }}
                        transition={{ duration: 0.3 }}
                    >
                        <h4>{__('Um welchen Haustyp handelt es sich?', 'immobilien-rechner-pro')}</h4>

                        <div className="irp-type-grid irp-type-grid-3 irp-house-type-grid">
                            {HOUSE_TYPES.map((type) => (
                                <motion.button
                                    key={type.id}
                                    type="button"
                                    className={`irp-type-card irp-type-card-small ${data.house_type === type.id ? 'is-selected' : ''}`}
                                    onClick={() => handleHouseTypeSelect(type.id)}
                                    whileHover={{ scale: 1.02 }}
                                    whileTap={{ scale: 0.98 }}
                                >
                                    <div className="irp-type-icon">
                                        <img
                                            src={type.icon}
                                            alt={type.label}
                                            className="irp-type-icon-img"
                                        />
                                    </div>
                                    <span className="irp-type-label">{type.label}</span>
                                </motion.button>
                            ))}
                        </div>
                    </motion.div>
                )}
            </AnimatePresence>
        </div>
    );
}
