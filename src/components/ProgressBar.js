/**
 * Progress Bar Component
 */

import { __ } from '@wordpress/i18n';
import { motion } from 'framer-motion';

export default function ProgressBar({ steps, currentStep }) {
    const progress = ((currentStep + 1) / steps.length) * 100;
    
    return (
        <div className="irp-progress">
            <div className="irp-progress-bar">
                <motion.div
                    className="irp-progress-fill"
                    initial={{ width: 0 }}
                    animate={{ width: `${progress}%` }}
                    transition={{ duration: 0.3, ease: 'easeOut' }}
                />
            </div>
            
            <div className="irp-progress-steps">
                {steps.map((step, index) => (
                    <div
                        key={index}
                        className={`irp-progress-step ${
                            index < currentStep ? 'is-complete' : ''
                        } ${index === currentStep ? 'is-active' : ''}`}
                    >
                        <span className="irp-step-number">
                            {index < currentStep ? '✓' : index + 1}
                        </span>
                        <span className="irp-step-title">{step}</span>
                    </div>
                ))}
            </div>
            
            <div className="irp-progress-info">
                <span>
                    {__('Step', 'immobilien-rechner-pro')} {currentStep + 1} {__('of', 'immobilien-rechner-pro')} {steps.length}
                </span>
            </div>
        </div>
    );
}
