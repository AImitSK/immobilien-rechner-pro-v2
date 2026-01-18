/**
 * Sale Value Calculator Component
 * Multi-step wizard for property sale value estimation
 */

import { useState, useCallback, useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { motion, AnimatePresence } from 'framer-motion';
import apiFetch from '@wordpress/api-fetch';

import ProgressBar from './ProgressBar';
import SalePropertyTypeStep from './steps/SalePropertyTypeStep';
import SaleSizeStep from './steps/SaleSizeStep';
import SaleFeaturesStep from './steps/SaleFeaturesStep';
import SaleQualityLocationStep from './steps/SaleQualityLocationStep';
import SaleAddressStep from './steps/SaleAddressStep';
import SalePurposeStep from './steps/SalePurposeStep';

export default function SaleValueCalculator({ initialData, onComplete, onBack, cityId, cityName }) {
    // Define steps
    const STEPS = useMemo(() => {
        return [
            { id: 'property_type', component: SalePropertyTypeStep, title: __('Immobilienart', 'immobilien-rechner-pro') },
            { id: 'size', component: SaleSizeStep, title: __('Größe', 'immobilien-rechner-pro') },
            { id: 'features', component: SaleFeaturesStep, title: __('Ausstattung', 'immobilien-rechner-pro') },
            { id: 'quality_location', component: SaleQualityLocationStep, title: __('Qualität & Lage', 'immobilien-rechner-pro') },
            { id: 'address', component: SaleAddressStep, title: __('Adresse', 'immobilien-rechner-pro') },
            { id: 'purpose', component: SalePurposeStep, title: __('Vorhaben', 'immobilien-rechner-pro') },
        ];
    }, []);

    const [currentStep, setCurrentStep] = useState(0);
    const [formData, setFormData] = useState({
        property_type: '',
        living_space: '',
        land_size: '',
        house_type: '',
        build_year: '',
        modernization: 'none',
        quality: 'normal',
        location_rating: 3,
        features: [],
        usage_type: '',
        sale_intention: '',
        timeframe: '',
        street_address: '',
        city_id: cityId || '',
        city_name: cityName || '',
        ...initialData,
    });
    const [isCalculating, setIsCalculating] = useState(false);
    const [error, setError] = useState(null);

    // Update form data
    const updateFormData = useCallback((updates) => {
        setFormData((prev) => ({ ...prev, ...updates }));
    }, []);

    // Navigate to next step
    const handleNext = useCallback(() => {
        if (currentStep < STEPS.length - 1) {
            setCurrentStep((prev) => prev + 1);
        } else {
            // Final step - submit calculation
            submitCalculation();
        }
    }, [currentStep, STEPS.length]);

    // Navigate to previous step
    const handlePrev = useCallback(() => {
        if (currentStep > 0) {
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
            const propertyType = formData.property_type;
            const isLand = propertyType === 'land';
            const isHouse = propertyType === 'house';
            const isApartment = propertyType === 'apartment';

            // Build request data based on property type
            const requestData = {
                property_type: propertyType,
                location_rating: formData.location_rating || 3,
                features: formData.features || [],
                city_id: formData.city_id || cityId || '',
            };

            // Add living_space for apartments and houses
            if (isApartment || isHouse) {
                requestData.living_space = parseFloat(formData.living_space) || 0;
                requestData.build_year = formData.build_year ? parseInt(formData.build_year) : null;
                requestData.modernization = formData.modernization || 'never';
                requestData.quality = formData.quality || 'normal';
            }

            // Add land_size for houses and land
            if (isHouse || isLand) {
                requestData.land_size = parseFloat(formData.land_size) || 0;
            }

            // Add house_type only for houses
            if (isHouse && formData.house_type) {
                requestData.house_type = formData.house_type;
            }

            const response = await apiFetch({
                path: '/irp/v1/calculate/sale_value',
                method: 'POST',
                data: requestData,
            });

            if (response.success) {
                // Add extra data for results display
                const enrichedFormData = {
                    ...formData,
                    city_id: formData.city_id || cityId,
                    city_name: formData.city_name || cityName,
                };
                onComplete(enrichedFormData, response.data);
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
            case 'property_type':
                return !!formData.property_type;
            case 'size':
                if (formData.property_type === 'land') {
                    return formData.land_size && parseFloat(formData.land_size) > 0;
                }
                return formData.living_space && parseFloat(formData.living_space) > 0;
            case 'features':
                return true; // Features are optional
            case 'quality_location':
                return !!formData.quality && formData.location_rating >= 1 && formData.location_rating <= 5;
            case 'address':
                return true; // Address is optional
            case 'purpose':
                return true; // Purpose fields are optional
            default:
                return true;
        }
    };

    // Animation variants
    const stepVariants = {
        initial: (direction) => ({
            x: direction > 0 ? 50 : -50,
            opacity: 0,
        }),
        animate: {
            x: 0,
            opacity: 1,
        },
        exit: (direction) => ({
            x: direction < 0 ? 50 : -50,
            opacity: 0,
        }),
    };

    const [direction, setDirection] = useState(1);

    const goNext = () => {
        setDirection(1);
        handleNext();
    };

    const goPrev = () => {
        setDirection(-1);
        handlePrev();
    };

    return (
        <div className="irp-sale-calculator">
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
                    onClick={goPrev}
                    disabled={isCalculating}
                >
                    {__('Zurück', 'immobilien-rechner-pro')}
                </button>

                <button
                    type="button"
                    className="irp-btn irp-btn-primary"
                    onClick={goNext}
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
