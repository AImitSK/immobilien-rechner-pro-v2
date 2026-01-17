/**
 * Contact Form Step
 * Collects user contact information to complete the lead
 */

import { useState, useEffect, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { motion } from 'framer-motion';
import apiFetch from '@wordpress/api-fetch';

export default function ContactFormStep({ leadId, onComplete, onBack }) {
    const settings = window.irpSettings?.settings || {};
    const recaptchaSiteKey = settings.recaptchaSiteKey || '';

    const [formData, setFormData] = useState({
        name: '',
        email: '',
        phone: '',
        consent: false,
        newsletter_consent: false,
    });
    const [errors, setErrors] = useState({});
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [submitError, setSubmitError] = useState(null);
    const [recaptchaReady, setRecaptchaReady] = useState(!recaptchaSiteKey);

    // Load reCAPTCHA script if configured
    useEffect(() => {
        if (!recaptchaSiteKey) return;

        // Check if already loaded
        if (window.grecaptcha) {
            setRecaptchaReady(true);
            return;
        }

        const script = document.createElement('script');
        script.src = `https://www.google.com/recaptcha/api.js?render=${recaptchaSiteKey}`;
        script.async = true;
        script.onload = () => {
            window.grecaptcha.ready(() => {
                setRecaptchaReady(true);
            });
        };
        document.head.appendChild(script);

        return () => {
            // Cleanup if component unmounts
            if (script.parentNode) {
                script.parentNode.removeChild(script);
            }
        };
    }, [recaptchaSiteKey]);

    // Update form field
    const handleChange = useCallback((field, value) => {
        setFormData((prev) => ({ ...prev, [field]: value }));
        // Clear field error on change
        if (errors[field]) {
            setErrors((prev) => ({ ...prev, [field]: null }));
        }
    }, [errors]);

    // Validate form
    const validate = useCallback(() => {
        const newErrors = {};

        if (!formData.name.trim()) {
            newErrors.name = __('Bitte geben Sie Ihren Namen ein.', 'immobilien-rechner-pro');
        }

        if (!formData.email.trim()) {
            newErrors.email = __('Bitte geben Sie Ihre E-Mail-Adresse ein.', 'immobilien-rechner-pro');
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
            newErrors.email = __('Bitte geben Sie eine gültige E-Mail-Adresse ein.', 'immobilien-rechner-pro');
        }

        if (!formData.consent) {
            newErrors.consent = __('Bitte stimmen Sie der Datenschutzerklärung zu.', 'immobilien-rechner-pro');
        }

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    }, [formData]);

    // Get reCAPTCHA token
    const getRecaptchaToken = useCallback(async () => {
        if (!recaptchaSiteKey || !window.grecaptcha) {
            return null;
        }

        try {
            return await window.grecaptcha.execute(recaptchaSiteKey, { action: 'submit_lead' });
        } catch (err) {
            console.error('reCAPTCHA error:', err);
            return null;
        }
    }, [recaptchaSiteKey]);

    // Submit form
    const handleSubmit = async (e) => {
        e.preventDefault();

        if (!validate()) return;

        setIsSubmitting(true);
        setSubmitError(null);

        try {
            // Get reCAPTCHA token if configured
            const recaptchaToken = await getRecaptchaToken();

            const response = await apiFetch({
                path: '/irp/v1/leads/complete',
                method: 'POST',
                data: {
                    lead_id: leadId,
                    name: formData.name.trim(),
                    email: formData.email.trim(),
                    phone: formData.phone.trim(),
                    consent: formData.consent,
                    newsletter_consent: formData.newsletter_consent,
                    recaptcha_token: recaptchaToken,
                },
            });

            if (response.success) {
                onComplete(response.calculation_data);
            } else {
                setSubmitError(response.message || __('Ein Fehler ist aufgetreten.', 'immobilien-rechner-pro'));
            }
        } catch (err) {
            setSubmitError(err.message || __('Ein Fehler ist aufgetreten.', 'immobilien-rechner-pro'));
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <div className="irp-contact-form-step">
            <div className="irp-contact-form-header">
                <div className="irp-contact-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                    </svg>
                </div>
                <h3>{__('Fast geschafft!', 'immobilien-rechner-pro')}</h3>
                <p>
                    {__('Geben Sie Ihre Kontaktdaten ein, um Ihre persönliche Bewertung zu erhalten.', 'immobilien-rechner-pro')}
                </p>
            </div>

            {submitError && (
                <div className="irp-error-box">
                    <p>{submitError}</p>
                </div>
            )}

            <form onSubmit={handleSubmit} className="irp-contact-form">
                <div className="irp-form-group">
                    <label htmlFor="irp-name">
                        {__('Name', 'immobilien-rechner-pro')}
                        <span className="irp-required">*</span>
                    </label>
                    <input
                        type="text"
                        id="irp-name"
                        value={formData.name}
                        onChange={(e) => handleChange('name', e.target.value)}
                        placeholder={__('Max Mustermann', 'immobilien-rechner-pro')}
                        className={errors.name ? 'has-error' : ''}
                        disabled={isSubmitting}
                    />
                    {errors.name && <span className="irp-error-message">{errors.name}</span>}
                </div>

                <div className="irp-form-group">
                    <label htmlFor="irp-email">
                        {__('E-Mail', 'immobilien-rechner-pro')}
                        <span className="irp-required">*</span>
                    </label>
                    <input
                        type="email"
                        id="irp-email"
                        value={formData.email}
                        onChange={(e) => handleChange('email', e.target.value)}
                        placeholder={__('max@beispiel.de', 'immobilien-rechner-pro')}
                        className={errors.email ? 'has-error' : ''}
                        disabled={isSubmitting}
                    />
                    {errors.email && <span className="irp-error-message">{errors.email}</span>}
                </div>

                <div className="irp-form-group">
                    <label htmlFor="irp-phone">
                        {__('Telefon', 'immobilien-rechner-pro')}
                    </label>
                    <input
                        type="tel"
                        id="irp-phone"
                        value={formData.phone}
                        onChange={(e) => handleChange('phone', e.target.value)}
                        placeholder={__('+49 123 456789', 'immobilien-rechner-pro')}
                        disabled={isSubmitting}
                    />
                </div>

                <div className="irp-form-group-checkbox">
                    <label>
                        <input
                            type="checkbox"
                            checked={formData.consent}
                            onChange={(e) => handleChange('consent', e.target.checked)}
                            disabled={isSubmitting}
                        />
                        <span>
                            {__('Ich stimme der', 'immobilien-rechner-pro')}{' '}
                            {settings.privacyPolicyUrl ? (
                                <a href={settings.privacyPolicyUrl} target="_blank" rel="noopener noreferrer">
                                    {__('Datenschutzerklärung', 'immobilien-rechner-pro')}
                                </a>
                            ) : (
                                __('Datenschutzerklärung', 'immobilien-rechner-pro')
                            )}{' '}
                            {__('zu.', 'immobilien-rechner-pro')}
                            <span className="irp-required">*</span>
                        </span>
                    </label>
                    {errors.consent && <span className="irp-error-message">{errors.consent}</span>}
                </div>

                <div className="irp-form-group-checkbox">
                    <label>
                        <input
                            type="checkbox"
                            checked={formData.newsletter_consent}
                            onChange={(e) => handleChange('newsletter_consent', e.target.checked)}
                            disabled={isSubmitting}
                        />
                        <span>
                            {__('Ich möchte den Newsletter mit Immobilien-Tipps erhalten.', 'immobilien-rechner-pro')}
                        </span>
                    </label>
                </div>

                <div className="irp-form-actions">
                    {onBack && (
                        <button
                            type="button"
                            className="irp-btn irp-btn-secondary"
                            onClick={onBack}
                            disabled={isSubmitting}
                        >
                            {__('Zurück', 'immobilien-rechner-pro')}
                        </button>
                    )}
                    <motion.button
                        type="submit"
                        className="irp-btn irp-btn-primary irp-btn-large"
                        disabled={isSubmitting || (recaptchaSiteKey && !recaptchaReady)}
                        whileHover={{ scale: 1.02 }}
                        whileTap={{ scale: 0.98 }}
                    >
                        {isSubmitting ? (
                            <>
                                <span className="irp-loading-spinner-small" />
                                {__('Wird gesendet...', 'immobilien-rechner-pro')}
                            </>
                        ) : (
                            __('Bewertung anzeigen', 'immobilien-rechner-pro')
                        )}
                    </motion.button>
                </div>
            </form>

            <div className="irp-contact-trust">
                <div className="irp-trust-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" width="16" height="16">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                        <polyline points="9 12 11 14 15 10" />
                    </svg>
                    <span>{__('Ihre Daten sind sicher', 'immobilien-rechner-pro')}</span>
                </div>
                <div className="irp-trust-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" width="16" height="16">
                        <circle cx="12" cy="12" r="10" />
                        <polyline points="12 6 12 12 16 14" />
                    </svg>
                    <span>{__('Kostenlos & unverbindlich', 'immobilien-rechner-pro')}</span>
                </div>
            </div>

            {recaptchaSiteKey && (
                <div className="irp-recaptcha-notice">
                    <small>
                        {__('Diese Seite ist durch reCAPTCHA geschützt. Es gelten die', 'immobilien-rechner-pro')}{' '}
                        <a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer">
                            {__('Datenschutzerklärung', 'immobilien-rechner-pro')}
                        </a>{' '}
                        {__('und', 'immobilien-rechner-pro')}{' '}
                        <a href="https://policies.google.com/terms" target="_blank" rel="noopener noreferrer">
                            {__('Nutzungsbedingungen', 'immobilien-rechner-pro')}
                        </a>{' '}
                        {__('von Google.', 'immobilien-rechner-pro')}
                    </small>
                </div>
            )}
        </div>
    );
}
