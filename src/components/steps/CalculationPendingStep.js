/**
 * Calculation Pending Step
 * Shows a loading animation while the partial lead is being saved
 */

import { useEffect, useState, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { motion } from 'framer-motion';
import { irpDebug } from '../../utils/debug';

const LOADING_MESSAGES = [
    __('Daten werden analysiert...', 'immobilien-rechner-pro'),
    __('Marktdaten werden abgerufen...', 'immobilien-rechner-pro'),
    __('Mietwert wird berechnet...', 'immobilien-rechner-pro'),
];

const MIN_DISPLAY_TIME = 3000; // Show loading for at least 3 seconds

export default function CalculationPendingStep({ onComplete, error, isReady }) {
    const [messageIndex, setMessageIndex] = useState(0);
    const [minTimeElapsed, setMinTimeElapsed] = useState(false);
    const hasAdvanced = useRef(false);

    // Cycle through loading messages
    useEffect(() => {
        const interval = setInterval(() => {
            setMessageIndex((prev) => (prev + 1) % LOADING_MESSAGES.length);
        }, 1500);

        return () => clearInterval(interval);
    }, []);

    // Track minimum display time
    useEffect(() => {
        const timer = setTimeout(() => {
            setMinTimeElapsed(true);
        }, MIN_DISPLAY_TIME);

        return () => clearTimeout(timer);
    }, []);

    // Debug logging (using irpDebug to survive minification)
    useEffect(() => {
        irpDebug('CalculationPendingStep State:', { minTimeElapsed, isReady, error, hasAdvanced: hasAdvanced.current });
    }, [minTimeElapsed, isReady, error]);

    // Advance when BOTH conditions are met: min time elapsed AND lead is ready
    useEffect(() => {
        irpDebug('Checking advance conditions:', { minTimeElapsed, isReady, error, hasAdvanced: hasAdvanced.current });
        if (minTimeElapsed && isReady && !error && !hasAdvanced.current) {
            irpDebug('Advancing to contact form!');
            hasAdvanced.current = true;
            onComplete?.();
        }
    }, [minTimeElapsed, isReady, error, onComplete]);

    if (error) {
        return (
            <div className="irp-calculation-pending irp-calculation-error">
                <div className="irp-error-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="15" y1="9" x2="9" y2="15" />
                        <line x1="9" y1="9" x2="15" y2="15" />
                    </svg>
                </div>
                <h3>{__('Ein Fehler ist aufgetreten', 'immobilien-rechner-pro')}</h3>
                <p>{error}</p>
            </div>
        );
    }

    return (
        <div className="irp-calculation-pending">
            <div className="irp-pending-animation">
                <motion.div
                    className="irp-pending-circle"
                    animate={{
                        scale: [1, 1.2, 1],
                        opacity: [0.5, 1, 0.5],
                    }}
                    transition={{
                        duration: 2,
                        repeat: Infinity,
                        ease: 'easeInOut',
                    }}
                />
                <motion.div
                    className="irp-pending-circle irp-pending-circle-2"
                    animate={{
                        scale: [1.2, 1, 1.2],
                        opacity: [1, 0.5, 1],
                    }}
                    transition={{
                        duration: 2,
                        repeat: Infinity,
                        ease: 'easeInOut',
                    }}
                />
                <div className="irp-pending-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                        <polyline points="9 22 9 12 15 12 15 22" />
                    </svg>
                </div>
            </div>

            <h3>{__('Ihre Berechnung wird vorbereitet', 'immobilien-rechner-pro')}</h3>

            <motion.p
                key={messageIndex}
                initial={{ opacity: 0, y: 10 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0, y: -10 }}
                className="irp-pending-message"
            >
                {LOADING_MESSAGES[messageIndex]}
            </motion.p>

            <div className="irp-pending-progress">
                <motion.div
                    className="irp-pending-progress-bar"
                    initial={{ width: '0%' }}
                    animate={{ width: '100%' }}
                    transition={{ duration: 3, ease: 'linear' }}
                />
            </div>
        </div>
    );
}
