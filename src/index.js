/**
 * Immobilien Rechner Pro - Main Entry Point
 */

import { createRoot } from '@wordpress/element';
import App from './components/App';
import './styles/main.scss';

// Initialize all calculator instances on the page
document.addEventListener('DOMContentLoaded', () => {
    const containers = document.querySelectorAll('.irp-calculator-root');

    containers.forEach((container) => {
        const root = createRoot(container);

        const config = {
            instanceId: container.dataset.instanceId,
            initialMode: container.dataset.initialMode || '',
            cityId: container.dataset.cityId || '',
            cityName: container.dataset.cityName || '',
            theme: container.dataset.theme || 'light',
            showBranding: container.dataset.showBranding !== 'false',
        };

        root.render(<App config={config} />);
    });
});
