/**
 * Thank You Component
 * Displayed after successful lead submission
 */

import { __ } from '@wordpress/i18n';
import { motion } from 'framer-motion';

export default function ThankYou({ companyName, onStartOver }) {
    return (
        <div className="irp-thank-you">
            <motion.div
                className="irp-thank-you-icon"
                initial={{ scale: 0 }}
                animate={{ scale: 1 }}
                transition={{
                    type: 'spring',
                    stiffness: 200,
                    damping: 15,
                    delay: 0.2
                }}
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                    <circle cx="12" cy="12" r="10" />
                    <polyline points="16 8 10 14 8 12" />
                </svg>
            </motion.div>

            <motion.h2
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.4 }}
            >
                {__('Vielen Dank!', 'immobilien-rechner-pro')}
            </motion.h2>

            <motion.p
                className="irp-thank-you-message"
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.5 }}
            >
                {companyName ? (
                    <>
                        {__('Ihre Anfrage wurde übermittelt.', 'immobilien-rechner-pro')}{' '}
                        <strong>{companyName}</strong>{' '}
                        {__('wird sich in Kürze bei Ihnen melden.', 'immobilien-rechner-pro')}
                    </>
                ) : (
                    __('Ihre Anfrage wurde übermittelt. Wir werden uns in Kürze bei Ihnen melden.', 'immobilien-rechner-pro')
                )}
            </motion.p>

            <motion.div
                className="irp-thank-you-info"
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                transition={{ delay: 0.6 }}
            >
                <div className="irp-info-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" width="24" height="24">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                        <polyline points="22,6 12,13 2,6" />
                    </svg>
                    <div>
                        <strong>{__('Prüfen Sie Ihre E-Mails', 'immobilien-rechner-pro')}</strong>
                        <p>{__('Wir haben Ihnen eine Bestätigung mit Ihren Berechnungsergebnissen gesendet.', 'immobilien-rechner-pro')}</p>
                    </div>
                </div>

                <div className="irp-info-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" width="24" height="24">
                        <circle cx="12" cy="12" r="10" />
                        <polyline points="12 6 12 12 16 14" />
                    </svg>
                    <div>
                        <strong>{__('Wie geht es weiter?', 'immobilien-rechner-pro')}</strong>
                        <p>{__('Ein lokaler Experte wird Sie innerhalb von 24 Stunden kontaktieren, um Ihre Immobilie zu besprechen.', 'immobilien-rechner-pro')}</p>
                    </div>
                </div>
            </motion.div>

            <motion.div
                className="irp-thank-you-actions"
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                transition={{ delay: 0.8 }}
            >
                <button
                    type="button"
                    className="irp-btn irp-btn-secondary"
                    onClick={onStartOver}
                >
                    {__('Weitere Immobilie berechnen', 'immobilien-rechner-pro')}
                </button>
            </motion.div>
        </div>
    );
}
