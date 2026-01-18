/**
 * Sale Value Calculator - Size & Build Year Step
 * Combines land size, living space, build year, and modernization
 */

import { __ } from '@wordpress/i18n';
import { motion } from 'framer-motion';
import Icon from '../Icon';

const MODERNIZATION_OPTIONS = [
    {
        id: '1-3_years',
        label: __('Vor 1-3 Jahren', 'immobilien-rechner-pro'),
        description: __('Kernsanierung / umfassende Modernisierung', 'immobilien-rechner-pro'),
    },
    {
        id: '4-9_years',
        label: __('Vor 4-9 Jahren', 'immobilien-rechner-pro'),
        description: __('Größere Modernisierungsmaßnahmen', 'immobilien-rechner-pro'),
    },
    {
        id: '10-15_years',
        label: __('Vor 10-15 Jahren', 'immobilien-rechner-pro'),
        description: __('Mittlere Modernisierung', 'immobilien-rechner-pro'),
    },
    {
        id: 'over_15_years',
        label: __('Vor mehr als 15 Jahren', 'immobilien-rechner-pro'),
        description: __('Ältere Modernisierung', 'immobilien-rechner-pro'),
    },
    {
        id: 'never',
        label: __('Noch nie', 'immobilien-rechner-pro'),
        description: __('Originalzustand', 'immobilien-rechner-pro'),
    },
];

export default function SaleSizeStep({ data, onChange }) {
    const propertyType = data.property_type || 'house';
    const isLand = propertyType === 'land';
    const isApartment = propertyType === 'apartment';
    const isHouse = propertyType === 'house';

    // Show fields based on property type
    const showLandSize = isLand || isHouse;
    const showLivingSpace = isApartment || isHouse;
    const showBuildYear = isApartment || isHouse;
    const showModernization = isApartment || isHouse;

    const handleInputChange = (field, value) => {
        onChange({ [field]: value });
    };

    const handleModernizationSelect = (id) => {
        onChange({ modernization: id });
    };

    const currentYear = new Date().getFullYear();

    return (
        <div className="irp-sale-size-step">
            <h3>
                {isLand
                    ? __('Wie groß ist Ihr Grundstück?', 'immobilien-rechner-pro')
                    : __('Angaben zu Größe und Baujahr', 'immobilien-rechner-pro')
                }
            </h3>

            <div className="irp-size-inputs">
                {showLandSize && (
                    <div className="irp-input-group">
                        <label htmlFor="land_size">
                            {__('Grundstücksfläche', 'immobilien-rechner-pro')}
                        </label>
                        <div className="irp-input-with-unit">
                            <input
                                type="number"
                                id="land_size"
                                value={data.land_size || ''}
                                onChange={(e) => handleInputChange('land_size', e.target.value)}
                                placeholder={isLand ? '500' : '400'}
                                min="50"
                                max="50000"
                                step="10"
                            />
                            <span className="irp-input-unit">m²</span>
                        </div>
                        <p className="irp-input-hint">
                            {__('Die Größe des Grundstücks lt. Grundbuch', 'immobilien-rechner-pro')}
                        </p>
                    </div>
                )}

                {showLivingSpace && (
                    <div className="irp-input-group">
                        <label htmlFor="living_space">
                            {__('Wohnfläche', 'immobilien-rechner-pro')}
                        </label>
                        <div className="irp-input-with-unit">
                            <input
                                type="number"
                                id="living_space"
                                value={data.living_space || data.property_size || ''}
                                onChange={(e) => {
                                    handleInputChange('living_space', e.target.value);
                                    handleInputChange('property_size', e.target.value);
                                }}
                                placeholder={isApartment ? '85' : '140'}
                                min="20"
                                max="2000"
                                step="5"
                            />
                            <span className="irp-input-unit">m²</span>
                        </div>
                        <p className="irp-input-hint">
                            {__('Die Wohnfläche gemäß Wohnflächenberechnung', 'immobilien-rechner-pro')}
                        </p>
                    </div>
                )}

                {showBuildYear && (
                    <div className="irp-input-group">
                        <label htmlFor="build_year">
                            {__('Baujahr', 'immobilien-rechner-pro')}
                        </label>
                        <div className="irp-input-with-unit">
                            <input
                                type="number"
                                id="build_year"
                                value={data.build_year || ''}
                                onChange={(e) => handleInputChange('build_year', e.target.value)}
                                placeholder="1990"
                                min="1800"
                                max={currentYear}
                                step="1"
                            />
                        </div>
                        <p className="irp-input-hint">
                            {__('Das Jahr der ursprünglichen Fertigstellung', 'immobilien-rechner-pro')}
                        </p>
                    </div>
                )}
            </div>

            {showModernization && (
                <div className="irp-modernization-section">
                    <h4>{__('Wann wurde zuletzt umfassend modernisiert?', 'immobilien-rechner-pro')}</h4>
                    <p className="irp-step-description">
                        {__('Eine umfassende Modernisierung wirkt sich positiv auf den Wert aus.', 'immobilien-rechner-pro')}
                    </p>

                    <div className="irp-modernization-grid">
                        {MODERNIZATION_OPTIONS.map((option) => (
                            <motion.button
                                key={option.id}
                                type="button"
                                className={`irp-modernization-card ${data.modernization === option.id ? 'is-selected' : ''}`}
                                onClick={() => handleModernizationSelect(option.id)}
                                whileHover={{ scale: 1.02 }}
                                whileTap={{ scale: 0.98 }}
                            >
                                <Icon path="assets/icon/modernisierung/modernisierung.svg" size={32} className="irp-modernization-icon" />
                                <span className="irp-modernization-label">{option.label}</span>
                                <span className="irp-modernization-description">{option.description}</span>
                            </motion.button>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
}
