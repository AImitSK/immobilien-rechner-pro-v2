<?php
/**
 * Zentrale Fehlerbehandlung mit i18n-Unterstuetzung
 *
 * @package Immobilien_Rechner_Pro
 * @since 2.2.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Error Handler Klasse
 *
 * Bietet einheitliche Fehlerbehandlung mit:
 * - Error-Code-System (E1xxx-E5xxx)
 * - Lokalisierte Fehlermeldungen
 * - REST API Response-Konvertierung
 * - Sprachunabhaengiges Logging (Englisch)
 */
class IRP_Error_Handler {

    /**
     * Error-Code-Definitionen mit internen Bezeichnern
     *
     * E1xxx = Validierungsfehler
     * E2xxx = Authentifizierung
     * E3xxx = Datenbank
     * E4xxx = Externe APIs
     * E5xxx = System
     *
     * @var array<string, string>
     */
    private static array $error_codes = [
        // Validierungsfehler (1xxx)
        'E1001' => 'invalid_email',
        'E1002' => 'invalid_phone',
        'E1003' => 'required_field',
        'E1004' => 'invalid_property_size',
        'E1005' => 'invalid_property_type',
        'E1006' => 'invalid_location',
        'E1007' => 'invalid_postal_code',
        'E1008' => 'invalid_year',
        'E1009' => 'invalid_price',
        'E1010' => 'invalid_input_data',

        // Authentifizierung (2xxx)
        'E2001' => 'unauthorized',
        'E2002' => 'session_expired',
        'E2003' => 'invalid_nonce',
        'E2004' => 'recaptcha_failed',
        'E2005' => 'invalid_token',

        // Datenbank (3xxx)
        'E3001' => 'db_connection_failed',
        'E3002' => 'db_query_failed',
        'E3003' => 'lead_not_found',
        'E3004' => 'duplicate_entry',
        'E3005' => 'db_insert_failed',
        'E3006' => 'db_update_failed',
        'E3007' => 'db_delete_failed',

        // Externe APIs (4xxx)
        'E4001' => 'api_connection_failed',
        'E4002' => 'api_rate_limit',
        'E4003' => 'api_invalid_response',
        'E4004' => 'propstack_sync_failed',
        'E4005' => 'geocoding_failed',
        'E4006' => 'email_send_failed',

        // System (5xxx)
        'E5001' => 'file_not_found',
        'E5002' => 'permission_denied',
        'E5003' => 'memory_limit',
        'E5004' => 'timeout',
        'E5005' => 'pdf_generation_failed',
        'E5006' => 'export_failed',
        'E5007' => 'calculation_failed',
    ];

    /**
     * Englische Log-Nachrichten fuer sprachunabhaengiges Logging
     *
     * @var array<string, string>
     */
    private static array $log_messages = [
        // Validierungsfehler
        'E1001' => 'Invalid email address provided',
        'E1002' => 'Invalid phone number provided',
        'E1003' => 'Required field missing',
        'E1004' => 'Invalid property size',
        'E1005' => 'Invalid property type',
        'E1006' => 'Invalid location data',
        'E1007' => 'Invalid postal code',
        'E1008' => 'Invalid year value',
        'E1009' => 'Invalid price value',
        'E1010' => 'Invalid input data',

        // Authentifizierung
        'E2001' => 'Unauthorized access attempt',
        'E2002' => 'Session expired',
        'E2003' => 'Invalid nonce - possible CSRF attempt',
        'E2004' => 'reCAPTCHA verification failed',
        'E2005' => 'Invalid authentication token',

        // Datenbank
        'E3001' => 'Database connection failed',
        'E3002' => 'Database query failed',
        'E3003' => 'Lead not found in database',
        'E3004' => 'Duplicate entry detected',
        'E3005' => 'Database insert operation failed',
        'E3006' => 'Database update operation failed',
        'E3007' => 'Database delete operation failed',

        // Externe APIs
        'E4001' => 'External API connection failed',
        'E4002' => 'API rate limit exceeded',
        'E4003' => 'Invalid response from external API',
        'E4004' => 'Propstack synchronization failed',
        'E4005' => 'Geocoding service failed',
        'E4006' => 'Email sending failed',

        // System
        'E5001' => 'Required file not found',
        'E5002' => 'Permission denied',
        'E5003' => 'Memory limit exceeded',
        'E5004' => 'Operation timed out',
        'E5005' => 'PDF generation failed',
        'E5006' => 'Export operation failed',
        'E5007' => 'Calculation failed',
    ];

    /**
     * Erstellt einen lokalisierten WP_Error
     *
     * @param string $code       Error-Code (z.B. 'E1001')
     * @param array  $context    Kontext-Variablen fuer die Nachricht
     * @param int    $http_status HTTP-Statuscode (default: 400)
     * @return \WP_Error
     */
    public static function create_error(
        string $code,
        array $context = [],
        int $http_status = 400
    ): \WP_Error {
        $message = self::get_message($code, $context);

        return new \WP_Error(
            $code,
            $message,
            [
                'status'  => $http_status,
                'code'    => $code,
                'context' => $context,
            ]
        );
    }

    /**
     * Gibt die lokalisierte Fehlermeldung zurueck
     *
     * @param string $code    Error-Code (z.B. 'E1001')
     * @param array  $context Kontext-Variablen (z.B. ['field_name' => 'E-Mail'])
     * @return string Lokalisierte Fehlermeldung
     */
    public static function get_message(string $code, array $context = []): string {
        $messages = self::get_localized_messages();

        $message = $messages[$code] ?? __('Ein unbekannter Fehler ist aufgetreten.', 'immobilien-rechner-pro');

        // Kontext-Variablen ersetzen (z.B. {field_name})
        foreach ($context as $key => $value) {
            $message = str_replace('{' . $key . '}', esc_html($value), $message);
        }

        return $message;
    }

    /**
     * Gibt alle lokalisierten Fehlermeldungen zurueck
     *
     * @return array<string, string>
     */
    private static function get_localized_messages(): array {
        return [
            // Validierungsfehler (1xxx)
            'E1001' => __('Bitte geben Sie eine gueltige E-Mail-Adresse ein.', 'immobilien-rechner-pro'),
            'E1002' => __('Bitte geben Sie eine gueltige Telefonnummer ein.', 'immobilien-rechner-pro'),
            'E1003' => __('Dieses Feld ist erforderlich.', 'immobilien-rechner-pro'),
            'E1004' => __('Bitte geben Sie eine gueltige Wohnflaeche ein (min. 10 m2).', 'immobilien-rechner-pro'),
            'E1005' => __('Bitte waehlen Sie einen gueltigen Immobilientyp.', 'immobilien-rechner-pro'),
            'E1006' => __('Der angegebene Standort konnte nicht gefunden werden.', 'immobilien-rechner-pro'),
            'E1007' => __('Bitte geben Sie eine gueltige Postleitzahl ein.', 'immobilien-rechner-pro'),
            'E1008' => __('Bitte geben Sie ein gueltiges Jahr ein.', 'immobilien-rechner-pro'),
            'E1009' => __('Bitte geben Sie einen gueltigen Preis ein.', 'immobilien-rechner-pro'),
            'E1010' => __('Die eingegebenen Daten sind ungueltig.', 'immobilien-rechner-pro'),

            // Authentifizierung (2xxx)
            'E2001' => __('Sie haben keine Berechtigung fuer diese Aktion.', 'immobilien-rechner-pro'),
            'E2002' => __('Ihre Sitzung ist abgelaufen. Bitte laden Sie die Seite neu.', 'immobilien-rechner-pro'),
            'E2003' => __('Ungueltige Sicherheitsanfrage. Bitte laden Sie die Seite neu.', 'immobilien-rechner-pro'),
            'E2004' => __('Die Sicherheitsueberpruefung ist fehlgeschlagen. Bitte versuchen Sie es erneut.', 'immobilien-rechner-pro'),
            'E2005' => __('Ungueltiges Authentifizierungs-Token.', 'immobilien-rechner-pro'),

            // Datenbank (3xxx)
            'E3001' => __('Verbindung zur Datenbank fehlgeschlagen. Bitte kontaktieren Sie den Administrator.', 'immobilien-rechner-pro'),
            'E3002' => __('Datenbankfehler. Die Anfrage konnte nicht verarbeitet werden.', 'immobilien-rechner-pro'),
            'E3003' => __('Der angeforderte Eintrag wurde nicht gefunden.', 'immobilien-rechner-pro'),
            'E3004' => __('Ein Eintrag mit diesen Daten existiert bereits.', 'immobilien-rechner-pro'),
            'E3005' => __('Der Eintrag konnte nicht gespeichert werden.', 'immobilien-rechner-pro'),
            'E3006' => __('Der Eintrag konnte nicht aktualisiert werden.', 'immobilien-rechner-pro'),
            'E3007' => __('Der Eintrag konnte nicht geloescht werden.', 'immobilien-rechner-pro'),

            // Externe APIs (4xxx)
            'E4001' => __('Verbindung zum externen Dienst fehlgeschlagen. Bitte versuchen Sie es spaeter erneut.', 'immobilien-rechner-pro'),
            'E4002' => __('Zu viele Anfragen. Bitte warten Sie einen Moment.', 'immobilien-rechner-pro'),
            'E4003' => __('Der externe Dienst hat eine ungueltige Antwort gesendet.', 'immobilien-rechner-pro'),
            'E4004' => __('Die Synchronisierung mit Propstack ist fehlgeschlagen.', 'immobilien-rechner-pro'),
            'E4005' => __('Die Adressermittlung ist fehlgeschlagen.', 'immobilien-rechner-pro'),
            'E4006' => __('Die E-Mail konnte nicht gesendet werden.', 'immobilien-rechner-pro'),

            // System (5xxx)
            'E5001' => __('Die angeforderte Datei wurde nicht gefunden.', 'immobilien-rechner-pro'),
            'E5002' => __('Zugriff verweigert. Sie haben keine Berechtigung.', 'immobilien-rechner-pro'),
            'E5003' => __('Systemressourcen erschoepft. Bitte kontaktieren Sie den Administrator.', 'immobilien-rechner-pro'),
            'E5004' => __('Die Anfrage hat zu lange gedauert. Bitte versuchen Sie es erneut.', 'immobilien-rechner-pro'),
            'E5005' => __('Das PDF konnte nicht erstellt werden.', 'immobilien-rechner-pro'),
            'E5006' => __('Der Export konnte nicht erstellt werden.', 'immobilien-rechner-pro'),
            'E5007' => __('Die Berechnung konnte nicht durchgefuehrt werden.', 'immobilien-rechner-pro'),
        ];
    }

    /**
     * Konvertiert WP_Error in REST-Response
     *
     * @param \WP_Error $error WP_Error-Objekt
     * @return \WP_REST_Response
     */
    public static function to_rest_response(\WP_Error $error): \WP_REST_Response {
        $data   = $error->get_error_data();
        $status = $data['status'] ?? 400;

        return new \WP_REST_Response(
            [
                'success' => false,
                'code'    => $error->get_error_code(),
                'message' => $error->get_error_message(),
                'data'    => $data['context'] ?? null,
            ],
            $status
        );
    }

    /**
     * Logging mit Sprachunabhaengigkeit (immer Englisch im Log)
     *
     * @param string $code    Error-Code
     * @param array  $context Zusaetzlicher Kontext fuer das Log
     * @return void
     */
    public static function log_error(string $code, array $context = []): void {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }

        $log_message = self::$log_messages[$code] ?? "Unknown error: {$code}";
        $context_str = !empty($context) ? ' | Context: ' . wp_json_encode($context) : '';

        error_log("[IRP] [{$code}] {$log_message}{$context_str}");
    }

    /**
     * Prueft ob ein Error-Code existiert
     *
     * @param string $code Error-Code
     * @return bool
     */
    public static function code_exists(string $code): bool {
        return isset(self::$error_codes[$code]);
    }

    /**
     * Gibt den internen Bezeichner fuer einen Error-Code zurueck
     *
     * @param string $code Error-Code
     * @return string|null
     */
    public static function get_error_key(string $code): ?string {
        return self::$error_codes[$code] ?? null;
    }

    /**
     * Erstellt einen Fehler und loggt ihn gleichzeitig
     *
     * @param string $code        Error-Code
     * @param array  $context     Kontext-Variablen
     * @param int    $http_status HTTP-Statuscode
     * @return \WP_Error
     */
    public static function create_and_log_error(
        string $code,
        array $context = [],
        int $http_status = 400
    ): \WP_Error {
        self::log_error($code, $context);
        return self::create_error($code, $context, $http_status);
    }
}
