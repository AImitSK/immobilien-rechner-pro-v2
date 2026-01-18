/**
 * Sale Value Calculator - Quality & Location Step
 * Combines quality level and location rating
 */

import { __ } from '@wordpress/i18n';
import { motion } from 'framer-motion';

// Get plugin URL from WordPress localized settings
const pluginUrl = window.irpSettings?.pluginUrl || '';

const QUALITY_LEVELS = [
    {
        id: 'simple',
        icon: `${pluginUrl}assets/icon/qualitaetsstufen/einfach.svg`,
        label: __('Einfach', 'immobilien-rechner-pro'),
        description: __('Einfache Ausstattung, Standardmaterialien', 'immobilien-rechner-pro'),
    },
    {
        id: 'normal',
        icon: `${pluginUrl}assets/icon/qualitaetsstufen/normal.svg`,
        label: __('Normal', 'immobilien-rechner-pro'),
        description: __('Durchschnittliche Ausstattung und Materialien', 'immobilien-rechner-pro'),
    },
    {
        id: 'upscale',
        icon: `${pluginUrl}assets/icon/qualitaetsstufen/gehoben.svg`,
        label: __('Gehoben', 'immobilien-rechner-pro'),
        description: __('Hochwertige Ausstattung und Materialien', 'immobilien-rechner-pro'),
    },
    {
        id: 'luxury',
        icon: `${pluginUrl}assets/icon/qualitaetsstufen/luxurioes.svg`,
        label: __('Luxuriös', 'immobilien-rechner-pro'),
        description: __('Erstklassige Ausstattung, exklusive Materialien', 'immobilien-rechner-pro'),
    },
];

const LOCATION_RATINGS = [
    {
        value: 1,
        label: __('Einfache Lage', 'immobilien-rechner-pro'),
        description: __('Weniger begehrte Wohngegend, einfache Infrastruktur', 'immobilien-rechner-pro'),
    },
    {
        value: 2,
        label: __('Normale Lage', 'immobilien-rechner-pro'),
        description: __('Durchschnittliche Wohnlage mit guter Versorgung', 'immobilien-rechner-pro'),
    },
    {
        value: 3,
        label: __('Gute Lage', 'immobilien-rechner-pro'),
        description: __('Beliebte Wohngegend, gute Anbindung', 'immobilien-rechner-pro'),
    },
    {
        value: 4,
        label: __('Sehr gute Lage', 'immobilien-rechner-pro'),
        description: __('Begehrte Wohngegend, sehr gute Infrastruktur', 'immobilien-rechner-pro'),
    },
    {
        value: 5,
        label: __('Premium-Lage', 'immobilien-rechner-pro'),
        description: __('Erstklassige Wohngegend, exzellente Ausstattung', 'immobilien-rechner-pro'),
    },
];

export default function SaleQualityLocationStep({ data, onChange }) {
    const propertyType = data.property_type || 'house';
    const isLand = propertyType === 'land';

    const handleQualitySelect = (qualityId) => {
        onChange({ quality: qualityId });
    };

    const handleLocationSelect = (rating) => {
        onChange({ location_rating: rating });
    };

    const renderStars = (count) => {
        return '★'.repeat(count) + '☆'.repeat(5 - count);
    };

    return (
        <div className="irp-sale-quality-location-step">
            {/* Quality Section - only for buildings */}
            {!isLand && (
                <div className="irp-quality-section">
                    <h3>{__('Wie würden Sie die Bauqualität einschätzen?', 'immobilien-rechner-pro')}</h3>
                    <p className="irp-step-description">
                        {__('Die Qualität der Bauausführung und verwendeten Materialien.', 'immobilien-rechner-pro')}
                    </p>

                    <div className="irp-quality-grid">
                        {QUALITY_LEVELS.map((level) => (
                            <motion.button
                                key={level.id}
                                type="button"
                                className={`irp-quality-card ${data.quality === level.id ? 'is-selected' : ''}`}
                                onClick={() => handleQualitySelect(level.id)}
                                whileHover={{ scale: 1.02 }}
                                whileTap={{ scale: 0.98 }}
                            >
                                <div className="irp-quality-icon">
                                    <img
                                        src={level.icon}
                                        alt={level.label}
                                        className="irp-quality-icon-img"
                                    />
                                </div>
                                <span className="irp-quality-label">{level.label}</span>
                                <span className="irp-quality-description">{level.description}</span>
                            </motion.button>
                        ))}
                    </div>
                </div>
            )}

            {/* Location Section - for all property types */}
            <div className="irp-location-section">
                <h4>
                    {isLand
                        ? __('Wie bewerten Sie die Lage des Grundstücks?', 'immobilien-rechner-pro')
                        : __('Wie bewerten Sie die Lage der Immobilie?', 'immobilien-rechner-pro')
                    }
                </h4>
                <p className="irp-step-description">
                    {__('Die Lage hat erheblichen Einfluss auf den Verkaufswert.', 'immobilien-rechner-pro')}
                </p>

                <div className="irp-location-ratings">
                    {LOCATION_RATINGS.map((rating) => (
                        <motion.button
                            key={rating.value}
                            type="button"
                            className={`irp-location-rating-card ${data.location_rating === rating.value ? 'is-selected' : ''}`}
                            onClick={() => handleLocationSelect(rating.value)}
                            whileHover={{ scale: 1.01 }}
                            whileTap={{ scale: 0.99 }}
                        >
                            <div className="irp-location-rating-header">
                                <span className="irp-location-stars">{renderStars(rating.value)}</span>
                                <span className="irp-location-label">{rating.label}</span>
                            </div>
                            <span className="irp-location-description">{rating.description}</span>
                        </motion.button>
                    ))}
                </div>
            </div>
        </div>
    );
}
