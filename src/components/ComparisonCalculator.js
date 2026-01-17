/**
 * Comparison Calculator Component
 * Extended wizard for sell vs rent comparison
 */

import { useState, useCallback, useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { motion, AnimatePresence } from 'framer-motion';
import apiFetch from '@wordpress/api-fetch';

import ProgressBar from './ProgressBar';
import PropertyTypeStep from './steps/PropertyTypeStep';
import PropertyDetailsStep from './steps/PropertyDetailsStep';
import CityStep from './steps/CityStep';
import ConditionStep from './steps/ConditionStep';
import LocationRatingStep from './steps/LocationRatingStep';
import FeaturesStep from './steps/FeaturesStep';
import FinancialStep from './steps/FinancialStep';

export default function ComparisonCalculator({ initialData, onComplete, onBack, cityId, cityName }) {
    // Determine which steps to show based on whether cityId is provided
    const STEPS = useMemo(() => {
        const baseSteps = [
            { id: 'type', component: PropertyTypeStep, title: __('Immobilie', 'immobilien-rechner-pro') },
            { id: 'details', component: PropertyDetailsStep, title: __('Details', 'immobilien-rechner-pro') },
        ];

        // Only show city selection step if no cityId was provided in shortcode
        if (!cityId) {
            baseSteps.push({ id: 'city', component: CityStep, title: __('Standort', 'immobilien-rechner-pro') });
        }

        baseSteps.push(
            { id: 'condition', component: ConditionStep, title: __('Zustand', 'immobilien-rechner-pro') },
            { id: 'location_rating', component: LocationRatingStep, title: __('Lage', 'immobilien-rechner-pro') },
            { id: 'features', component: FeaturesStep, title: __('Ausstattung', 'immobilien-rechner-pro') },
            { id: 'financial', component: FinancialStep, title: __('Finanzen', 'immobilien-rechner-pro') },
        );

        return baseSteps;
    }, [cityId]);

    const [currentStep, setCurrentStep] = useState(0);
    const [formData, setFormData] = useState({
        property_type: '',
        size: '',
        rooms: '',
        city_id: cityId || '',
        city_name: cityName || '',
        condition: '',
        location_rating: 3, // Default: "Gute Lage"
        address: '',
        features: [],
        year_built: '',
        property_value: '',
        remaining_mortgage: '',
        mortgage_rate: '3.5',
        holding_period_years: '',
        expected_appreciation: '2',
        ...initialData,
    });
    const [isCalculating, setIsCalculating] = useState(false);
    const [error, setError] = useState(null);
    const [direction, setDirection] = useState(1);

    // Update form data
    const updateFormData = useCallback((updates) => {
        setFormData((prev) => ({ ...prev, ...updates }));
    }, []);

    // Navigate to next step
    const handleNext = useCallback(() => {
        if (currentStep < STEPS.length - 1) {
            setDirection(1);
            setCurrentStep((prev) => prev + 1);
        } else {
            submitCalculation();
        }
    }, [currentStep, STEPS.length, formData]);

    // Navigate to previous step
    const handlePrev = useCallback(() => {
        if (currentStep > 0) {
            setDirection(-1);
            setCurrentStep((prev) => prev - 1);
        } else if (onBack) {
            onBack();
        }
    }, [currentStep, onBack]);

    // Submit calculation to API
    const submitCalculation = async () => {
        setIsCalculating(true);
        setError(null);

        try {
            const response = await apiFetch({
                path: '/irp/v1/calculate/comparison',
                method: 'POST',
                data: {
                    property_type: formData.property_type,
                    size: parseFloat(formData.size),
                    rooms: formData.rooms ? parseInt(formData.rooms) : null,
                    city_id: formData.city_id,
                    condition: formData.condition,
                    location_rating: formData.location_rating || 3,
                    address: formData.address || '',
                    features: formData.features,
                    year_built: formData.year_built ? parseInt(formData.year_built) : null,
                    property_value: parseFloat(formData.property_value),
                    remaining_mortgage: formData.remaining_mortgage ? parseFloat(formData.remaining_mortgage) : 0,
                    mortgage_rate: parseFloat(formData.mortgage_rate),
                    holding_period_years: formData.holding_period_years ? parseInt(formData.holding_period_years) : 0,
                    expected_appreciation: parseFloat(formData.expected_appreciation),
                },
            });

            if (response.success) {
                onComplete(formData, response.data);
            } else {
                setError(response.message || __('Berechnung fehlgeschlagen', 'immobilien-rechner-pro'));
            }
        } catch (err) {
            setError(err.message || __('Ein Fehler ist aufgetreten', 'immobilien-rechner-pro'));
        } finally {
            setIsCalculating(false);
        }
    };

    // Get current step component
    const CurrentStepComponent = STEPS[currentStep].component;

    // Check if current step is valid
    const isStepValid = () => {
        switch (STEPS[currentStep].id) {
            case 'type':
                return !!formData.property_type;
            case 'details':
                return formData.size && parseFloat(formData.size) > 0;
            case 'city':
                return !!formData.city_id;
            case 'condition':
                return !!formData.condition;
            case 'location_rating':
                return formData.location_rating >= 1 && formData.location_rating <= 5;
            case 'features':
                return true;
            case 'financial':
                return formData.property_value && parseFloat(formData.property_value) > 0;
            default:
                return true;
        }
    };

    // Animation variants
    const stepVariants = {
        initial: (dir) => ({
            x: dir > 0 ? 50 : -50,
            opacity: 0,
        }),
        animate: {
            x: 0,
            opacity: 1,
        },
        exit: (dir) => ({
            x: dir < 0 ? 50 : -50,
            opacity: 0,
        }),
    };

    return (
        <div className="irp-comparison-calculator">
            {/* Show city name if fixed via shortcode */}
            {cityId && cityName && (
                <div className="irp-fixed-city">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" width="16" height="16">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                        <circle cx="12" cy="10" r="3" />
                    </svg>
                    <span>{cityName}</span>
                </div>
            )}

            <ProgressBar
                steps={STEPS.map((s) => s.title)}
                currentStep={currentStep}
            />

            <div className="irp-step-container">
                <AnimatePresence mode="wait" custom={direction}>
                    <motion.div
                        key={currentStep}
                        custom={direction}
                        variants={stepVariants}
                        initial="initial"
                        animate="animate"
                        exit="exit"
                        transition={{ duration: 0.25 }}
                        className="irp-step"
                    >
                        <CurrentStepComponent
                            data={formData}
                            onChange={updateFormData}
                        />
                    </motion.div>
                </AnimatePresence>
            </div>

            {error && (
                <div className="irp-error">
                    <p>{error}</p>
                </div>
            )}

            <div className="irp-navigation">
                <button
                    type="button"
                    className="irp-btn irp-btn-secondary"
                    onClick={handlePrev}
                    disabled={isCalculating}
                >
                    {__('Zurück', 'immobilien-rechner-pro')}
                </button>

                <button
                    type="button"
                    className="irp-btn irp-btn-primary"
                    onClick={handleNext}
                    disabled={!isStepValid() || isCalculating}
                >
                    {isCalculating ? (
                        <span className="irp-loading-spinner-small" />
                    ) : currentStep === STEPS.length - 1 ? (
                        __('Berechnen', 'immobilien-rechner-pro')
                    ) : (
                        __('Weiter', 'immobilien-rechner-pro')
                    )}
                </button>
            </div>
        </div>
    );
}
