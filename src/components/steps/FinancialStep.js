/**
 * Financial Step Component
 * For sell vs rent comparison - property value and mortgage details
 */

import { __ } from '@wordpress/i18n';

const inputStyle = {
    color: '#44474c',
    WebkitTextFillColor: '#44474c',
};

export default function FinancialStep({ data, onChange }) {
    const handleChange = (e) => {
        const { name, value } = e.target;
        onChange({ [name]: value });
    };

    const formatCurrency = (value) => {
        if (!value) return '';
        const num = parseInt(value.replace(/\D/g, ''));
        if (isNaN(num)) return '';
        return num.toLocaleString('de-DE');
    };

    const handleCurrencyChange = (e) => {
        const { name, value } = e.target;
        const numericValue = value.replace(/\D/g, '');
        onChange({ [name]: numericValue });
    };

    return (
        <div className="irp-financial-step">
            <h3>{__('Finanzielle Details', 'immobilien-rechner-pro')}</h3>
            <p className="irp-step-description">
                {__('Diese Informationen helfen uns, Verkauf und Vermietung genau zu vergleichen.', 'immobilien-rechner-pro')}
            </p>

            <div className="irp-form-group">
                <label htmlFor="irp-property-value">
                    {__('Geschätzter Immobilienwert', 'immobilien-rechner-pro')}
                    <span className="irp-required">*</span>
                </label>
                <div className="irp-input-with-unit">
                    <input
                        type="text"
                        id="irp-property-value"
                        name="property_value"
                        value={formatCurrency(data.property_value)}
                        onChange={handleCurrencyChange}
                        placeholder="350.000"
                        inputMode="numeric"
                        required
                        style={inputStyle}
                    />
                    <span className="irp-unit">€</span>
                </div>
                <p className="irp-help-text">
                    {__('Ihre beste Schätzung des aktuellen Marktwerts', 'immobilien-rechner-pro')}
                </p>
            </div>

            <div className="irp-form-group">
                <label htmlFor="irp-remaining-mortgage">
                    {__('Restschuld Hypothek', 'immobilien-rechner-pro')}
                </label>
                <div className="irp-input-with-unit">
                    <input
                        type="text"
                        id="irp-remaining-mortgage"
                        name="remaining_mortgage"
                        value={formatCurrency(data.remaining_mortgage)}
                        onChange={handleCurrencyChange}
                        placeholder="150.000"
                        inputMode="numeric"
                        style={inputStyle}
                    />
                    <span className="irp-unit">€</span>
                </div>
                <p className="irp-help-text">
                    {__('Offener Darlehensbetrag (falls vorhanden)', 'immobilien-rechner-pro')}
                </p>
            </div>

            <div className="irp-form-row">
                <div className="irp-form-group">
                    <label htmlFor="irp-holding-period">
                        {__('Besitzdauer', 'immobilien-rechner-pro')}
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
                        {__('Wie lange Sie die Immobilie besitzen', 'immobilien-rechner-pro')}
                    </p>
                </div>

                <div className="irp-form-group">
                    <label htmlFor="irp-mortgage-rate">
                        {__('Hypothekenzins', 'immobilien-rechner-pro')}
                    </label>
                    <div className="irp-input-with-unit">
                        <input
                            type="number"
                            id="irp-mortgage-rate"
                            name="mortgage_rate"
                            value={data.mortgage_rate}
                            onChange={handleChange}
                            placeholder="3.5"
                            min="0"
                            max="15"
                            step="0.1"
                            style={inputStyle}
                        />
                        <span className="irp-unit">%</span>
                    </div>
                </div>
            </div>

            <div className="irp-info-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" width="20" height="20">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="16" x2="12" y2="12" />
                    <line x1="12" y1="8" x2="12.01" y2="8" />
                </svg>
                <div>
                    <strong>{__('Warum das wichtig ist', 'immobilien-rechner-pro')}</strong>
                    <p>
                        {__('Immobilien, die weniger als 10 Jahre im Besitz sind, können beim Verkauf der Spekulationssteuer unterliegen. Ihre Hypothek beeinflusst sowohl den Nettoverkaufserlös als auch die laufenden Kosten der Vermietung.', 'immobilien-rechner-pro')}
                    </p>
                </div>
            </div>

            <details className="irp-advanced-options">
                <summary>{__('Erweiterte Optionen', 'immobilien-rechner-pro')}</summary>
                <div className="irp-advanced-content">
                    <div className="irp-form-group">
                        <label htmlFor="irp-appreciation">
                            {__('Erwartete jährliche Wertsteigerung', 'immobilien-rechner-pro')}
                        </label>
                        <div className="irp-input-with-unit">
                            <input
                                type="number"
                                id="irp-appreciation"
                                name="expected_appreciation"
                                value={data.expected_appreciation}
                                onChange={handleChange}
                                placeholder="2"
                                min="-10"
                                max="20"
                                step="0.5"
                                style={inputStyle}
                            />
                            <span className="irp-unit">%</span>
                        </div>
                        <p className="irp-help-text">
                            {__('Der historische Durchschnitt liegt bei etwa 2-3% pro Jahr', 'immobilien-rechner-pro')}
                        </p>
                    </div>
                </div>
            </details>
        </div>
    );
}
