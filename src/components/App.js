/**
 * Main App Component
 * Lead Magnet Flow: Calculator -> Pending -> Contact Form -> Results
 */

import { useState, useMemo, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { motion, AnimatePresence } from 'framer-motion';
import apiFetch from '@wordpress/api-fetch';

import { irpDebug } from '../utils/debug';
import { trackPartialLead } from '../utils/tracking';
import ModeSelector from './ModeSelector';
import RentalCalculator from './RentalCalculator';
import ComparisonCalculator from './ComparisonCalculator';
import SaleValueCalculator from './SaleValueCalculator';
import ResultsDisplay from './ResultsDisplay';
import SaleResultsDisplay from './SaleResultsDisplay';
import CalculationPendingStep from './steps/CalculationPendingStep';
import ContactFormStep from './steps/ContactFormStep';

const STEPS = {
    MODE_SELECT: 'mode_select',
    CALCULATOR: 'calculator',
    CALCULATION_PENDING: 'calculation_pending',
    CONTACT_FORM: 'contact_form',
    RESULTS: 'results',
};

export default function App({ config }) {
    const { initialMode, theme, showBranding, cityId, cityName } = config;

    // State
    const [currentStep, setCurrentStep] = useState(
        initialMode ? STEPS.CALCULATOR : STEPS.MODE_SELECT
    );
    const [mode, setMode] = useState(initialMode || '');
    const [formData, setFormData] = useState({});
    const [results, setResults] = useState(null);
    const [leadId, setLeadId] = useState(null);
    const [pendingError, setPendingError] = useState(null);

    // Get settings from localized script
    const settings = window.irpSettings?.settings || {};

    // Dynamic styles based on branding
    const brandStyles = useMemo(() => ({
        '--irp-primary': settings.primaryColor || '#2563eb',
        '--irp-secondary': settings.secondaryColor || '#1e40af',
        maxWidth: `${settings.calculatorMaxWidth || 680}px`,
    }), [settings]);

    // Handlers
    const handleModeSelect = (selectedMode) => {
        setMode(selectedMode);
        setCurrentStep(STEPS.CALCULATOR);
    };

    // Called when calculator completes - creates partial lead and shows pending animation
    const handleCalculationComplete = useCallback(async (data, calculationResults) => {
        irpDebug('handleCalculationComplete called');
        setFormData(data);
        setResults(calculationResults);
        setPendingError(null);
        setLeadId(null); // Reset lead ID
        setCurrentStep(STEPS.CALCULATION_PENDING);

        // Create partial lead in background
        try {
            irpDebug('Creating partial lead...');

            // Build partial lead data based on mode
            const partialLeadData = {
                mode: mode,
                property_type: data.property_type,
                city_id: data.city_id || '',
                city_name: data.city_name || '',
                location_rating: data.location_rating || 3,
                features: data.features || [],
                calculation_result: calculationResults,
            };

            // Add mode-specific fields
            if (mode === 'sale_value') {
                // Sale value uses living_space and land_size
                if (data.living_space) {
                    partialLeadData.living_space = parseFloat(data.living_space);
                    partialLeadData.property_size = parseFloat(data.living_space);
                }
                if (data.land_size) {
                    partialLeadData.land_size = parseFloat(data.land_size);
                }
                if (data.house_type) {
                    partialLeadData.house_type = data.house_type;
                }
                if (data.build_year) {
                    partialLeadData.build_year = parseInt(data.build_year);
                }
                if (data.modernization) {
                    partialLeadData.modernization = data.modernization;
                }
                if (data.quality) {
                    partialLeadData.quality = data.quality;
                }
                if (data.street_address) {
                    partialLeadData.street_address = data.street_address;
                }
            } else {
                // Rental and comparison use size
                partialLeadData.property_size = parseFloat(data.size) || 0;
                partialLeadData.address = data.address || '';
                partialLeadData.condition = data.condition;
            }

            const response = await apiFetch({
                path: '/irp/v1/leads/partial',
                method: 'POST',
                data: partialLeadData,
            });

            irpDebug('API response:', response);

            if (response.success) {
                irpDebug('Setting leadId:', response.lead_id);
                setLeadId(response.lead_id);

                // Track partial lead conversion
                trackPartialLead({
                    mode: mode,
                    city: data.city_name || '',
                    propertyType: data.property_type || '',
                });
            } else {
                irpDebug('API error:', response.message);
                setPendingError(response.message || __('Ein Fehler ist aufgetreten.', 'immobilien-rechner-pro'));
            }
        } catch (err) {
            irpDebug('API exception:', err);
            setPendingError(err.message || __('Ein Fehler ist aufgetreten.', 'immobilien-rechner-pro'));
        }
    }, [mode]);

    // Called when pending animation completes - show contact form
    const handlePendingComplete = useCallback(() => {
        irpDebug('handlePendingComplete called - advancing to CONTACT_FORM');
        setCurrentStep(STEPS.CONTACT_FORM);
    }, []);

    // Called when contact form is submitted successfully - show results
    const handleContactFormComplete = useCallback((calculationData) => {
        // If the API returned updated calculation data, use it
        if (calculationData) {
            setResults(calculationData.result || results);
        }
        setCurrentStep(STEPS.RESULTS);
    }, [results]);

    // Back from contact form - go back to calculator
    const handleContactFormBack = useCallback(() => {
        setCurrentStep(STEPS.CALCULATOR);
        setLeadId(null);
    }, []);

    const handleStartOver = () => {
        setMode(initialMode || '');
        setFormData({});
        setResults(null);
        setLeadId(null);
        setPendingError(null);
        setCurrentStep(initialMode ? STEPS.CALCULATOR : STEPS.MODE_SELECT);
    };

    const handleBack = () => {
        switch (currentStep) {
            case STEPS.CALCULATOR:
                if (!initialMode) {
                    setCurrentStep(STEPS.MODE_SELECT);
                }
                break;
            case STEPS.RESULTS:
                // From results, user can only start over
                break;
            default:
                break;
        }
    };

    // Animation variants
    const pageVariants = {
        initial: { opacity: 0, x: 20 },
        animate: { opacity: 1, x: 0 },
        exit: { opacity: 0, x: -20 },
    };

    const pageTransition = {
        duration: 0.3,
        ease: 'easeInOut',
    };

    return (
        <div
            className={`irp-calculator irp-theme-${theme}`}
            style={brandStyles}
        >
            {showBranding && settings.companyLogo && (
                <div className="irp-branding">
                    <img
                        src={settings.companyLogo}
                        alt={settings.companyName || ''}
                        className="irp-logo"
                    />
                </div>
            )}

            <div className="irp-calculator-content">
                <AnimatePresence mode="wait">
                    {currentStep === STEPS.MODE_SELECT && (
                        <motion.div
                            key="mode-select"
                            variants={pageVariants}
                            initial="initial"
                            animate="animate"
                            exit="exit"
                            transition={pageTransition}
                        >
                            <ModeSelector onSelect={handleModeSelect} />
                        </motion.div>
                    )}

                    {currentStep === STEPS.CALCULATOR && (
                        <motion.div
                            key="calculator"
                            variants={pageVariants}
                            initial="initial"
                            animate="animate"
                            exit="exit"
                            transition={pageTransition}
                        >
                            {mode === 'rental' && (
                                <RentalCalculator
                                    initialData={formData}
                                    onComplete={handleCalculationComplete}
                                    onBack={!initialMode ? handleBack : null}
                                    cityId={cityId}
                                    cityName={cityName}
                                />
                            )}
                            {mode === 'comparison' && (
                                <ComparisonCalculator
                                    initialData={formData}
                                    onComplete={handleCalculationComplete}
                                    onBack={!initialMode ? handleBack : null}
                                    cityId={cityId}
                                    cityName={cityName}
                                />
                            )}
                            {mode === 'sale_value' && (
                                <SaleValueCalculator
                                    initialData={formData}
                                    onComplete={handleCalculationComplete}
                                    onBack={!initialMode ? handleBack : null}
                                    cityId={cityId}
                                    cityName={cityName}
                                />
                            )}
                        </motion.div>
                    )}

                    {currentStep === STEPS.CALCULATION_PENDING && (
                        <motion.div
                            key="calculation-pending"
                            variants={pageVariants}
                            initial="initial"
                            animate="animate"
                            exit="exit"
                            transition={pageTransition}
                        >
                            <CalculationPendingStep
                                onComplete={handlePendingComplete}
                                error={pendingError}
                                isReady={!!leadId}
                            />
                        </motion.div>
                    )}

                    {currentStep === STEPS.CONTACT_FORM && (
                        <motion.div
                            key="contact-form"
                            variants={pageVariants}
                            initial="initial"
                            animate="animate"
                            exit="exit"
                            transition={pageTransition}
                        >
                            <ContactFormStep
                                leadId={leadId}
                                onComplete={handleContactFormComplete}
                                onBack={handleContactFormBack}
                            />
                        </motion.div>
                    )}

                    {currentStep === STEPS.RESULTS && (
                        <motion.div
                            key="results"
                            variants={pageVariants}
                            initial="initial"
                            animate="animate"
                            exit="exit"
                            transition={pageTransition}
                        >
                            {mode === 'sale_value' ? (
                                <SaleResultsDisplay
                                    formData={formData}
                                    results={results}
                                    onStartOver={handleStartOver}
                                    leadId={leadId}
                                />
                            ) : (
                                <ResultsDisplay
                                    mode={mode}
                                    formData={formData}
                                    results={results}
                                    onStartOver={handleStartOver}
                                    showBrokerNotice={true}
                                    leadId={leadId}
                                />
                            )}
                        </motion.div>
                    )}
                </AnimatePresence>
            </div>

            {showBranding && settings.companyName && !settings.companyLogo && (
                <div className="irp-footer">
                    <span>{settings.companyName}</span>
                </div>
            )}
        </div>
    );
}
