/**
 * Mortgage Details Step
 */

import { __ } from '@wordpress/i18n';

const inputStyle = {
    color: '#44474c',
    WebkitTextFillColor: '#44474c',
};

export default function MortgageStep({ data, onChange }) {
    const handleChange = (e) => {
        const { name, value } = e.target;
        onChange({ [name]: value });
    };

    const formatNumber = (value) => {
        const num = String(value).replace(/[^0-9]/g, '');
        return num ? parseInt(num).toLocaleString('de-DE') : '';
    };

    const handleMortgageChange = (e) => {
        const rawValue = e.target.value.replace(/[^0-9]/g, '');
        onChange({ remaining_mortgage: rawValue });
    };

    return (
        <div className="irp-mortgage-step">
            <h3>{__('Haben Sie eine bestehende Hypothek?', 'immobilien-rechner-pro')}</h3>
            <p className="irp-step-description">
                {__('Dies hilft uns, Ihre Nettoerlöse und Mietrenditen zu berechnen', 'immobilien-rechner-pro')}
            </p>

            <div className="irp-form-group">
                <label htmlFor="irp-mortgage">
                    {__('Restschuld Hypothek', 'immobilien-rechner-pro')}
                </label>
                <div className="irp-input-with-unit">
                    <input
                        type="text"
                        id="irp-mortgage"
                        name="remaining_mortgage"
                        value={formatNumber(data.remaining_mortgage || '')}
                        onChange={handleMortgageChange}
                        placeholder="150.000"
                        inputMode="numeric"
                        style={inputStyle}
                    />
                    <span className="irp-unit">€</span>
                </div>
                <p className="irp-help-text">
                    {__('Leer lassen oder 0 eingeben wenn abbezahlt', 'immobilien-rechner-pro')}
                </p>
            </div>

            {data.remaining_mortgage && parseInt(data.remaining_mortgage) > 0 && (
                <div className="irp-form-group">
                    <label htmlFor="irp-rate">
                        {__('Aktueller Zinssatz', 'immobilien-rechner-pro')}
                    </label>
                    <div className="irp-input-with-unit">
                        <input
                            type="number"
                            id="irp-rate"
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
                    <p className="irp-help-text">
                        {__('Ihr aktueller jährlicher Zinssatz', 'immobilien-rechner-pro')}
                    </p>
                </div>
            )}

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
                    {__('Historischer Durchschnitt in Deutschland: 2-4% pro Jahr', 'immobilien-rechner-pro')}
                </p>
            </div>

            <div className="irp-info-box irp-info-box-muted">
                <p>
                    {__('Alle Felder auf dieser Seite sind optional. Wir verwenden sinnvolle Standardwerte wenn leer gelassen.', 'immobilien-rechner-pro')}
                </p>
            </div>
        </div>
    );
}
