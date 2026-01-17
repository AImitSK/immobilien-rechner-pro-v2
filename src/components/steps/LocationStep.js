/**
 * Location Step
 */

import { useState, useEffect, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { useDebouncedCallback } from '../../hooks/useDebounce';

const inputStyle = {
    color: '#44474c',
    WebkitTextFillColor: '#44474c',
};

export default function LocationStep({ data, onChange }) {
    const [suggestions, setSuggestions] = useState([]);
    const [showSuggestions, setShowSuggestions] = useState(false);
    const [isLoading, setIsLoading] = useState(false);

    // Debounced search for location suggestions
    const searchLocations = useDebouncedCallback(async (search) => {
        if (search.length < 2) {
            setSuggestions([]);
            return;
        }

        setIsLoading(true);

        try {
            const response = await apiFetch({
                path: `/irp/v1/locations?search=${encodeURIComponent(search)}`,
            });

            if (response.success) {
                setSuggestions(response.data);
            }
        } catch (err) {
            console.error('Location search error:', err);
        } finally {
            setIsLoading(false);
        }
    }, 300);

    const handleZipChange = (e) => {
        const value = e.target.value.replace(/[^0-9]/g, '').slice(0, 5);
        onChange({ zip_code: value });
        searchLocations(value);
    };

    const handleLocationChange = (e) => {
        const value = e.target.value;
        onChange({ location: value });
        searchLocations(value);
    };

    const handleSuggestionClick = (suggestion) => {
        onChange({
            zip_code: suggestion.zip,
            location: suggestion.city,
        });
        setSuggestions([]);
        setShowSuggestions(false);
    };

    const handleFocus = () => {
        if (suggestions.length > 0) {
            setShowSuggestions(true);
        }
    };

    const handleBlur = () => {
        // Delay hiding to allow click on suggestion
        setTimeout(() => setShowSuggestions(false), 200);
    };

    return (
        <div className="irp-location-step">
            <h3>{__('Wo befindet sich Ihre Immobilie?', 'immobilien-rechner-pro')}</h3>

            <div className="irp-form-row">
                <div className="irp-form-group irp-form-group-zip">
                    <label htmlFor="irp-zip">
                        {__('Postleitzahl', 'immobilien-rechner-pro')}
                        <span className="irp-required">*</span>
                    </label>
                    <input
                        type="text"
                        id="irp-zip"
                        name="zip_code"
                        value={data.zip_code}
                        onChange={handleZipChange}
                        onFocus={handleFocus}
                        onBlur={handleBlur}
                        placeholder="10115"
                        maxLength="5"
                        pattern="[0-9]*"
                        inputMode="numeric"
                        required
                        style={inputStyle}
                    />
                </div>

                <div className="irp-form-group irp-form-group-city">
                    <label htmlFor="irp-location">
                        {__('Stadt / Gebiet', 'immobilien-rechner-pro')}
                    </label>
                    <div className="irp-autocomplete-wrapper">
                        <input
                            type="text"
                            id="irp-location"
                            name="location"
                            value={data.location}
                            onChange={handleLocationChange}
                            onFocus={handleFocus}
                            onBlur={handleBlur}
                            placeholder={__('z.B. Berlin Mitte', 'immobilien-rechner-pro')}
                            autoComplete="off"
                            style={inputStyle}
                        />

                        {isLoading && (
                            <span className="irp-autocomplete-loading">
                                <span className="irp-loading-spinner-small" />
                            </span>
                        )}

                        {showSuggestions && suggestions.length > 0 && (
                            <ul className="irp-autocomplete-suggestions">
                                {suggestions.map((suggestion, index) => (
                                    <li key={index}>
                                        <button
                                            type="button"
                                            onClick={() => handleSuggestionClick(suggestion)}
                                        >
                                            <span className="irp-suggestion-zip">{suggestion.zip}</span>
                                            <span className="irp-suggestion-city">{suggestion.city}</span>
                                            {suggestion.state && (
                                                <span className="irp-suggestion-state">{suggestion.state}</span>
                                            )}
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </div>
                </div>
            </div>

            <p className="irp-info-text">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" width="16" height="16">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="16" x2="12" y2="12" />
                    <line x1="12" y1="8" x2="12.01" y2="8" />
                </svg>
                {__('Der Standort beeinflusst den Mietwert erheblich. Städtische Gebiete erzielen in der Regel höhere Mieten.', 'immobilien-rechner-pro')}
            </p>
        </div>
    );
}
