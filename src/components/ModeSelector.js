/**
 * Mode Selector Component
 */

import { __ } from '@wordpress/i18n';
import { motion } from 'framer-motion';

export default function ModeSelector({ onSelect }) {
    const modes = [
        {
            id: 'rental',
            icon: (
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                    <polyline points="9 22 9 12 15 12 15 22" />
                </svg>
            ),
            title: __('Mietwert berechnen', 'immobilien-rechner-pro'),
            description: __('Erfahren Sie, wie viel Miete Sie für Ihre Immobilie verlangen können', 'immobilien-rechner-pro'),
            tagline: __('Ich möchte meine Immobilie vermieten', 'immobilien-rechner-pro'),
        },
        {
            id: 'comparison',
            icon: (
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                    <line x1="12" y1="20" x2="12" y2="10" />
                    <line x1="18" y1="20" x2="18" y2="4" />
                    <line x1="6" y1="20" x2="6" y2="16" />
                </svg>
            ),
            title: __('Verkaufen vs. Vermieten', 'immobilien-rechner-pro'),
            description: __('Vergleichen Sie die finanziellen Ergebnisse von Verkauf und Vermietung', 'immobilien-rechner-pro'),
            tagline: __('Ich bin unsicher, ob ich verkaufen oder vermieten soll', 'immobilien-rechner-pro'),
        },
    ];
    
    const cardVariants = {
        initial: { scale: 1 },
        hover: { scale: 1.02 },
        tap: { scale: 0.98 },
    };
    
    return (
        <div className="irp-mode-selector">
            <div className="irp-mode-header">
                <h2>{__('Was möchten Sie wissen?', 'immobilien-rechner-pro')}</h2>
                <p>{__('Wählen Sie eine Option, um mit Ihrer Immobilienanalyse zu beginnen', 'immobilien-rechner-pro')}</p>
            </div>
            
            <div className="irp-mode-options">
                {modes.map((mode) => (
                    <motion.button
                        key={mode.id}
                        className="irp-mode-card"
                        onClick={() => onSelect(mode.id)}
                        variants={cardVariants}
                        initial="initial"
                        whileHover="hover"
                        whileTap="tap"
                        transition={{ duration: 0.2 }}
                    >
                        <div className="irp-mode-icon">
                            {mode.icon}
                        </div>
                        <h3 className="irp-mode-title">{mode.title}</h3>
                        <p className="irp-mode-description">{mode.description}</p>
                        <span className="irp-mode-tagline">{mode.tagline}</span>
                    </motion.button>
                ))}
            </div>
        </div>
    );
}
