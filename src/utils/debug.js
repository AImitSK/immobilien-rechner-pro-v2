/**
 * Debug logging utility that survives minification
 * Stores logs in window.irpLogs array and uses indirect console reference
 */

// Initialize log storage
if (typeof window !== 'undefined') {
    window.irpLogs = window.irpLogs || [];
}

export function irpDebug(...args) {
    const timestamp = new Date().toISOString();
    const message = args.map(a => typeof a === 'object' ? JSON.stringify(a) : String(a)).join(' ');

    // Store in window for retrieval
    if (typeof window !== 'undefined') {
        window.irpLogs.push({ timestamp, message });
        // Also try to log via indirect reference
        const c = window['con' + 'sole'];
        if (c && typeof c.log === 'function') {
            c.log.apply(c, ['[IRP]', ...args]);
        }
    }
}
