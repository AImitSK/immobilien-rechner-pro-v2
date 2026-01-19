/**
 * Sale Value Calculator - Address Step
 * Single address input with Google Maps Autocomplete and Map display
 * Similar to LocationRatingStep but extracts address components for data flow
 */

import { useState, useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

export default function SaleAddressStep({ data, onChange, city }) {
    const [mapLoaded, setMapLoaded] = useState(false);
    const mapRef = useRef(null);
    const mapInstanceRef = useRef(null);
    const markerRef = useRef(null);
    const autocompleteRef = useRef(null);

    // Get settings
    const settings = window.irpSettings?.settings || {};
    const apiKey = settings.googleMapsApiKey || window.irpSettings?.googleMapsApiKey || '';
    const showMap = apiKey && window.google?.maps;

    // City name for autocomplete restriction
    const cityName = city?.name || data.property_location || '';

    // Initialize Google Maps
    useEffect(() => {
        if (!showMap) return;

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

            setMapLoaded(true);
        };

        // Small delay to ensure DOM is ready
        setTimeout(initMap, 100);

        return () => {
            if (markerRef.current) {
                markerRef.current.setMap(null);
            }
        };
    }, [showMap]);

    // Initialize Places Autocomplete
    useEffect(() => {
        if (!showMap || !window.google?.maps?.places || !autocompleteRef.current) return;
        if (autocompleteRef.current._autocomplete) return; // Already initialized

        const geocoder = new window.google.maps.Geocoder();

        const initAutocomplete = (bounds) => {
            const autocompleteOptions = {
                types: ['address'],
                componentRestrictions: { country: 'de' },
            };

            if (bounds) {
                autocompleteOptions.bounds = bounds;
                autocompleteOptions.strictBounds = true;
            }

            const autocomplete = new window.google.maps.places.Autocomplete(
                autocompleteRef.current,
                autocompleteOptions
            );

            autocomplete.addListener('place_changed', () => {
                const place = autocomplete.getPlace();

                if (place.geometry?.location) {
                    const lat = place.geometry.location.lat();
                    const lng = place.geometry.location.lng();

                    // Extract address components
                    let street = '';
                    let streetNumber = '';
                    let zip = '';
                    let cityFromPlace = '';

                    if (place.address_components) {
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
                    }

                    const fullStreet = streetNumber
                        ? `${street} ${streetNumber}`
                        : street;

                    // Update all address data
                    onChange({
                        address: place.formatted_address || '',
                        address_lat: lat,
                        address_lng: lng,
                        street_address: fullStreet,
                        zip_code: zip || data.zip_code,
                        property_location: cityFromPlace || data.property_location || cityName,
                    });

                    // Update map
                    if (mapInstanceRef.current) {
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

            autocompleteRef.current._autocomplete = autocomplete;
        };

        // If we have a city name, geocode it to get bounds
        if (cityName) {
            geocoder.geocode({ address: cityName + ', Deutschland' }, (results, status) => {
                if (status === 'OK' && results[0]?.geometry?.viewport) {
                    initAutocomplete(results[0].geometry.viewport);

                    // Also center the map on the city
                    if (mapInstanceRef.current && !data.address_lat) {
                        mapInstanceRef.current.setCenter(results[0].geometry.location);
                        mapInstanceRef.current.setZoom(12);
                    }
                } else {
                    initAutocomplete(null);
                }
            });
        } else {
            initAutocomplete(null);
        }
    }, [mapLoaded, showMap, cityName]);

    // Handle address input change
    const handleAddressChange = (e) => {
        onChange({ address: e.target.value });
    };

    return (
        <div className="irp-sale-address-step">
            <h3>{__('Wo befindet sich die Immobilie?', 'immobilien-rechner-pro')}</h3>
            <p className="irp-step-description">
                {__('Die genaue Adresse wird für die Bewertung benötigt.', 'immobilien-rechner-pro')}
            </p>

            {/* Address Input */}
            <div className="irp-form-group">
                <label htmlFor="irp-address">
                    {__('Adresse der Immobilie', 'immobilien-rechner-pro')}
                    <span className="irp-required">*</span>
                </label>
                <input
                    ref={showMap ? autocompleteRef : null}
                    type="text"
                    id="irp-address"
                    name="address"
                    value={data.address || ''}
                    onChange={handleAddressChange}
                    placeholder={showMap
                        ? (cityName
                            ? __('Straße und Hausnummer in ', 'immobilien-rechner-pro') + cityName + '...'
                            : __('Adresse eingeben...', 'immobilien-rechner-pro'))
                        : __('z.B. Musterstraße 123, 12345 Berlin', 'immobilien-rechner-pro')
                    }
                    autoComplete="off"
                    required
                />
                {showMap && (
                    <p className="irp-input-hint">
                        {__('Tippen Sie die Adresse ein und wählen Sie einen Vorschlag aus.', 'immobilien-rechner-pro')}
                    </p>
                )}
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
