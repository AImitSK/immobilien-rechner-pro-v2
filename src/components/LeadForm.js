/**
 * Lead Form Component
 */

import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { motion } from 'framer-motion';

const inputStyle = {
    color: '#44474c',
    WebkitTextFillColor: '#44474c',
};

export default function LeadForm({ mode, calculationData, onSubmitted, onBack }) {
    const settings = window.irpSettings?.settings || {};

    const [formData, setFormData] = useState({
        name: '',
        email: '',
        phone: '',
        consent: false,
    });
    const [errors, setErrors] = useState({});
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [submitError, setSubmitError] = useState(null);

    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        setFormData((prev) => ({
            ...prev,
            [name]: type === 'checkbox' ? checked : value,
        }));

        // Clear error on change
        if (errors[name]) {
            setErrors((prev) => ({ ...prev, [name]: null }));
        }
    };

    const validate = () => {
        const newErrors = {};

        if (!formData.email) {
            newErrors.email = __('E-Mail ist erforderlich', 'immobilien-rechner-pro');
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
            newErrors.email = __('Bitte geben Sie eine gültige E-Mail-Adresse ein', 'immobilien-rechner-pro');
        }

        if (settings.requireConsent && !formData.consent) {
            newErrors.consent = __('Sie müssen der Datenschutzerklärung zustimmen', 'immobilien-rechner-pro');
        }

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const handleSubmit = async (e) => {
        e.preventDefault();

        if (!validate()) {
            return;
        }

        setIsSubmitting(true);
        setSubmitError(null);

        try {
            const response = await apiFetch({
                path: '/irp/v1/leads',
                method: 'POST',
                data: {
                    name: formData.name,
                    email: formData.email,
                    phone: formData.phone,
                    mode,
                    calculation_data: calculationData,
                    consent: formData.consent,
                },
            });

            if (response.success) {
                onSubmitted();
            } else {
                setSubmitError(response.message || __('Übermittlung fehlgeschlagen', 'immobilien-rechner-pro'));
            }
        } catch (err) {
            setSubmitError(err.message || __('Ein Fehler ist aufgetreten', 'immobilien-rechner-pro'));
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <div className="irp-lead-form">
            <motion.div
                className="irp-lead-form-header"
                initial={{ opacity: 0, y: -20 }}
                animate={{ opacity: 1, y: 0 }}
            >
                <h2>{__('Ihre kostenlose Beratung', 'immobilien-rechner-pro')}</h2>
                <p>
                    {__('Hinterlassen Sie Ihre Kontaktdaten und ein lokaler Experte wird sich mit Ihnen in Verbindung setzen.', 'immobilien-rechner-pro')}
                </p>
            </motion.div>

            <motion.form
                onSubmit={handleSubmit}
                className="irp-form"
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                transition={{ delay: 0.2 }}
            >
                <div className="irp-form-group">
                    <label htmlFor="irp-lead-name">
                        {__('Name', 'immobilien-rechner-pro')}
                    </label>
                    <input
                        type="text"
                        id="irp-lead-name"
                        name="name"
                        value={formData.name}
                        onChange={handleChange}
                        placeholder={__('Ihr Name', 'immobilien-rechner-pro')}
                        autoComplete="name"
                        style={inputStyle}
                    />
                </div>

                <div className="irp-form-group">
                    <label htmlFor="irp-lead-email">
                        {__('E-Mail', 'immobilien-rechner-pro')}
                        <span className="irp-required">*</span>
                    </label>
                    <input
                        type="email"
                        id="irp-lead-email"
                        name="email"
                        value={formData.email}
                        onChange={handleChange}
                        placeholder={__('ihre@email.de', 'immobilien-rechner-pro')}
                        autoComplete="email"
                        required
                        className={errors.email ? 'has-error' : ''}
                        style={inputStyle}
                    />
                    {errors.email && (
                        <span className="irp-error-message">{errors.email}</span>
                    )}
                </div>

                <div className="irp-form-group">
                    <label htmlFor="irp-lead-phone">
                        {__('Telefon', 'immobilien-rechner-pro')}
                    </label>
                    <input
                        type="tel"
                        id="irp-lead-phone"
                        name="phone"
                        value={formData.phone}
                        onChange={handleChange}
                        placeholder={__('Ihre Telefonnummer', 'immobilien-rechner-pro')}
                        autoComplete="tel"
                        style={inputStyle}
                    />
                    <p className="irp-help-text">
                        {__('Optional - für schnellere Rückmeldung', 'immobilien-rechner-pro')}
                    </p>
                </div>

                {settings.requireConsent && (
                    <div className="irp-form-group irp-form-group-checkbox">
                        <label className={errors.consent ? 'has-error' : ''}>
                            <input
                                type="checkbox"
                                name="consent"
                                checked={formData.consent}
                                onChange={handleChange}
                            />
                            <span>
                                {__('Ich stimme der', 'immobilien-rechner-pro')}{' '}
                                <a
                                    href={settings.privacyPolicyUrl || '#'}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    {__('Datenschutzerklärung', 'immobilien-rechner-pro')}
                                </a>
                                {' '}{__('zu und bin mit einer Kontaktaufnahme einverstanden.', 'immobilien-rechner-pro')}
                                <span className="irp-required">*</span>
                            </span>
                        </label>
                        {errors.consent && (
                            <span className="irp-error-message">{errors.consent}</span>
                        )}
                    </div>
                )}

                {submitError && (
                    <div className="irp-error-box">
                        <p>{submitError}</p>
                    </div>
                )}

                <div className="irp-form-actions">
                    <button
                        type="button"
                        className="irp-btn irp-btn-secondary"
                        onClick={onBack}
                        disabled={isSubmitting}
                    >
                        {__('Zurück', 'immobilien-rechner-pro')}
                    </button>

                    <button
                        type="submit"
                        className="irp-btn irp-btn-primary"
                        disabled={isSubmitting}
                    >
                        {isSubmitting ? (
                            <>
                                <span className="irp-loading-spinner-small" />
                                {__('Wird gesendet...', 'immobilien-rechner-pro')}
                            </>
                        ) : (
                            __('Beratung anfordern', 'immobilien-rechner-pro')
                        )}
                    </button>
                </div>
            </motion.form>

            <motion.div
                className="irp-trust-badges"
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                transition={{ delay: 0.4 }}
            >
                <div className="irp-trust-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" width="20" height="20">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                    </svg>
                    <span>{__('Ihre Daten sind sicher', 'immobilien-rechner-pro')}</span>
                </div>
                <div className="irp-trust-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" width="20" height="20">
                        <circle cx="12" cy="12" r="10" />
                        <polyline points="12 6 12 12 16 14" />
                    </svg>
                    <span>{__('Antwort innerhalb 24h', 'immobilien-rechner-pro')}</span>
                </div>
                <div className="irp-trust-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" width="20" height="20">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                    </svg>
                    <span>{__('Unverbindlich', 'immobilien-rechner-pro')}</span>
                </div>
            </motion.div>
        </div>
    );
}
