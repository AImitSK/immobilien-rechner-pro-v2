/**
 * City Selection Step
 * Shows a dropdown with all configured cities when no city_id is provided in shortcode
 */

import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

const inputStyle = {
    color: '#44474c',
    WebkitTextFillColor: '#44474c',
};

export default function CityStep({ data, onChange }) {
    const [cities, setCities] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        // Fetch cities from the API
        const fetchCities = async () => {
            setIsLoading(true);
            setError(null);

            try {
                const response = await apiFetch({
                    path: '/irp/v1/cities',
                });

                if (response.success && response.data) {
                    setCities(response.data);

                    // Auto-select first city if only one is available
                    if (response.data.length === 1 && !data.city_id) {
                        onChange({
                            city_id: response.data[0].id,
                            city_name: response.data[0].name,
                        });
                    }
                }
            } catch (err) {
                console.error('Failed to load cities:', err);
                setError(__('Städte konnten nicht geladen werden', 'immobilien-rechner-pro'));
            } finally {
                setIsLoading(false);
            }
        };

        fetchCities();
    }, []);

    const handleCityChange = (e) => {
        const selectedId = e.target.value;
        const selectedCity = cities.find(c => c.id === selectedId);

        onChange({
            city_id: selectedId,
            city_name: selectedCity ? selectedCity.name : '',
        });
    };

    if (isLoading) {
        return (
            <div className="irp-city-step">
                <h3>{__('Standort auswählen', 'immobilien-rechner-pro')}</h3>
                <div className="irp-loading-inline">
                    <span className="irp-loading-spinner-small" />
                    <span>{__('Städte werden geladen...', 'immobilien-rechner-pro')}</span>
                </div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="irp-city-step">
                <h3>{__('Standort auswählen', 'immobilien-rechner-pro')}</h3>
                <div className="irp-error-box">
                    <p>{error}</p>
                </div>
            </div>
        );
    }

    if (cities.length === 0) {
        return (
            <div className="irp-city-step">
                <h3>{__('Standort auswählen', 'immobilien-rechner-pro')}</h3>
                <div className="irp-info-box">
                    <p>{__('Keine Städte konfiguriert. Bitte kontaktieren Sie den Administrator.', 'immobilien-rechner-pro')}</p>
                </div>
            </div>
        );
    }

    return (
        <div className="irp-city-step">
            <h3>{__('Für welche Stadt möchten Sie den Mietwert berechnen?', 'immobilien-rechner-pro')}</h3>

            <div className="irp-form-group">
                <label htmlFor="irp-city">
                    {__('Stadt', 'immobilien-rechner-pro')}
                    <span className="irp-required">*</span>
                </label>
                <select
                    id="irp-city"
                    name="city_id"
                    value={data.city_id || ''}
                    onChange={handleCityChange}
                    className="irp-city-select"
                    required
                    style={inputStyle}
                >
                    <option value="">{__('Bitte wählen...', 'immobilien-rechner-pro')}</option>
                    {cities.map((city) => (
                        <option key={city.id} value={city.id}>
                            {city.name}
                        </option>
                    ))}
                </select>
            </div>

            {data.city_id && (
                <p className="irp-city-selected">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" width="16" height="16">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                        <circle cx="12" cy="10" r="3" />
                    </svg>
                    {__('Berechnung für', 'immobilien-rechner-pro')} <strong>{data.city_name}</strong>
                </p>
            )}
        </div>
    );
}
