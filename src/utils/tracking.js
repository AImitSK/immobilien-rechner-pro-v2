/**
 * Google Ads Conversion Tracking Utilities
 */

/**
 * Get tracking settings from WordPress
 */
const getTrackingSettings = () => {
    const settings = window.irpSettings?.settings || {};
    return {
        conversionId: settings.gadsConversionId || '',
        partialLabel: settings.gadsPartialLabel || '',
        completeLabel: settings.gadsCompleteLabel || '',
    };
};

/**
 * Check if gtag is available
 */
const isGtagAvailable = () => {
    return typeof window.gtag === 'function';
};

/**
 * Push event to dataLayer (for GTM)
 */
const pushToDataLayer = (eventName, eventData = {}) => {
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
        event: eventName,
        ...eventData,
    });
};

/**
 * Fire Google Ads conversion
 */
const fireGadsConversion = (conversionId, label) => {
    if (!conversionId || !label) {
        return;
    }

    if (isGtagAvailable()) {
        window.gtag('event', 'conversion', {
            send_to: `${conversionId}/${label}`,
        });
    }
};

/**
 * Track partial lead (calculation completed, no contact info yet)
 * Called when results are displayed
 */
export const trackPartialLead = (data = {}) => {
    const settings = getTrackingSettings();

    // Push to dataLayer for GTM
    pushToDataLayer('irp_partial_lead', {
        lead_mode: data.mode || 'rental',
        lead_city: data.city || '',
        lead_property_type: data.propertyType || '',
    });

    // Fire Google Ads conversion if configured
    fireGadsConversion(settings.conversionId, settings.partialLabel);
};

/**
 * Track complete lead (contact form submitted)
 * Called when lead form is successfully submitted
 */
export const trackCompleteLead = (data = {}) => {
    const settings = getTrackingSettings();

    // Push to dataLayer for GTM
    pushToDataLayer('irp_complete_lead', {
        lead_mode: data.mode || 'rental',
        lead_city: data.city || '',
        lead_property_type: data.propertyType || '',
        lead_id: data.leadId || null,
    });

    // Fire Google Ads conversion if configured
    fireGadsConversion(settings.conversionId, settings.completeLabel);
};
