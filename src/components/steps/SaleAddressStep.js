/**
 * Sale Value Calculator - Address Step
 * Address input with Google Maps Autocomplete support
 */

import { useState, useEffect, useRef, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const inputStyle = {
    color: '#44474c',
    WebkitTextFillColor: '#44474c',
};

export default function SaleAddressStep({ data, onChange, city }) {
    const [isGoogleMapsLoaded, setIsGoogleMapsLoaded] = useState(false);
    const [autocomplete, setAutocomplete] = useState(null);
    const addressInputRef = useRef(null);

    // Get Google Maps API key from settings
    const googleMapsApiKey = window.irpSettings?.googleMapsApiKey || '';
    const cityName = city?.name || data.property_location || '';

    // Initialize Google Maps Autocomplete
    useEffect(() => {
        if (!googleMapsApiKey || !window.google?.maps?.places) {
            setIsGoogleMapsLoaded(false);
            return;
        }

        setIsGoogleMapsLoaded(true);

        if (addressInputRef.current && !autocomplete) {
            const options = {
                types: ['address'],
                componentRestrictions: { country: 'de' },
            };

            const ac = new window.google.maps.places.Autocomplete(
                addressInputRef.current,
                options
            );

            ac.addListener('place_changed', () => {
                const place = ac.getPlace();

                if (place && place.address_components) {
                    let street = '';
                    let streetNumber = '';
                    let zip = '';
                    let cityFromPlace = '';

                    place.address_components.forEach((component) => {
                        const types = component.types;

                        if (types.includes('route')) {
                            street = component.long_name;
                        }
                        if (types.includes('street_number')) {
                            streetNumber = component.long_name;
                        }
                        if (types.includes('postal_code')) {
                            zip = component.long_name;
                        }
                        if (types.includes('locality')) {
                            cityFromPlace = component.long_name;
                        }
                    });

                    const fullStreet = streetNumber
                        ? `${street} ${streetNumber}`
                        : street;

                    onChange({
                        street_address: fullStreet,
                        zip_code: zip || data.zip_code,
                        property_location: cityFromPlace || data.property_location,
                    });
                }
            });

            setAutocomplete(ac);
        }

        return () => {
            if (autocomplete) {
                window.google.maps.event.clearInstanceListeners(autocomplete);
            }
        };
    }, [googleMapsApiKey, autocomplete]);

    const handleZipChange = (e) => {
        const value = e.target.value.replace(/[^0-9]/g, '').slice(0, 5);
        onChange({ zip_code: value });
    };

    const handleCityChange = (e) => {
        onChange({ property_location: e.target.value });
    };

    const handleStreetChange = (e) => {
        onChange({ street_address: e.target.value });
    };

    return (
        <div className="irp-sale-address-step">
            <h3>{__('Wo befindet sich die Immobilie?', 'immobilien-rechner-pro')}</h3>
            <p className="irp-step-description">
                {__('Die genaue Adresse wird für die Bewertung und Kontaktaufnahme benötigt.', 'immobilien-rechner-pro')}
            </p>

            <div className="irp-address-form">
                <div className="irp-form-row">
                    <div className="irp-form-group irp-form-group-zip">
                        <label htmlFor="irp-zip">
                            {__('PLZ', 'immobilien-rechner-pro')}
                            <span className="irp-required">*</span>
                        </label>
                        <input
                            type="text"
                            id="irp-zip"
                            name="zip_code"
                            value={data.zip_code || ''}
                            onChange={handleZipChange}
                            placeholder="12345"
                            maxLength="5"
                            pattern="[0-9]*"
                            inputMode="numeric"
                            required
                            style={inputStyle}
                        />
                    </div>

                    <div className="irp-form-group irp-form-group-city">
                        <label htmlFor="irp-city">
                            {__('Stadt', 'immobilien-rechner-pro')}
                            <span className="irp-required">*</span>
                        </label>
                        <input
                            type="text"
                            id="irp-city"
                            name="property_location"
                            value={data.property_location || cityName || ''}
                            onChange={handleCityChange}
                            placeholder={__('z.B. Berlin', 'immobilien-rechner-pro')}
                            required
                            style={inputStyle}
                        />
                    </div>
                </div>

                <div className="irp-form-group">
                    <label htmlFor="irp-street">
                        {__('Straße und Hausnummer', 'immobilien-rechner-pro')}
                        <span className="irp-required">*</span>
                    </label>
                    <input
                        ref={addressInputRef}
                        type="text"
                        id="irp-street"
                        name="street_address"
                        value={data.street_address || ''}
                        onChange={handleStreetChange}
                        placeholder={
                            cityName
                                ? __(`Straße und Hausnummer in ${cityName}...`, 'immobilien-rechner-pro')
                                : __('Straße und Hausnummer...', 'immobilien-rechner-pro')
                        }
                        autoComplete="off"
                        required
                        style={inputStyle}
                    />
                    {isGoogleMapsLoaded && (
                        <p className="irp-input-hint">
                            {__('Tippen Sie die Adresse ein für Vorschläge.', 'immobilien-rechner-pro')}
                        </p>
                    )}
                </div>
            </div>

            <p className="irp-info-text">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" width="16" height="16">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                    <circle cx="12" cy="10" r="3" />
                </svg>
                {__('Die Adresse wird vertraulich behandelt und nur für die Bewertung verwendet.', 'immobilien-rechner-pro')}
            </p>
        </div>
    );
}
