/**
 * Zentrale Fehlerbehandlung fuer das Frontend
 *
 * @package Immobilien_Rechner_Pro
 * @since 2.2.0
 */

import { __ } from '@wordpress/i18n';

/**
 * Error-Code Konstanten
 *
 * E1xxx = Validierungsfehler
 * E2xxx = Authentifizierung
 * E3xxx = Datenbank
 * E4xxx = Externe APIs
 * E5xxx = System
 */
export const ErrorCodes = {
    // Validierungsfehler (1xxx)
    INVALID_EMAIL: 'E1001',
    INVALID_PHONE: 'E1002',
    REQUIRED_FIELD: 'E1003',
    INVALID_PROPERTY_SIZE: 'E1004',
    INVALID_PROPERTY_TYPE: 'E1005',
    INVALID_LOCATION: 'E1006',
    INVALID_POSTAL_CODE: 'E1007',
    INVALID_YEAR: 'E1008',
    INVALID_PRICE: 'E1009',
    INVALID_INPUT_DATA: 'E1010',

    // Authentifizierung (2xxx)
    UNAUTHORIZED: 'E2001',
    SESSION_EXPIRED: 'E2002',
    INVALID_NONCE: 'E2003',
    RECAPTCHA_FAILED: 'E2004',
    INVALID_TOKEN: 'E2005',

    // Datenbank (3xxx)
    DB_CONNECTION_FAILED: 'E3001',
    DB_QUERY_FAILED: 'E3002',
    LEAD_NOT_FOUND: 'E3003',
    DUPLICATE_ENTRY: 'E3004',
    DB_INSERT_FAILED: 'E3005',
    DB_UPDATE_FAILED: 'E3006',
    DB_DELETE_FAILED: 'E3007',

    // Externe APIs (4xxx)
    API_CONNECTION_FAILED: 'E4001',
    API_RATE_LIMIT: 'E4002',
    API_INVALID_RESPONSE: 'E4003',
    PROPSTACK_SYNC_FAILED: 'E4004',
    GEOCODING_FAILED: 'E4005',
    EMAIL_SEND_FAILED: 'E4006',

    // System (5xxx)
    FILE_NOT_FOUND: 'E5001',
    PERMISSION_DENIED: 'E5002',
    MEMORY_LIMIT: 'E5003',
    TIMEOUT: 'E5004',
    PDF_GENERATION_FAILED: 'E5005',
    EXPORT_FAILED: 'E5006',
    CALCULATION_FAILED: 'E5007',

    // Netzwerk
    NETWORK_ERROR: 'E4001',

    // Allgemein
    UNKNOWN_ERROR: 'E9999',
};

/**
 * Gibt alle lokalisierten Fehlermeldungen zurueck
 *
 * @returns {Object} Objekt mit Error-Codes als Schluessel und Meldungen als Werte
 */
function getLocalizedMessages() {
    return {
        // Validierungsfehler (1xxx)
        E1001: __('Bitte geben Sie eine gueltige E-Mail-Adresse ein.', 'immobilien-rechner-pro'),
        E1002: __('Bitte geben Sie eine gueltige Telefonnummer ein.', 'immobilien-rechner-pro'),
        E1003: __('Dieses Feld ist erforderlich.', 'immobilien-rechner-pro'),
        E1004: __('Bitte geben Sie eine gueltige Wohnflaeche ein (min. 10 m2).', 'immobilien-rechner-pro'),
        E1005: __('Bitte waehlen Sie einen gueltigen Immobilientyp.', 'immobilien-rechner-pro'),
        E1006: __('Der angegebene Standort konnte nicht gefunden werden.', 'immobilien-rechner-pro'),
        E1007: __('Bitte geben Sie eine gueltige Postleitzahl ein.', 'immobilien-rechner-pro'),
        E1008: __('Bitte geben Sie ein gueltiges Jahr ein.', 'immobilien-rechner-pro'),
        E1009: __('Bitte geben Sie einen gueltigen Preis ein.', 'immobilien-rechner-pro'),
        E1010: __('Die eingegebenen Daten sind ungueltig.', 'immobilien-rechner-pro'),

        // Authentifizierung (2xxx)
        E2001: __('Sie haben keine Berechtigung fuer diese Aktion.', 'immobilien-rechner-pro'),
        E2002: __('Ihre Sitzung ist abgelaufen. Bitte laden Sie die Seite neu.', 'immobilien-rechner-pro'),
        E2003: __('Ungueltige Sicherheitsanfrage. Bitte laden Sie die Seite neu.', 'immobilien-rechner-pro'),
        E2004: __('Die Sicherheitsueberpruefung ist fehlgeschlagen. Bitte versuchen Sie es erneut.', 'immobilien-rechner-pro'),
        E2005: __('Ungueltiges Authentifizierungs-Token.', 'immobilien-rechner-pro'),

        // Datenbank (3xxx)
        E3001: __('Verbindung zur Datenbank fehlgeschlagen. Bitte kontaktieren Sie den Administrator.', 'immobilien-rechner-pro'),
        E3002: __('Datenbankfehler. Die Anfrage konnte nicht verarbeitet werden.', 'immobilien-rechner-pro'),
        E3003: __('Der angeforderte Eintrag wurde nicht gefunden.', 'immobilien-rechner-pro'),
        E3004: __('Ein Eintrag mit diesen Daten existiert bereits.', 'immobilien-rechner-pro'),
        E3005: __('Der Eintrag konnte nicht gespeichert werden.', 'immobilien-rechner-pro'),
        E3006: __('Der Eintrag konnte nicht aktualisiert werden.', 'immobilien-rechner-pro'),
        E3007: __('Der Eintrag konnte nicht geloescht werden.', 'immobilien-rechner-pro'),

        // Externe APIs (4xxx)
        E4001: __('Netzwerkfehler. Bitte ueberpruefen Sie Ihre Internetverbindung.', 'immobilien-rechner-pro'),
        E4002: __('Zu viele Anfragen. Bitte warten Sie einen Moment.', 'immobilien-rechner-pro'),
        E4003: __('Der externe Dienst hat eine ungueltige Antwort gesendet.', 'immobilien-rechner-pro'),
        E4004: __('Die Synchronisierung mit Propstack ist fehlgeschlagen.', 'immobilien-rechner-pro'),
        E4005: __('Die Adressermittlung ist fehlgeschlagen.', 'immobilien-rechner-pro'),
        E4006: __('Die E-Mail konnte nicht gesendet werden.', 'immobilien-rechner-pro'),

        // System (5xxx)
        E5001: __('Die angeforderte Datei wurde nicht gefunden.', 'immobilien-rechner-pro'),
        E5002: __('Zugriff verweigert. Sie haben keine Berechtigung.', 'immobilien-rechner-pro'),
        E5003: __('Systemressourcen erschoepft. Bitte kontaktieren Sie den Administrator.', 'immobilien-rechner-pro'),
        E5004: __('Die Anfrage hat zu lange gedauert. Bitte versuchen Sie es erneut.', 'immobilien-rechner-pro'),
        E5005: __('Das PDF konnte nicht erstellt werden.', 'immobilien-rechner-pro'),
        E5006: __('Der Export konnte nicht erstellt werden.', 'immobilien-rechner-pro'),
        E5007: __('Die Berechnung konnte nicht durchgefuehrt werden.', 'immobilien-rechner-pro'),

        // Allgemein
        E9999: __('Ein unbekannter Fehler ist aufgetreten.', 'immobilien-rechner-pro'),
    };
}

/**
 * Gibt die lokalisierte Fehlermeldung zurueck
 *
 * @param {string} code    Error-Code (z.B. 'E1001')
 * @param {Object} context Kontext-Variablen (z.B. { field_name: 'E-Mail' })
 * @returns {string} Lokalisierte Fehlermeldung
 */
export function getErrorMessage(code, context = {}) {
    const messages = getLocalizedMessages();

    let message = messages[code] || messages[ErrorCodes.UNKNOWN_ERROR];

    // Kontext-Variablen ersetzen (z.B. {field_name})
    Object.entries(context).forEach(([key, value]) => {
        message = message.replace(`{${key}}`, value);
    });

    return message;
}

/**
 * Verarbeitet API-Fehler und gibt benutzerfreundliche Meldung zurueck
 *
 * @param {Error|Object} error - Error-Objekt oder API-Response
 * @returns {Object} Objekt mit code und message
 */
export function handleApiError(error) {
    // Server hat strukturierten Fehler zurueckgegeben
    if (error && error.code && error.message) {
        return {
            code: error.code,
            message: error.message,
        };
    }

    // Response-Objekt mit success: false
    if (error && error.success === false && error.code) {
        return {
            code: error.code,
            message: error.message || getErrorMessage(error.code),
        };
    }

    // Netzwerkfehler (fetch failed)
    if (error instanceof TypeError && error.message && error.message.includes('fetch')) {
        return {
            code: ErrorCodes.NETWORK_ERROR,
            message: getErrorMessage(ErrorCodes.NETWORK_ERROR),
        };
    }

    // Timeout (AbortError)
    if (error && error.name === 'AbortError') {
        return {
            code: ErrorCodes.TIMEOUT,
            message: getErrorMessage(ErrorCodes.TIMEOUT),
        };
    }

    // HTTP-Statuscode-basierte Fehler
    if (error && error.status) {
        switch (error.status) {
            case 401:
                return {
                    code: ErrorCodes.UNAUTHORIZED,
                    message: getErrorMessage(ErrorCodes.UNAUTHORIZED),
                };
            case 403:
                return {
                    code: ErrorCodes.PERMISSION_DENIED,
                    message: getErrorMessage(ErrorCodes.PERMISSION_DENIED),
                };
            case 404:
                return {
                    code: ErrorCodes.LEAD_NOT_FOUND,
                    message: getErrorMessage(ErrorCodes.LEAD_NOT_FOUND),
                };
            case 429:
                return {
                    code: ErrorCodes.API_RATE_LIMIT,
                    message: getErrorMessage(ErrorCodes.API_RATE_LIMIT),
                };
            case 500:
            case 502:
            case 503:
                return {
                    code: ErrorCodes.API_CONNECTION_FAILED,
                    message: getErrorMessage(ErrorCodes.API_CONNECTION_FAILED),
                };
        }
    }

    // Unbekannter Fehler
    return {
        code: ErrorCodes.UNKNOWN_ERROR,
        message: getErrorMessage(ErrorCodes.UNKNOWN_ERROR),
    };
}

/**
 * Prueft ob ein Error-Code existiert
 *
 * @param {string} code Error-Code
 * @returns {boolean}
 */
export function isValidErrorCode(code) {
    const messages = getLocalizedMessages();
    return Object.prototype.hasOwnProperty.call(messages, code);
}

/**
 * Erstellt ein strukturiertes Fehlerobjekt
 *
 * @param {string} code    Error-Code
 * @param {Object} context Kontext-Variablen
 * @returns {Object} Strukturiertes Fehlerobjekt
 */
export function createError(code, context = {}) {
    return {
        code: code,
        message: getErrorMessage(code, context),
        context: context,
    };
}

/**
 * Validierungsfehler fuer Formularfelder
 *
 * @param {string} fieldName Feldname fuer die Fehlermeldung
 * @returns {Object} Fehlerobjekt mit REQUIRED_FIELD Code
 */
export function createRequiredFieldError(fieldName) {
    return createError(ErrorCodes.REQUIRED_FIELD, { field_name: fieldName });
}

/**
 * Loggt Fehler in der Konsole (nur im Development-Modus)
 *
 * @param {string} code    Error-Code
 * @param {Object} context Zusaetzlicher Kontext
 * @param {Error}  error   Optionales Error-Objekt
 */
export function logError(code, context = {}, error = null) {
    if (process.env.NODE_ENV === 'development' || window?.irpSettings?.debug) {
        console.error(`[IRP] [${code}]`, {
            message: getErrorMessage(code, context),
            context,
            originalError: error,
        });
    }
}

export default {
    ErrorCodes,
    getErrorMessage,
    handleApiError,
    isValidErrorCode,
    createError,
    createRequiredFieldError,
    logError,
};
