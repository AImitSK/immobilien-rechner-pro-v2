/**
 * Sale Value Calculator - Features Step
 * Combines exterior and interior features on one page
 */

import { __ } from '@wordpress/i18n';
import { motion } from 'framer-motion';
import { CheckIcon } from '@heroicons/react/24/solid';
import Icon from '../Icon';

const EXTERIOR_FEATURES = [
    { id: 'balcony', label: __('Balkon', 'immobilien-rechner-pro'), iconPath: 'assets/icon/ausstattung/balkon.svg' },
    { id: 'terrace', label: __('Terrasse', 'immobilien-rechner-pro'), iconPath: 'assets/icon/ausstattung/terrasse.svg' },
    { id: 'garden', label: __('Garten', 'immobilien-rechner-pro'), iconPath: 'assets/icon/ausstattung/garten.svg' },
    { id: 'garage', label: __('Garage', 'immobilien-rechner-pro'), iconPath: 'assets/icon/ausstattung/garage.svg' },
    { id: 'parking', label: __('Stellplatz', 'immobilien-rechner-pro'), iconPath: 'assets/icon/ausstattung/stellplatz.svg' },
    { id: 'solar', label: __('Solaranlage', 'immobilien-rechner-pro'), iconPath: 'assets/icon/ausstattung/solaranlage.svg' },
];

const INTERIOR_FEATURES = [
    { id: 'fitted_kitchen', label: __('Einbauküche', 'immobilien-rechner-pro'), iconPath: 'assets/icon/ausstattung/kueche.svg' },
    { id: 'elevator', label: __('Aufzug', 'immobilien-rechner-pro'), iconPath: 'assets/icon/ausstattung/aufzug.svg' },
    { id: 'cellar', label: __('Keller', 'immobilien-rechner-pro'), iconPath: 'assets/icon/ausstattung/keller.svg' },
    { id: 'attic', label: __('Dachboden', 'immobilien-rechner-pro'), iconPath: 'assets/icon/ausstattung/dachboden.svg' },
    { id: 'fireplace', label: __('Kamin', 'immobilien-rechner-pro'), iconPath: 'assets/icon/ausstattung/kamin.svg' },
    { id: 'parquet', label: __('Parkettboden', 'immobilien-rechner-pro'), iconPath: 'assets/icon/ausstattung/parkettboden.svg' },
];

export default function SaleFeaturesStep({ data, onChange }) {
    const propertyType = data.property_type || 'house';
    const isLand = propertyType === 'land';

    // For land, features are not applicable
    if (isLand) {
        return (
            <div className="irp-sale-features-step">
                <h3>{__('Ausstattungsmerkmale', 'immobilien-rechner-pro')}</h3>
                <p className="irp-step-description irp-info-message">
                    {__('Für unbebaute Grundstücke werden keine Ausstattungsmerkmale erfasst.', 'immobilien-rechner-pro')}
                </p>
            </div>
        );
    }

    const toggleFeature = (featureId) => {
        const currentFeatures = data.features || [];
        const newFeatures = currentFeatures.includes(featureId)
            ? currentFeatures.filter((f) => f !== featureId)
            : [...currentFeatures, featureId];

        onChange({ features: newFeatures });
    };

    const selectedFeatures = data.features || [];

    const renderFeatureGrid = (features, title) => (
        <div className="irp-feature-section">
            <h4>{title}</h4>
            <div className="irp-features-grid">
                {features.map((feature) => {
                    const isSelected = selectedFeatures.includes(feature.id);

                    return (
                        <motion.button
                            key={feature.id}
                            type="button"
                            className={`irp-feature-chip ${isSelected ? 'is-selected' : ''}`}
                            onClick={() => toggleFeature(feature.id)}
                            whileHover={{ scale: 1.05 }}
                            whileTap={{ scale: 0.95 }}
                        >
                            <span className="irp-feature-icon">
                                <Icon path={feature.iconPath} size={32} />
                            </span>
                            <span className="irp-feature-label">{feature.label}</span>
                            {isSelected && (
                                <span className="irp-feature-check">
                                    <CheckIcon className="irp-heroicon-xs" />
                                </span>
                            )}
                        </motion.button>
                    );
                })}
            </div>
        </div>
    );

    return (
        <div className="irp-sale-features-step">
            <h3>{__('Welche Ausstattung hat Ihre Immobilie?', 'immobilien-rechner-pro')}</h3>
            <p className="irp-step-description">
                {__('Wählen Sie alle zutreffenden Merkmale aus. Diese erhöhen den geschätzten Verkaufswert.', 'immobilien-rechner-pro')}
            </p>

            {renderFeatureGrid(EXTERIOR_FEATURES, __('Außenbereich', 'immobilien-rechner-pro'))}
            {renderFeatureGrid(INTERIOR_FEATURES, __('Innenbereich', 'immobilien-rechner-pro'))}

            <div className="irp-features-summary">
                {selectedFeatures.length === 0 ? (
                    <p className="irp-no-features">
                        {__('Keine Ausstattung ausgewählt', 'immobilien-rechner-pro')}
                    </p>
                ) : (
                    <p className="irp-selected-count">
                        {selectedFeatures.length} {selectedFeatures.length === 1
                            ? __('Merkmal ausgewählt', 'immobilien-rechner-pro')
                            : __('Merkmale ausgewählt', 'immobilien-rechner-pro')
                        }
                    </p>
                )}
            </div>
        </div>
    );
}
