/**
 * Sale Value Calculator - Address Step
 * Address input with Google Maps Autocomplete and Map display
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
    const mapRef = useRef(null);
    const mapInstanceRef = useRef(null);
    const markerRef = useRef(null);

    // Get Google Maps API key from settings
    const settings = window.irpSettings?.settings || {};
    const googleMapsApiKey = settings.googleMapsApiKey || window.irpSettings?.googleMapsApiKey || '';
    const showMap = settings.showMapInLocationStep && googleMapsApiKey;
    const cityName = city?.name || data.property_location || '';

    // Initialize Google Map
    useEffect(() => {
        if (!showMap || !window.google?.maps) return;

        const initMap = () => {
            if (!mapRef.current || mapInstanceRef.current) return;

            const defaultCenter = { lat: 51.1657, lng: 10.4515 }; // Germany center

            mapInstanceRef.current = new window.google.maps.Map(mapRef.current, {
                center: data.address_lat && data.address_lng
                    ? { lat: data.address_lat, lng: data.address_lng }
                    : defaultCenter,
                zoom: data.address_lat ? 15 : 6,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: false,
            });

            // Add marker if we have coordinates
            if (data.address_lat && data.address_lng) {
                markerRef.current = new window.google.maps.Marker({
                    position: { lat: data.address_lat, lng: data.address_lng },
                    map: mapInstanceRef.current,
                });
            }
        };

        // Small delay to ensure DOM is ready
        setTimeout(initMap, 100);

        return () => {
            if (markerRef.current) {
                markerRef.current.setMap(null);
            }
        };
    }, [showMap]);

    // Geocode city name to center map
    useEffect(() => {
        if (!showMap || !window.google?.maps || !cityName || data.address_lat) return;

        const geocoder = new window.google.maps.Geocoder();
        geocoder.geocode({ address: cityName + ', Deutschland' }, (results, status) => {
            if (status === 'OK' && results[0]?.geometry?.location && mapInstanceRef.current) {
                mapInstanceRef.current.setCenter(results[0].geometry.location);
                mapInstanceRef.current.setZoom(12);
            }
        });
    }, [showMap, cityName]);

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

                    // Get coordinates from place
                    const lat = place.geometry?.location?.lat();
                    const lng = place.geometry?.location?.lng();

                    onChange({
                        street_address: fullStreet,
                        zip_code: zip || data.zip_code,
                        property_location: cityFromPlace || data.property_location,
                        address_lat: lat,
                        address_lng: lng,
                    });

                    // Update map
                    if (lat && lng && mapInstanceRef.current) {
                        mapInstanceRef.current.setCenter({ lat, lng });
                        mapInstanceRef.current.setZoom(15);

                        if (markerRef.current) {
                            markerRef.current.setPosition({ lat, lng });
                        } else {
                            markerRef.current = new window.google.maps.Marker({
                                position: { lat, lng },
                                map: mapInstanceRef.current,
                            });
                        }
                    }
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

            {/* Google Map */}
            {showMap && (
                <div className="irp-map-container">
                    <div ref={mapRef} className="irp-google-map" />
                </div>
            )}

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
