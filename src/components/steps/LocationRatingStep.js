/**
 * Location Rating Step
 * Allows users to rate the location quality (1-5 stars)
 * Optionally shows Google Maps if API key is configured
 */

import { useState, useEffect, useRef, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

export default function LocationRatingStep({ data, onChange }) {
    const [mapLoaded, setMapLoaded] = useState(false);
    const mapRef = useRef(null);
    const mapInstanceRef = useRef(null);
    const markerRef = useRef(null);
    const autocompleteRef = useRef(null);

    // Get settings from global irpSettings
    const settings = window.irpSettings?.settings || {};
    const locationRatings = window.irpSettings?.locationRatings || {};
    const apiKey = settings.googleMapsApiKey || '';
    const showMap = settings.showMapInLocationStep && apiKey;

    // Current rating (default to 3 = "Gute Lage")
    const currentRating = data.location_rating || 3;
    const currentRatingData = locationRatings[currentRating] || {};

    // Initialize Google Maps
    useEffect(() => {
        if (!showMap || !window.google?.maps) return;

        // Initialize map
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

    // Get city name for autocomplete restriction
    const cityName = data.city_name || '';

    // Initialize Places Autocomplete
    useEffect(() => {
        if (!showMap || !window.google?.maps?.places || !autocompleteRef.current) return;
        if (autocompleteRef.current._autocomplete) return; // Already initialized

        // Use Geocoder to get city bounds for better autocomplete restriction
        const geocoder = new window.google.maps.Geocoder();

        const initAutocomplete = (bounds) => {
            const autocompleteOptions = {
                types: ['address'],
                componentRestrictions: { country: 'de' },
            };

            // If we have city bounds, use them to bias the search
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

                    onChange({
                        address: place.formatted_address || '',
                        address_lat: lat,
                        address_lng: lng,
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
                    // Fallback: init without bounds
                    initAutocomplete(null);
                }
            });
        } else {
            initAutocomplete(null);
        }
    }, [mapLoaded, showMap, cityName]);

    // Handle rating change
    const handleRatingChange = useCallback((rating) => {
        onChange({ location_rating: rating });
    }, [onChange]);

    // Handle address input change (for non-autocomplete)
    const handleAddressChange = (e) => {
        onChange({ address: e.target.value });
    };

    // Parse description into bullet points
    const descriptionLines = (currentRatingData.description || '')
        .split('\n')
        .filter(line => line.trim());

    // Generate stars for display
    const renderStars = (count) => {
        return '★'.repeat(count) + '☆'.repeat(5 - count);
    };

    return (
        <div className="irp-location-rating-step">
            <h3>{__('Wie bewerten Sie die Lage?', 'immobilien-rechner-pro')}</h3>
            <p className="irp-step-description">
                {__('Die Lage hat einen erheblichen Einfluss auf den Mietwert.', 'immobilien-rechner-pro')}
            </p>

            {/* Address Input (optional) */}
            <div className="irp-form-group">
                <label htmlFor="irp-address">
                    {__('Adresse der Immobilie', 'immobilien-rechner-pro')}
                    <span className="irp-optional"> ({__('optional', 'immobilien-rechner-pro')})</span>
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
                        : __('z.B. Musterstraße 123', 'immobilien-rechner-pro')
                    }
                    autoComplete="off"
                />
            </div>

            {/* Google Map */}
            {showMap && (
                <div className="irp-map-container">
                    <div ref={mapRef} className="irp-google-map" />
                </div>
            )}

            {/* Rating Slider */}
            <div className="irp-rating-section">
                <label className="irp-rating-label">
                    {__('Lage-Bewertung', 'immobilien-rechner-pro')}
                    <span className="irp-required">*</span>
                </label>

                <div className="irp-rating-slider-container">
                    <span className="irp-rating-end-label irp-rating-low">
                        {__('Einfach', 'immobilien-rechner-pro')}
                    </span>

                    <div className="irp-rating-slider-wrapper">
                        <input
                            type="range"
                            min="1"
                            max="5"
                            step="1"
                            value={currentRating}
                            onChange={(e) => handleRatingChange(parseInt(e.target.value))}
                            className="irp-rating-slider"
                        />
                        <div className="irp-rating-marks">
                            {[1, 2, 3, 4, 5].map((num) => (
                                <button
                                    key={num}
                                    type="button"
                                    className={`irp-rating-mark ${currentRating === num ? 'is-active' : ''}`}
                                    onClick={() => handleRatingChange(num)}
                                >
                                    {num}
                                </button>
                            ))}
                        </div>
                    </div>

                    <span className="irp-rating-end-label irp-rating-high">
                        {__('Premium', 'immobilien-rechner-pro')}
                    </span>
                </div>
            </div>

            {/* Rating Description Box */}
            <div className="irp-rating-description-box">
                <div className="irp-rating-header">
                    <span className="irp-rating-stars">{renderStars(currentRating)}</span>
                    <span className="irp-rating-name">{currentRatingData.name || ''}</span>
                </div>

                {descriptionLines.length > 0 && (
                    <ul className="irp-rating-features">
                        {descriptionLines.map((line, index) => (
                            <li key={index}>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" width="16" height="16">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                                {line}
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </div>
    );
}
