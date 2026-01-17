/**
 * Property Value Step
 */

import { __ } from '@wordpress/i18n';

const inputStyle = {
    color: '#44474c',
    WebkitTextFillColor: '#44474c',
};

export default function PropertyValueStep({ data, onChange }) {
    const handleChange = (e) => {
        const { name, value } = e.target;
        onChange({ [name]: value });
    };

    const formatNumber = (value) => {
        const num = value.replace(/[^0-9]/g, '');
        return num ? parseInt(num).toLocaleString('de-DE') : '';
    };

    const handleValueChange = (e) => {
        const rawValue = e.target.value.replace(/[^0-9]/g, '');
        onChange({ property_value: rawValue });
    };

    return (
        <div className="irp-value-step">
            <h3>{__('Was ist Ihre Immobilie wert?', 'immobilien-rechner-pro')}</h3>
            <p className="irp-step-description">
                {__('Geben Sie Ihren geschätzten Immobilienwert oder eine aktuelle Bewertung ein', 'immobilien-rechner-pro')}
            </p>

            <div className="irp-form-group">
                <label htmlFor="irp-property-value">
                    {__('Immobilienwert', 'immobilien-rechner-pro')}
                    <span className="irp-required">*</span>
                </label>
                <div className="irp-input-with-unit irp-input-large">
                    <input
                        type="text"
                        id="irp-property-value"
                        name="property_value"
                        value={formatNumber(data.property_value || '')}
                        onChange={handleValueChange}
                        placeholder="350.000"
                        inputMode="numeric"
                        required
                        style={inputStyle}
                    />
                    <span className="irp-unit">€</span>
                </div>
                <p className="irp-help-text">
                    {__('Geschätzter Marktwert Ihrer Immobilie', 'immobilien-rechner-pro')}
                </p>
            </div>

            <div className="irp-form-group">
                <label htmlFor="irp-holding-period">
                    {__('Wie lange besitzen Sie diese Immobilie?', 'immobilien-rechner-pro')}
                </label>
                <div className="irp-input-with-unit">
                    <input
                        type="number"
                        id="irp-holding-period"
                        name="holding_period_years"
                        value={data.holding_period_years}
                        onChange={handleChange}
                        placeholder="5"
                        min="0"
                        max="50"
                        style={inputStyle}
                    />
                    <span className="irp-unit">{__('Jahre', 'immobilien-rechner-pro')}</span>
                </div>
                <p className="irp-help-text">
                    {__('Wichtig für die Spekulationssteuer-Berechnung (10-Jahres-Regel)', 'immobilien-rechner-pro')}
                </p>
            </div>

            <div className="irp-info-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" width="20" height="20">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="16" x2="12" y2="12" />
                    <line x1="12" y1="8" x2="12.01" y2="8" />
                </svg>
                <div>
                    <strong>{__('Tipp:', 'immobilien-rechner-pro')}</strong>
                    <p>{__('Wenn Sie die Immobilie weniger als 10 Jahre besitzen, kann ein Verkauf Spekulationssteuer auf Gewinne auslösen.', 'immobilien-rechner-pro')}</p>
                </div>
            </div>
        </div>
    );
}
