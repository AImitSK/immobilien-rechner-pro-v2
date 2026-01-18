# Refactoring-Plan: Immobilien Rechner Pro V2

**Version:** 1.0
**Erstellt:** 2026-01-18
**Status:** Geplant
**Priorität:** Hoch

---

## Inhaltsverzeichnis

1. [Übersicht](#1-übersicht)
2. [Phase 1: Internationalisierung (i18n)](#2-phase-1-internationalisierung-i18n)
3. [Phase 2: Einheitliche Fehlerbehandlung](#3-phase-2-einheitliche-fehlerbehandlung)
4. [Phase 3: Sicherheits-Refactoring](#4-phase-3-sicherheits-refactoring)
5. [Phase 4: Code-Qualität](#5-phase-4-code-qualität)
6. [Phase 5: Icon-Management-System](#6-phase-5-icon-management-system)
7. [Implementierungsreihenfolge](#7-implementierungsreihenfolge)
8. [Testplan](#8-testplan)

---

## 1. Übersicht

### Aktuelle Situation

- **Text Domain:** `immobilien-rechner-pro` (bereits definiert)
- **PHP i18n-Aufrufe:** 613 in 16 Dateien
- **JS i18n-Aufrufe:** 432 in 30 Dateien (via `@wordpress/i18n`)
- **Übersetzungsdateien:** Keine vorhanden (.pot/.po/.mo fehlen)
- **Fehlermeldungen:** Gemischt Deutsch/Englisch, inkonsistent

### Ziele

1. Vollständige Internationalisierung mit Deutsch als Standard und Englisch als Alternative
2. Aussagekräftige, einheitliche Fehlermeldungen in der jeweiligen Sprache
3. Behebung kritischer Sicherheitsprobleme
4. Verbesserung der Code-Qualität und Wartbarkeit

---

## 2. Phase 1: Internationalisierung (i18n)

### 2.1 POT-Datei Generierung

**Datei:** `languages/immobilien-rechner-pro.pot`

```bash
# Mit WP-CLI generieren
wp i18n make-pot . languages/immobilien-rechner-pro.pot --domain=immobilien-rechner-pro

# Oder mit npm (für JS-Strings)
npx @wordpress/scripts i18n make-pot . languages/immobilien-rechner-pro.pot
```

### 2.2 Übersetzungsdateien erstellen

| Datei | Sprache | Status |
|-------|---------|--------|
| `languages/immobilien-rechner-pro-de_DE.po` | Deutsch | Zu erstellen |
| `languages/immobilien-rechner-pro-de_DE.mo` | Deutsch (kompiliert) | Zu erstellen |
| `languages/immobilien-rechner-pro-en_US.po` | Englisch | Zu erstellen |
| `languages/immobilien-rechner-pro-en_US.mo` | Englisch (kompiliert) | Zu erstellen |

### 2.3 JavaScript-Übersetzungen

**Neue Datei:** `languages/immobilien-rechner-pro-de_DE-irp-calculator.json`

```bash
# JSON-Übersetzungen für JS generieren
wp i18n make-json languages/immobilien-rechner-pro-de_DE.po --no-purge
```

**Script-Handle registrieren in `class-assets.php`:**

```php
wp_set_script_translations(
    'irp-calculator',
    'immobilien-rechner-pro',
    IRP_PLUGIN_DIR . 'languages'
);
```

### 2.4 String-Kategorisierung

#### Frontend-Strings (React)

| Kategorie | Beispiele | Anzahl ca. |
|-----------|-----------|------------|
| Formular-Labels | "Wohnfläche", "PLZ", "E-Mail" | 80 |
| Buttons | "Weiter", "Zurück", "Berechnen" | 20 |
| Validierung | "Pflichtfeld", "Ungültige E-Mail" | 30 |
| Ergebnisse | "Geschätzter Mietwert", "Verkaufspreis" | 50 |
| Fehler | "Netzwerkfehler", "Berechnung fehlgeschlagen" | 25 |

#### Backend-Strings (PHP)

| Kategorie | Beispiele | Anzahl ca. |
|-----------|-----------|------------|
| Admin-UI | "Einstellungen", "Leads verwalten" | 150 |
| Fehlermeldungen | "Ungültige E-Mail", "Keine Berechtigung" | 80 |
| E-Mail-Templates | Betreffzeilen, Grußformeln | 30 |
| API-Responses | Error-Codes, Success-Messages | 50 |

### 2.5 Übersetzungstabelle (Auszug)

#### Fehlermeldungen

| Englisch (Quelltext) | Deutsch |
|---------------------|---------|
| `Invalid email address` | `Bitte geben Sie eine gültige E-Mail-Adresse ein.` |
| `Field is required` | `Dieses Feld ist erforderlich.` |
| `Calculation failed` | `Die Berechnung konnte nicht durchgeführt werden.` |
| `Network error` | `Netzwerkfehler. Bitte versuchen Sie es erneut.` |
| `Unauthorized access` | `Sie haben keine Berechtigung für diese Aktion.` |
| `Lead not found` | `Der Lead wurde nicht gefunden.` |
| `Invalid property size` | `Bitte geben Sie eine gültige Wohnfläche ein (min. 10 m²).` |
| `Session expired` | `Ihre Sitzung ist abgelaufen. Bitte laden Sie die Seite neu.` |
| `reCAPTCHA verification failed` | `Die Sicherheitsüberprüfung ist fehlgeschlagen.` |
| `Database error` | `Datenbankfehler. Bitte kontaktieren Sie den Administrator.` |
| `API connection failed` | `Verbindung zur API fehlgeschlagen.` |
| `Export failed` | `Der Export konnte nicht erstellt werden.` |
| `Invalid input data` | `Die eingegebenen Daten sind ungültig.` |
| `File upload failed` | `Der Datei-Upload ist fehlgeschlagen.` |
| `Rate limit exceeded` | `Zu viele Anfragen. Bitte warten Sie einen Moment.` |

#### UI-Elemente

| Englisch | Deutsch |
|----------|---------|
| `Next` | `Weiter` |
| `Back` | `Zurück` |
| `Calculate` | `Berechnen` |
| `Submit` | `Absenden` |
| `Cancel` | `Abbrechen` |
| `Save` | `Speichern` |
| `Delete` | `Löschen` |
| `Edit` | `Bearbeiten` |
| `Export` | `Exportieren` |
| `Settings` | `Einstellungen` |
| `Dashboard` | `Dashboard` |
| `Leads` | `Kontakte` |
| `Loading...` | `Wird geladen...` |
| `No results found` | `Keine Ergebnisse gefunden.` |

---

## 3. Phase 2: Einheitliche Fehlerbehandlung

### 3.1 Error-Code-System

**Neue Datei:** `includes/class-error-handler.php`

```php
<?php
/**
 * Zentrale Fehlerbehandlung mit i18n-Unterstützung
 */
class IRP_Error_Handler {

    /**
     * Error-Code-Definitionen mit übersetzbaren Nachrichten
     */
    private static array $error_codes = [
        // Validierungsfehler (1xxx)
        'E1001' => 'invalid_email',
        'E1002' => 'invalid_phone',
        'E1003' => 'required_field',
        'E1004' => 'invalid_property_size',
        'E1005' => 'invalid_property_type',
        'E1006' => 'invalid_location',

        // Authentifizierung (2xxx)
        'E2001' => 'unauthorized',
        'E2002' => 'session_expired',
        'E2003' => 'invalid_nonce',
        'E2004' => 'recaptcha_failed',

        // Datenbank (3xxx)
        'E3001' => 'db_connection_failed',
        'E3002' => 'db_query_failed',
        'E3003' => 'lead_not_found',
        'E3004' => 'duplicate_entry',

        // Externe APIs (4xxx)
        'E4001' => 'api_connection_failed',
        'E4002' => 'api_rate_limit',
        'E4003' => 'api_invalid_response',
        'E4004' => 'propstack_sync_failed',

        // System (5xxx)
        'E5001' => 'file_not_found',
        'E5002' => 'permission_denied',
        'E5003' => 'memory_limit',
        'E5004' => 'timeout',
    ];

    /**
     * Erstellt einen lokalisierten WP_Error
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
                'status' => $http_status,
                'code' => $code,
                'context' => $context,
            ]
        );
    }

    /**
     * Gibt die lokalisierte Fehlermeldung zurück
     */
    public static function get_message(string $code, array $context = []): string {
        $messages = [
            // Validierungsfehler
            'E1001' => __('Bitte geben Sie eine gültige E-Mail-Adresse ein.', 'immobilien-rechner-pro'),
            'E1002' => __('Bitte geben Sie eine gültige Telefonnummer ein.', 'immobilien-rechner-pro'),
            'E1003' => __('Dieses Feld ist erforderlich.', 'immobilien-rechner-pro'),
            'E1004' => __('Bitte geben Sie eine gültige Wohnfläche ein (min. 10 m²).', 'immobilien-rechner-pro'),
            'E1005' => __('Bitte wählen Sie einen gültigen Immobilientyp.', 'immobilien-rechner-pro'),
            'E1006' => __('Der angegebene Standort konnte nicht gefunden werden.', 'immobilien-rechner-pro'),

            // Authentifizierung
            'E2001' => __('Sie haben keine Berechtigung für diese Aktion.', 'immobilien-rechner-pro'),
            'E2002' => __('Ihre Sitzung ist abgelaufen. Bitte laden Sie die Seite neu.', 'immobilien-rechner-pro'),
            'E2003' => __('Ungültige Sicherheitsanfrage. Bitte laden Sie die Seite neu.', 'immobilien-rechner-pro'),
            'E2004' => __('Die Sicherheitsüberprüfung ist fehlgeschlagen. Bitte versuchen Sie es erneut.', 'immobilien-rechner-pro'),

            // Datenbank
            'E3001' => __('Verbindung zur Datenbank fehlgeschlagen. Bitte kontaktieren Sie den Administrator.', 'immobilien-rechner-pro'),
            'E3002' => __('Datenbankfehler. Die Anfrage konnte nicht verarbeitet werden.', 'immobilien-rechner-pro'),
            'E3003' => __('Der angeforderte Eintrag wurde nicht gefunden.', 'immobilien-rechner-pro'),
            'E3004' => __('Ein Eintrag mit diesen Daten existiert bereits.', 'immobilien-rechner-pro'),

            // Externe APIs
            'E4001' => __('Verbindung zum externen Dienst fehlgeschlagen. Bitte versuchen Sie es später erneut.', 'immobilien-rechner-pro'),
            'E4002' => __('Zu viele Anfragen. Bitte warten Sie einen Moment.', 'immobilien-rechner-pro'),
            'E4003' => __('Der externe Dienst hat eine ungültige Antwort gesendet.', 'immobilien-rechner-pro'),
            'E4004' => __('Die Synchronisierung mit Propstack ist fehlgeschlagen.', 'immobilien-rechner-pro'),

            // System
            'E5001' => __('Die angeforderte Datei wurde nicht gefunden.', 'immobilien-rechner-pro'),
            'E5002' => __('Zugriff verweigert. Sie haben keine Berechtigung.', 'immobilien-rechner-pro'),
            'E5003' => __('Systemressourcen erschöpft. Bitte kontaktieren Sie den Administrator.', 'immobilien-rechner-pro'),
            'E5004' => __('Die Anfrage hat zu lange gedauert. Bitte versuchen Sie es erneut.', 'immobilien-rechner-pro'),
        ];

        $message = $messages[$code] ?? __('Ein unbekannter Fehler ist aufgetreten.', 'immobilien-rechner-pro');

        // Kontext-Variablen ersetzen (z.B. {field_name})
        foreach ($context as $key => $value) {
            $message = str_replace('{' . $key . '}', $value, $message);
        }

        return $message;
    }

    /**
     * Konvertiert WP_Error in REST-Response
     */
    public static function to_rest_response(\WP_Error $error): \WP_REST_Response {
        $data = $error->get_error_data();
        $status = $data['status'] ?? 400;

        return new \WP_REST_Response([
            'success' => false,
            'code' => $error->get_error_code(),
            'message' => $error->get_error_message(),
            'data' => $data['context'] ?? null,
        ], $status);
    }

    /**
     * Logging mit Sprachunabhängigkeit (immer Englisch im Log)
     */
    public static function log_error(string $code, array $context = []): void {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }

        $log_messages = [
            'E1001' => 'Invalid email address provided',
            'E2001' => 'Unauthorized access attempt',
            'E3001' => 'Database connection failed',
            'E4001' => 'External API connection failed',
            // ... weitere englische Log-Messages
        ];

        $log_message = $log_messages[$code] ?? "Error: {$code}";
        $context_str = !empty($context) ? ' | Context: ' . wp_json_encode($context) : '';

        error_log("[IRP] {$log_message}{$context_str}");
    }
}
```

### 3.2 Frontend Error-Handler

**Neue Datei:** `src/utils/errorHandler.js`

```javascript
import { __ } from '@wordpress/i18n';

/**
 * Zentrale Fehlerbehandlung für das Frontend
 */
export const ErrorCodes = {
    // Validierung
    INVALID_EMAIL: 'E1001',
    INVALID_PHONE: 'E1002',
    REQUIRED_FIELD: 'E1003',
    INVALID_PROPERTY_SIZE: 'E1004',

    // Netzwerk
    NETWORK_ERROR: 'E4001',
    TIMEOUT: 'E5004',

    // Allgemein
    UNKNOWN_ERROR: 'E9999',
};

/**
 * Gibt die lokalisierte Fehlermeldung zurück
 */
export function getErrorMessage(code, context = {}) {
    const messages = {
        E1001: __('Bitte geben Sie eine gültige E-Mail-Adresse ein.', 'immobilien-rechner-pro'),
        E1002: __('Bitte geben Sie eine gültige Telefonnummer ein.', 'immobilien-rechner-pro'),
        E1003: __('Dieses Feld ist erforderlich.', 'immobilien-rechner-pro'),
        E1004: __('Bitte geben Sie eine gültige Wohnfläche ein (min. 10 m²).', 'immobilien-rechner-pro'),
        E4001: __('Netzwerkfehler. Bitte überprüfen Sie Ihre Internetverbindung.', 'immobilien-rechner-pro'),
        E5004: __('Die Anfrage hat zu lange gedauert. Bitte versuchen Sie es erneut.', 'immobilien-rechner-pro'),
        E9999: __('Ein unbekannter Fehler ist aufgetreten.', 'immobilien-rechner-pro'),
    };

    let message = messages[code] || messages.E9999;

    // Kontext-Variablen ersetzen
    Object.entries(context).forEach(([key, value]) => {
        message = message.replace(`{${key}}`, value);
    });

    return message;
}

/**
 * Verarbeitet API-Fehler und gibt benutzerfreundliche Meldung zurück
 */
export function handleApiError(error) {
    // Server hat strukturierten Fehler zurückgegeben
    if (error.code && error.message) {
        return {
            code: error.code,
            message: error.message,
        };
    }

    // Netzwerkfehler
    if (error.name === 'TypeError' && error.message.includes('fetch')) {
        return {
            code: ErrorCodes.NETWORK_ERROR,
            message: getErrorMessage(ErrorCodes.NETWORK_ERROR),
        };
    }

    // Timeout
    if (error.name === 'AbortError') {
        return {
            code: ErrorCodes.TIMEOUT,
            message: getErrorMessage(ErrorCodes.TIMEOUT),
        };
    }

    // Unbekannter Fehler
    return {
        code: ErrorCodes.UNKNOWN_ERROR,
        message: getErrorMessage(ErrorCodes.UNKNOWN_ERROR),
    };
}
```

### 3.3 Refactoring bestehender Fehlerbehandlung

**Zu ändern in `class-rest-api.php`:**

```php
// VORHER:
return new \WP_REST_Response([
    'success' => false,
    'message' => 'Invalid email'
], 400);

// NACHHER:
return IRP_Error_Handler::to_rest_response(
    IRP_Error_Handler::create_error('E1001')
);
```

**Zu ändern in `class-leads.php`:**

```php
// VORHER:
return new \WP_Error('invalid_email', __('Bitte geben Sie eine gültige E-Mail-Adresse an.', 'immobilien-rechner-pro'));

// NACHHER:
return IRP_Error_Handler::create_error('E1001');
```

---

## 4. Phase 3: Sicherheits-Refactoring

### 4.1 SQL-Injection Prevention

**Datei:** `includes/class-activator.php`

```php
// VORHER (unsicher):
$wpdb->query("ALTER TABLE {$leads_table} ADD COLUMN status varchar(20)");

// NACHHER (sicher):
$table_name = esc_sql($wpdb->prefix . 'irp_leads');
$wpdb->query(
    $wpdb->prepare(
        "ALTER TABLE %i ADD COLUMN status varchar(20) NOT NULL DEFAULT 'complete'",
        $table_name
    )
);
```

### 4.2 Admin-AJAX Sicherheit

**Datei:** `admin/class-admin.php`

```php
// VORHER:
public function ajax_export_leads(): void {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    check_ajax_referer('irp_export_leads', 'nonce');
    // ...
}

// NACHHER:
public function ajax_export_leads(): void {
    // 1. Capability-Check ZUERST
    if (!current_user_can('manage_options')) {
        wp_send_json_error(
            IRP_Error_Handler::create_error('E2001')->get_error_message()
        );
        return;
    }

    // 2. Nonce-Check
    if (!check_ajax_referer('irp_export_leads', 'nonce', false)) {
        wp_send_json_error(
            IRP_Error_Handler::create_error('E2003')->get_error_message()
        );
        return;
    }

    // ...
}
```

### 4.3 Input-Validierung verschärfen

**Datei:** `includes/class-rest-api.php`

```php
// VORHER:
'property_size' => [
    'required' => false,
    'type' => 'number',
],

// NACHHER:
'property_size' => [
    'required' => false,
    'type' => 'number',
    'minimum' => 10,
    'maximum' => 10000,
    'validate_callback' => function($value) {
        if ($value !== null && ($value < 10 || $value > 10000)) {
            return IRP_Error_Handler::create_error('E1004');
        }
        return true;
    },
],
```

### 4.4 Race Condition Fix

**Datei:** `includes/class-leads.php`

```php
/**
 * Vervollständigt einen Partial Lead mit Transaction-Lock
 */
public function complete(int $lead_id, array $data): bool|\WP_Error {
    global $wpdb;

    $wpdb->query('START TRANSACTION');

    try {
        // SELECT FOR UPDATE - sperrt die Zeile
        $lead = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE id = %d FOR UPDATE",
                $lead_id
            )
        );

        if (!$lead) {
            $wpdb->query('ROLLBACK');
            return IRP_Error_Handler::create_error('E3003');
        }

        if ($lead->status !== 'partial') {
            $wpdb->query('ROLLBACK');
            return IRP_Error_Handler::create_error('E3004', [
                'reason' => 'Lead already completed'
            ]);
        }

        // Update durchführen
        $result = $wpdb->update(
            $this->table_name,
            [
                'email' => sanitize_email($data['email']),
                'name' => sanitize_text_field($data['name']),
                'phone' => sanitize_text_field($data['phone'] ?? ''),
                'status' => 'complete',
            ],
            ['id' => $lead_id],
            ['%s', '%s', '%s', '%s'],
            ['%d']
        );

        if ($result === false) {
            $wpdb->query('ROLLBACK');
            return IRP_Error_Handler::create_error('E3002');
        }

        $wpdb->query('COMMIT');
        return true;

    } catch (\Exception $e) {
        $wpdb->query('ROLLBACK');
        IRP_Error_Handler::log_error('E3002', ['exception' => $e->getMessage()]);
        return IRP_Error_Handler::create_error('E3002');
    }
}
```

### 4.5 Debug-Logging konditionieren

**Datei:** `includes/class-rest-api.php`

```php
// VORHER:
error_log('[IRP] create_partial_lead called');
error_log('[IRP] Params: ' . print_r($params, true));

// NACHHER:
if (defined('WP_DEBUG') && WP_DEBUG && defined('IRP_DEBUG') && IRP_DEBUG) {
    IRP_Error_Handler::log_error('DEBUG', [
        'action' => 'create_partial_lead',
        'params' => array_keys($params), // Nur Keys, keine Werte
    ]);
}
```

### 4.6 XSS-Prävention in Admin-Views

**Datei:** `admin/views/leads-list.php`

```javascript
// VORHER:
alert(response.data.message);

// NACHHER:
// Verwende eine sichere Notification-Methode
const showNotification = (message, type = 'info') => {
    const container = document.getElementById('irp-notifications');
    const notification = document.createElement('div');
    notification.className = `notice notice-${type} is-dismissible`;
    notification.textContent = message; // textContent statt innerHTML
    container.appendChild(notification);
};

showNotification(response.data.message, response.success ? 'success' : 'error');
```

---

## 5. Phase 4: Code-Qualität

### 5.1 Konstanten definieren

**Datei:** `includes/class-calculator.php`

```php
// VORHER:
$rent_low = $monthly_rent * 0.85;
$rent_high = $monthly_rent * 1.15;

// NACHHER:
private const VARIANCE_LOW = 0.85;
private const VARIANCE_HIGH = 1.15;

$rent_low = $monthly_rent * self::VARIANCE_LOW;
$rent_high = $monthly_rent * self::VARIANCE_HIGH;
```

### 5.2 extract() entfernen

**Datei:** `includes/class-email.php`

```php
// VORHER:
extract($template_vars);
include $template_path;

// NACHHER:
// Template-Variablen direkt verfügbar machen
$vars = $template_vars;
include $template_path;

// Im Template dann: $vars['key'] statt $key
```

### 5.3 N+1 Query optimieren

**Datei:** `admin/views/leads-list.php`

```php
// VORHER (in Schleife):
foreach ($leads as $lead) {
    $propstack_status = IRP_Propstack::get_sync_status($lead);
}

// NACHHER (Batch-Load):
$lead_ids = array_column($leads, 'id');
$propstack_statuses = IRP_Propstack::get_sync_statuses_batch($lead_ids);

foreach ($leads as $lead) {
    $propstack_status = $propstack_statuses[$lead->id] ?? null;
}
```

### 5.4 CSV-Export mit Streaming

**Datei:** `includes/class-leads.php`

```php
public function export_csv_streaming(array $args = []): void {
    $per_page = 100;
    $page = 1;

    // Header senden
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="leads-export.csv"');

    $output = fopen('php://output', 'w');

    // BOM für Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Header-Zeile
    fputcsv($output, ['ID', 'Name', 'E-Mail', 'Telefon', 'Erstellt']);

    // Batched Export
    do {
        $leads = $this->get_all(array_merge($args, [
            'per_page' => $per_page,
            'page' => $page,
        ]));

        foreach ($leads as $lead) {
            fputcsv($output, [
                $lead->id,
                $lead->name,
                $lead->email,
                $lead->phone,
                $lead->created_at,
            ]);
        }

        $page++;

        // Memory freigeben
        unset($leads);

    } while (count($leads) === $per_page);

    fclose($output);
    exit;
}
```

---

## 6. Phase 5: Icon-Management-System

### 6.1 Aktuelle Situation

Die Icons sind derzeit unstrukturiert verteilt:

```
assets/
├── images/           # Alte Icons (unstrukturiert)
│   ├── balkon.svg
│   ├── terrasse.svg
│   ├── wohnung.svg
│   └── ...
└── icon/             # Teilweise strukturiert
    ├── haustypen/
    ├── ausstattung/
    ├── qualitaetsstufen/
    └── ...
```

**Probleme:**
- Doppelte Icons in `assets/images/` und `assets/icon/`
- Inline-SVGs direkt in React-Komponenten (nicht wiederverwendbar)
- Keine zentrale Verwaltung für mehrfach verwendete Icons
- Icon-Änderungen erfordern Änderungen an mehreren Stellen

### 6.2 Zielstruktur: Zentrale Icon-Bibliothek

Die neue Struktur organisiert Icons nach **Verwendungskontext (Tabs/Steps)** mit einer zentralen **Quell-Bibliothek**:

```
assets/
└── icons/
    ├── _source/                    # Zentrale Quell-Icons (Single Source of Truth)
    │   ├── property/
    │   │   ├── apartment.svg
    │   │   ├── house.svg
    │   │   ├── commercial.svg
    │   │   └── land.svg
    │   ├── house-types/
    │   │   ├── single-family.svg
    │   │   ├── semi-detached.svg
    │   │   ├── townhouse.svg
    │   │   ├── bungalow.svg
    │   │   └── multi-family.svg
    │   ├── features/
    │   │   ├── balcony.svg
    │   │   ├── terrace.svg
    │   │   ├── garden.svg
    │   │   ├── elevator.svg
    │   │   ├── parking.svg
    │   │   ├── garage.svg
    │   │   ├── cellar.svg
    │   │   ├── kitchen.svg
    │   │   ├── floor-heating.svg
    │   │   ├── guest-toilet.svg
    │   │   ├── barrier-free.svg
    │   │   ├── solar.svg
    │   │   ├── fireplace.svg
    │   │   ├── parquet.svg
    │   │   └── attic.svg
    │   ├── condition/
    │   │   ├── new.svg
    │   │   ├── renovated.svg
    │   │   ├── good.svg
    │   │   └── repairs-needed.svg
    │   ├── quality/
    │   │   ├── simple.svg
    │   │   ├── normal.svg
    │   │   ├── premium.svg
    │   │   └── luxury.svg
    │   ├── usage/
    │   │   ├── owner-occupied.svg
    │   │   ├── rented.svg
    │   │   ├── vacant.svg
    │   │   ├── buy.svg
    │   │   └── sell.svg
    │   ├── ui/
    │   │   ├── check.svg
    │   │   ├── arrow-left.svg
    │   │   ├── arrow-right.svg
    │   │   ├── info.svg
    │   │   ├── warning.svg
    │   │   ├── error.svg
    │   │   ├── success.svg
    │   │   ├── download.svg
    │   │   ├── email.svg
    │   │   ├── phone.svg
    │   │   └── location.svg
    │   └── timeline/
    │       └── calendar.svg
    │
    ├── steps/                      # Icons nach Calculator-Steps organisiert
    │   ├── property-type/          # PropertyTypeStep
    │   │   ├── apartment.svg       → Symlink zu _source/property/apartment.svg
    │   │   ├── house.svg           → Symlink zu _source/property/house.svg
    │   │   └── commercial.svg      → Symlink zu _source/property/commercial.svg
    │   ├── house-type/             # (Nur wenn Haus gewählt)
    │   │   ├── single-family.svg   → Symlink
    │   │   ├── semi-detached.svg   → Symlink
    │   │   └── ...
    │   ├── features/               # FeaturesStep
    │   │   ├── balcony.svg         → Symlink
    │   │   ├── terrace.svg         → Symlink
    │   │   └── ...
    │   ├── condition/              # ConditionStep
    │   │   └── ...
    │   ├── quality/                # QualityStep
    │   │   └── ...
    │   ├── location/               # LocationStep
    │   │   └── location.svg
    │   ├── contact/                # ContactFormStep
    │   │   ├── email.svg
    │   │   ├── phone.svg
    │   │   └── check.svg
    │   └── results/                # ResultsDisplay
    │       ├── download.svg
    │       └── success.svg
    │
    └── admin/                      # Admin-Bereich Icons
        ├── dashboard/
        ├── leads/
        ├── settings/
        └── integrations/
```

### 6.3 Vorteile dieser Struktur

| Vorteil | Beschreibung |
|---------|--------------|
| **Single Source of Truth** | Jedes Icon existiert nur einmal in `_source/` |
| **Kontextuelle Organisation** | Icons in `steps/` entsprechen den UI-Bereichen |
| **Einfache Änderungen** | Icon in `_source/` ändern → wirkt überall |
| **Übersichtlichkeit** | Entwickler finden Icons nach Verwendungskontext |
| **Keine Duplikate** | Symlinks/Re-Exports verhindern doppelte Dateien |

### 6.4 Implementierung: JavaScript Icon-Registry

**Neue Datei:** `src/icons/index.js`

```javascript
/**
 * Zentrale Icon-Registry
 *
 * Icons werden aus der _source/ Bibliothek importiert und
 * nach Verwendungskontext re-exportiert.
 */

const ICON_BASE_URL = window.irpSettings?.pluginUrl + 'assets/icons/_source/';

// ============================================
// QUELL-ICONS (Single Source of Truth)
// ============================================

export const SourceIcons = {
    // Property Types
    property: {
        apartment: `${ICON_BASE_URL}property/apartment.svg`,
        house: `${ICON_BASE_URL}property/house.svg`,
        commercial: `${ICON_BASE_URL}property/commercial.svg`,
        land: `${ICON_BASE_URL}property/land.svg`,
    },

    // House Types
    houseTypes: {
        singleFamily: `${ICON_BASE_URL}house-types/single-family.svg`,
        semiDetached: `${ICON_BASE_URL}house-types/semi-detached.svg`,
        townhouseEnd: `${ICON_BASE_URL}house-types/townhouse-end.svg`,
        townhouseMiddle: `${ICON_BASE_URL}house-types/townhouse-middle.svg`,
        bungalow: `${ICON_BASE_URL}house-types/bungalow.svg`,
        multiFamily: `${ICON_BASE_URL}house-types/multi-family.svg`,
    },

    // Features
    features: {
        balcony: `${ICON_BASE_URL}features/balcony.svg`,
        terrace: `${ICON_BASE_URL}features/terrace.svg`,
        garden: `${ICON_BASE_URL}features/garden.svg`,
        elevator: `${ICON_BASE_URL}features/elevator.svg`,
        parking: `${ICON_BASE_URL}features/parking.svg`,
        garage: `${ICON_BASE_URL}features/garage.svg`,
        cellar: `${ICON_BASE_URL}features/cellar.svg`,
        kitchen: `${ICON_BASE_URL}features/kitchen.svg`,
        floorHeating: `${ICON_BASE_URL}features/floor-heating.svg`,
        guestToilet: `${ICON_BASE_URL}features/guest-toilet.svg`,
        barrierFree: `${ICON_BASE_URL}features/barrier-free.svg`,
        solar: `${ICON_BASE_URL}features/solar.svg`,
        fireplace: `${ICON_BASE_URL}features/fireplace.svg`,
        parquet: `${ICON_BASE_URL}features/parquet.svg`,
        attic: `${ICON_BASE_URL}features/attic.svg`,
    },

    // Condition
    condition: {
        new: `${ICON_BASE_URL}condition/new.svg`,
        renovated: `${ICON_BASE_URL}condition/renovated.svg`,
        good: `${ICON_BASE_URL}condition/good.svg`,
        repairsNeeded: `${ICON_BASE_URL}condition/repairs-needed.svg`,
    },

    // Quality
    quality: {
        simple: `${ICON_BASE_URL}quality/simple.svg`,
        normal: `${ICON_BASE_URL}quality/normal.svg`,
        premium: `${ICON_BASE_URL}quality/premium.svg`,
        luxury: `${ICON_BASE_URL}quality/luxury.svg`,
    },

    // Usage
    usage: {
        ownerOccupied: `${ICON_BASE_URL}usage/owner-occupied.svg`,
        rented: `${ICON_BASE_URL}usage/rented.svg`,
        vacant: `${ICON_BASE_URL}usage/vacant.svg`,
        buy: `${ICON_BASE_URL}usage/buy.svg`,
        sell: `${ICON_BASE_URL}usage/sell.svg`,
    },

    // UI Icons
    ui: {
        check: `${ICON_BASE_URL}ui/check.svg`,
        arrowLeft: `${ICON_BASE_URL}ui/arrow-left.svg`,
        arrowRight: `${ICON_BASE_URL}ui/arrow-right.svg`,
        info: `${ICON_BASE_URL}ui/info.svg`,
        warning: `${ICON_BASE_URL}ui/warning.svg`,
        error: `${ICON_BASE_URL}ui/error.svg`,
        success: `${ICON_BASE_URL}ui/success.svg`,
        download: `${ICON_BASE_URL}ui/download.svg`,
        email: `${ICON_BASE_URL}ui/email.svg`,
        phone: `${ICON_BASE_URL}ui/phone.svg`,
        location: `${ICON_BASE_URL}ui/location.svg`,
    },
};

// ============================================
// STEP-BASIERTE ICON-ZUORDNUNG
// ============================================

/**
 * Icons für PropertyTypeStep
 * Referenziert die Quell-Icons
 */
export const PropertyTypeStepIcons = {
    apartment: SourceIcons.property.apartment,
    house: SourceIcons.property.house,
    commercial: SourceIcons.property.commercial,
};

/**
 * Icons für HouseTypeStep (Sale Calculator)
 */
export const HouseTypeStepIcons = {
    singleFamily: SourceIcons.houseTypes.singleFamily,
    semiDetached: SourceIcons.houseTypes.semiDetached,
    townhouseEnd: SourceIcons.houseTypes.townhouseEnd,
    townhouseMiddle: SourceIcons.houseTypes.townhouseMiddle,
    bungalow: SourceIcons.houseTypes.bungalow,
    multiFamily: SourceIcons.houseTypes.multiFamily,
};

/**
 * Icons für FeaturesStep
 */
export const FeaturesStepIcons = {
    balcony: SourceIcons.features.balcony,
    terrace: SourceIcons.features.terrace,
    garden: SourceIcons.features.garden,
    elevator: SourceIcons.features.elevator,
    parking: SourceIcons.features.parking,
    garage: SourceIcons.features.garage,
    cellar: SourceIcons.features.cellar,
    fitted_kitchen: SourceIcons.features.kitchen,
    floor_heating: SourceIcons.features.floorHeating,
    guest_toilet: SourceIcons.features.guestToilet,
    barrier_free: SourceIcons.features.barrierFree,
};

/**
 * Icons für ConditionStep
 */
export const ConditionStepIcons = {
    new_building: SourceIcons.condition.new,
    renovated: SourceIcons.condition.renovated,
    good: SourceIcons.condition.good,
    needs_renovation: SourceIcons.condition.repairsNeeded,
};

/**
 * Icons für QualityStep (Sale Calculator)
 */
export const QualityStepIcons = {
    simple: SourceIcons.quality.simple,
    normal: SourceIcons.quality.normal,
    premium: SourceIcons.quality.premium,
    luxury: SourceIcons.quality.luxury,
};

/**
 * Icons für UsageStep (Sale Calculator)
 */
export const UsageStepIcons = {
    owner_occupied: SourceIcons.usage.ownerOccupied,
    rented: SourceIcons.usage.rented,
    vacant: SourceIcons.usage.vacant,
};

/**
 * Icons für ModeSelector
 */
export const ModeSelectorIcons = {
    rental: SourceIcons.property.apartment,    // Miete
    comparison: SourceIcons.usage.sell,        // Verkaufen vs. Vermieten
    sale_value: SourceIcons.property.house,    // Verkaufswert
};

/**
 * Icons für ContactFormStep
 */
export const ContactFormStepIcons = {
    email: SourceIcons.ui.email,
    phone: SourceIcons.ui.phone,
    consent: SourceIcons.ui.check,
};

/**
 * Icons für ResultsDisplay
 */
export const ResultsDisplayIcons = {
    download: SourceIcons.ui.download,
    success: SourceIcons.ui.success,
    info: SourceIcons.ui.info,
};

/**
 * UI-Icons für allgemeine Verwendung
 */
export const UIIcons = SourceIcons.ui;

// ============================================
// HELPER-FUNKTIONEN
// ============================================

/**
 * Gibt das Icon für einen bestimmten Step und Key zurück
 *
 * @param {string} step - Step-Name (z.B. 'propertyType', 'features')
 * @param {string} key - Icon-Key innerhalb des Steps
 * @returns {string|null} Icon-URL oder null
 */
export function getStepIcon(step, key) {
    const stepIconMaps = {
        propertyType: PropertyTypeStepIcons,
        houseType: HouseTypeStepIcons,
        features: FeaturesStepIcons,
        condition: ConditionStepIcons,
        quality: QualityStepIcons,
        usage: UsageStepIcons,
        contact: ContactFormStepIcons,
        results: ResultsDisplayIcons,
        modeSelector: ModeSelectorIcons,
    };

    const iconMap = stepIconMaps[step];
    if (!iconMap) {
        console.warn(`[IRP Icons] Unknown step: ${step}`);
        return null;
    }

    const icon = iconMap[key];
    if (!icon) {
        console.warn(`[IRP Icons] Unknown icon key '${key}' in step '${step}'`);
        return null;
    }

    return icon;
}

/**
 * React-Komponente für Icon-Rendering
 */
export function Icon({ src, alt = '', className = '', size = 24 }) {
    return (
        <img
            src={src}
            alt={alt}
            className={`irp-icon ${className}`}
            width={size}
            height={size}
            loading="lazy"
        />
    );
}
```

### 6.5 Migration der bestehenden Icons

**Schritt 1: Icon-Mapping erstellen**

| Alter Pfad | Neuer Pfad |
|------------|------------|
| `assets/images/wohnung.svg` | `assets/icons/_source/property/apartment.svg` |
| `assets/images/haus.svg` | `assets/icons/_source/property/house.svg` |
| `assets/images/gewerbe.svg` | `assets/icons/_source/property/commercial.svg` |
| `assets/images/balkon.svg` | `assets/icons/_source/features/balcony.svg` |
| `assets/images/terrasse.svg` | `assets/icons/_source/features/terrace.svg` |
| `assets/images/garte.svg` | `assets/icons/_source/features/garden.svg` |
| `assets/images/aufzug.svg` | `assets/icons/_source/features/elevator.svg` |
| `assets/images/stellplatz.svg` | `assets/icons/_source/features/parking.svg` |
| `assets/images/garage.svg` | `assets/icons/_source/features/garage.svg` |
| `assets/images/keller.svg` | `assets/icons/_source/features/cellar.svg` |
| `assets/images/kueche.svg` | `assets/icons/_source/features/kitchen.svg` |
| `assets/images/fussbodenheizung.svg` | `assets/icons/_source/features/floor-heating.svg` |
| `assets/images/wc.svg` | `assets/icons/_source/features/guest-toilet.svg` |
| `assets/images/barrierefrei.svg` | `assets/icons/_source/features/barrier-free.svg` |
| `assets/images/neubau.svg` | `assets/icons/_source/condition/new.svg` |
| `assets/images/renoviert.svg` | `assets/icons/_source/condition/renovated.svg` |
| `assets/images/gut.svg` | `assets/icons/_source/condition/good.svg` |
| `assets/images/reparaturen.svg` | `assets/icons/_source/condition/repairs-needed.svg` |
| `assets/icon/haustypen/einfamilienhaus.svg` | `assets/icons/_source/house-types/single-family.svg` |
| `assets/icon/haustypen/doppelhaushaelfte.svg` | `assets/icons/_source/house-types/semi-detached.svg` |
| `assets/icon/haustypen/endreihenhaus.svg` | `assets/icons/_source/house-types/townhouse-end.svg` |
| `assets/icon/haustypen/mittelreihenhaus.svg` | `assets/icons/_source/house-types/townhouse-middle.svg` |
| `assets/icon/haustypen/bungalow.svg` | `assets/icons/_source/house-types/bungalow.svg` |
| `assets/icon/haustypen/mehrfamilienhaus.svg` | `assets/icons/_source/house-types/multi-family.svg` |
| `assets/icon/qualitaetsstufen/einfach.svg` | `assets/icons/_source/quality/simple.svg` |
| `assets/icon/qualitaetsstufen/normal.svg` | `assets/icons/_source/quality/normal.svg` |
| `assets/icon/qualitaetsstufen/gehoben.svg` | `assets/icons/_source/quality/premium.svg` |
| `assets/icon/qualitaetsstufen/luxurioes.svg` | `assets/icons/_source/quality/luxury.svg` |
| `assets/icon/ausstattung/solaranlage.svg` | `assets/icons/_source/features/solar.svg` |
| `assets/icon/ausstattung/kamin.svg` | `assets/icons/_source/features/fireplace.svg` |
| `assets/icon/ausstattung/parkettboden.svg` | `assets/icons/_source/features/parquet.svg` |
| `assets/icon/ausstattung/dachboden.svg` | `assets/icons/_source/features/attic.svg` |
| `assets/icon/nutzung/selbstgenutzt.svg` | `assets/icons/_source/usage/owner-occupied.svg` |
| `assets/icon/nutzung/vermietet.svg` | `assets/icons/_source/usage/rented.svg` |
| `assets/icon/nutzung/leerstand.svg` | `assets/icons/_source/usage/vacant.svg` |
| `assets/icon/nutzung/kaufen.svg` | `assets/icons/_source/usage/buy.svg` |
| `assets/icon/nutzung/verkaufen.svg` | `assets/icons/_source/usage/sell.svg` |

**Schritt 2: Komponenten aktualisieren**

```javascript
// VORHER (FeaturesStep.js):
const features = [
    { id: 'balcony', label: __('Balkon'), icon: `${pluginUrl}assets/images/balkon.svg` },
    { id: 'terrace', label: __('Terrasse'), icon: `${pluginUrl}assets/images/terrasse.svg` },
    // ...
];

// NACHHER:
import { FeaturesStepIcons, Icon } from '../icons';

const features = [
    { id: 'balcony', label: __('Balkon'), icon: FeaturesStepIcons.balcony },
    { id: 'terrace', label: __('Terrasse'), icon: FeaturesStepIcons.terrace },
    // ...
];

// In der Render-Funktion:
<Icon src={feature.icon} alt={feature.label} size={32} />
```

**Schritt 3: Inline-SVGs ersetzen**

```javascript
// VORHER (LeadForm.js):
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
    <path d="M3 8l7.89 5.26..."/>
</svg>

// NACHHER:
import { UIIcons, Icon } from '../icons';

<Icon src={UIIcons.email} alt="E-Mail" size={20} />
```

### 6.6 Fehlende Icons erstellen

Die folgenden UI-Icons müssen noch erstellt werden:

| Icon | Verwendung | Priorität |
|------|------------|-----------|
| `ui/check.svg` | Checkboxen, Erfolg | Hoch |
| `ui/arrow-left.svg` | Zurück-Button | Hoch |
| `ui/arrow-right.svg` | Weiter-Button | Hoch |
| `ui/info.svg` | Info-Tooltips | Mittel |
| `ui/warning.svg` | Warnungen | Mittel |
| `ui/error.svg` | Fehlermeldungen | Mittel |
| `ui/success.svg` | Erfolgsmeldungen | Mittel |
| `ui/download.svg` | PDF-Download | Hoch |
| `ui/email.svg` | E-Mail-Feld | Hoch |
| `ui/phone.svg` | Telefon-Feld | Hoch |
| `ui/location.svg` | Standort/PLZ | Hoch |

### 6.7 Build-Prozess Integration

**NPM Script für Icon-Validierung:**

```json
// package.json
{
  "scripts": {
    "icons:validate": "node scripts/validate-icons.js",
    "icons:list": "node scripts/list-icons.js"
  }
}
```

**Validierungs-Script:** `scripts/validate-icons.js`

```javascript
const fs = require('fs');
const path = require('path');

const ICONS_DIR = path.join(__dirname, '../assets/icons/_source');

function validateIcons() {
    const errors = [];

    // Prüfe ob alle referenzierten Icons existieren
    const iconRegistry = require('../src/icons/index.js');

    Object.entries(iconRegistry.SourceIcons).forEach(([category, icons]) => {
        Object.entries(icons).forEach(([name, url]) => {
            const iconPath = url.replace(
                window.irpSettings?.pluginUrl + 'assets/icons/_source/',
                ICONS_DIR + '/'
            );

            if (!fs.existsSync(iconPath)) {
                errors.push(`Missing icon: ${iconPath}`);
            }
        });
    });

    if (errors.length > 0) {
        console.error('Icon validation failed:');
        errors.forEach(e => console.error(`  - ${e}`));
        process.exit(1);
    }

    console.log('All icons validated successfully!');
}

validateIcons();
```

---

## 7. Implementierungsreihenfolge

### Sprint 1: Grundlagen (Kritisch)

| # | Aufgabe | Datei(en) | Priorität |
|---|---------|-----------|-----------|
| 1.1 | Error-Handler Klasse erstellen | `includes/class-error-handler.php` | Kritisch |
| 1.2 | POT-Datei generieren | `languages/*.pot` | Kritisch |
| 1.3 | Deutsche Übersetzung erstellen | `languages/*-de_DE.po/mo` | Kritisch |
| 1.4 | Englische Übersetzung erstellen | `languages/*-en_US.po/mo` | Kritisch |
| 1.5 | JS-Übersetzungen konfigurieren | `class-assets.php` | Kritisch |

### Sprint 2: Sicherheit (Kritisch)

| # | Aufgabe | Datei(en) | Priorität |
|---|---------|-----------|-----------|
| 2.1 | SQL-Injection Fixes | `class-activator.php` | Kritisch |
| 2.2 | Admin-AJAX Absicherung | `class-admin.php` | Kritisch |
| 2.3 | Input-Validierung | `class-rest-api.php` | Kritisch |
| 2.4 | Race Condition Fix | `class-leads.php` | Hoch |
| 2.5 | Debug-Logging konditionieren | Alle PHP-Dateien | Hoch |

### Sprint 3: Fehlerbehandlung (Hoch)

| # | Aufgabe | Datei(en) | Priorität |
|---|---------|-----------|-----------|
| 3.1 | REST-API Fehler migrieren | `class-rest-api.php` | Hoch |
| 3.2 | Leads-Fehler migrieren | `class-leads.php` | Hoch |
| 3.3 | Propstack-Fehler migrieren | `class-propstack.php` | Hoch |
| 3.4 | Frontend Error-Handler | `src/utils/errorHandler.js` | Hoch |
| 3.5 | React-Komponenten anpassen | `src/components/*.js` | Hoch |

### Sprint 4: Performance & Qualität (Mittel)

| # | Aufgabe | Datei(en) | Priorität |
|---|---------|-----------|-----------|
| 4.1 | N+1 Query Fix | `leads-list.php`, `class-propstack.php` | Mittel |
| 4.2 | CSV Streaming Export | `class-leads.php` | Mittel |
| 4.3 | Konstanten definieren | `class-calculator.php` | Niedrig |
| 4.4 | extract() entfernen | `class-email.php` | Niedrig |
| 4.5 | XSS-Fixes Admin | `admin/views/*.php` | Mittel |

### Sprint 5: Icon-Management (Mittel)

| # | Aufgabe | Datei(en) | Priorität |
|---|---------|-----------|-----------|
| 5.1 | Icon-Verzeichnisstruktur erstellen | `assets/icons/_source/**` | Hoch |
| 5.2 | Bestehende Icons migrieren | `assets/images/` → `assets/icons/` | Hoch |
| 5.3 | Icon-Registry JavaScript erstellen | `src/icons/index.js` | Hoch |
| 5.4 | Fehlende UI-Icons erstellen | `assets/icons/_source/ui/` | Mittel |
| 5.5 | FeaturesStep.js migrieren | `src/components/steps/FeaturesStep.js` | Mittel |
| 5.6 | PropertyTypeStep.js migrieren | `src/components/steps/PropertyTypeStep.js` | Mittel |
| 5.7 | ConditionStep.js migrieren | `src/components/steps/ConditionStep.js` | Mittel |
| 5.8 | Sale-Steps migrieren | `src/components/steps/Sale*.js` | Mittel |
| 5.9 | Inline-SVGs durch Icon-Komponente ersetzen | `src/components/*.js` | Niedrig |
| 5.10 | Icon-Validierungs-Script erstellen | `scripts/validate-icons.js` | Niedrig |
| 5.11 | Alte Icon-Verzeichnisse entfernen | `assets/images/`, `assets/icon/` | Niedrig |

### Sprint 6: Dokumentation (Abschluss)

| # | Aufgabe | Datei(en) | Priorität |
|---|---------|-----------|-----------|
| 6.1 | BENUTZERHANDBUCH.md aktualisieren | `docs/BENUTZERHANDBUCH.md` | Mittel |
| 6.2 | ENTWICKLER.md aktualisieren | `docs/ENTWICKLER.md` | Mittel |
| 6.3 | API.md mit neuen Error-Codes ergänzen | `docs/API.md` | Mittel |
| 6.4 | KONFIGURATION.md i18n-Abschnitt hinzufügen | `docs/KONFIGURATION.md` | Mittel |
| 6.5 | Icon-Dokumentation erstellen | `docs/ICONS.md` | Mittel |
| 6.6 | CHANGELOG.md aktualisieren | `CHANGELOG.md` | Hoch |
| 6.7 | README.md überarbeiten | `README.md` | Mittel |
| 6.8 | Inline-Code-Dokumentation prüfen | Alle PHP/JS-Dateien | Niedrig |

---

## 8. Testplan

### 8.1 Übersetzungstests

```bash
# POT-Datei validieren
msgfmt --check languages/immobilien-rechner-pro-de_DE.po

# Fehlende Übersetzungen finden
msgcmp languages/immobilien-rechner-pro-de_DE.po languages/immobilien-rechner-pro.pot
```

### 8.2 Manuelle Sprachtests

1. WordPress auf Deutsch stellen (`de_DE`)
2. Plugin aktivieren
3. Alle Formulare durchklicken
4. Fehlermeldungen provozieren (leere Felder, ungültige E-Mail)
5. Admin-Bereich prüfen
6. Auf Englisch (`en_US`) wiederholen

### 8.3 Sicherheitstests

| Test | Erwartetes Ergebnis |
|------|---------------------|
| SQL-Injection in PLZ-Feld | Eingabe wird escaped |
| XSS in Name-Feld | Script wird nicht ausgeführt |
| CSRF ohne Nonce | Request wird abgelehnt |
| Doppelter Lead-Submit | Nur ein Lead erstellt |
| Unauthorized Admin-Access | 403 Forbidden |

### 8.4 Performance-Tests

| Test | Zielwert |
|------|----------|
| Leads-Liste mit 1000 Einträgen | < 2s Ladezeit |
| CSV-Export 5000 Leads | Kein Memory-Fehler |
| API-Response Zeit | < 500ms |

### 8.5 Berechnungstests (Parameter-Vollständigkeit)

#### Kritische Feststellung: Nicht verwendete Parameter

Bei der Analyse wurden **Parameter identifiziert, die abgefragt aber NICHT in der Berechnung berücksichtigt werden**:

| Rechner | Parameter | Abgefragt in | Verwendet in Berechnung? |
|---------|-----------|--------------|--------------------------|
| **Mietwert** | `rooms` | PropertyDetailsStep | ❌ NEIN |
| **Mietwert** | `zip_code` | LocationStep | ❌ NEIN (nur city_id) |
| **Mietwert** | `location` | LocationStep | ❌ NEIN |
| **Mietwert** | `address` | REST API | ❌ NEIN |
| **Verkaufswert** | `usage_type` | SalePurposeStep | ❌ NEIN |
| **Verkaufswert** | `sale_intention` | SalePurposeStep | ❌ NEIN |
| **Verkaufswert** | `timeframe` | SalePurposeStep | ❌ NEIN |

**Handlungsbedarf:**
1. Entscheiden, ob diese Parameter die Berechnung beeinflussen SOLLTEN
2. Falls ja: Berechnung erweitern
3. Falls nein: Im Frontend klarstellen, dass diese nur für die Lead-Qualifizierung dienen

---

#### Testspezifikation: Mietwert-Rechner (Rental)

**Testdatei:** `tests/php/test-rental-calculator.php`

```php
<?php
/**
 * Mietwert-Rechner Berechnungstests
 *
 * Prüft, dass ALLE Parameter korrekt in die Berechnung einfließen
 */
class Test_Rental_Calculator extends WP_UnitTestCase {

    private IRP_Calculator $calculator;

    public function setUp(): void {
        parent::setUp();
        $this->calculator = new IRP_Calculator();

        // Test-Matrix setzen
        update_option('irp_price_matrix', [
            'cities' => [
                [
                    'id' => 'test_city',
                    'name' => 'Test Stadt',
                    'base_price' => 10.00,
                    'size_degression' => 0.20,
                    'sale_factor' => 25,
                ]
            ],
            'condition_multipliers' => [
                'new' => 1.25,
                'renovated' => 1.10,
                'good' => 1.00,
                'needs_renovation' => 0.80,
            ],
            'type_multipliers' => [
                'apartment' => 1.00,
                'house' => 1.15,
                'commercial' => 0.85,
            ],
            'feature_premiums' => [
                'balcony' => 0.50,
                'terrace' => 0.75,
                'garden' => 1.00,
                'elevator' => 0.30,
                'garage' => 0.60,
            ],
            'location_ratings' => [
                1 => ['name' => 'Einfach', 'multiplier' => 0.85],
                2 => ['name' => 'Normal', 'multiplier' => 0.95],
                3 => ['name' => 'Gut', 'multiplier' => 1.00],
                4 => ['name' => 'Sehr gut', 'multiplier' => 1.10],
                5 => ['name' => 'Premium', 'multiplier' => 1.25],
            ],
            'age_multipliers' => [
                'from_2015' => ['multiplier' => 1.10, 'min_year' => 2015, 'max_year' => null],
                '1990_1999' => ['multiplier' => 1.00, 'min_year' => 1990, 'max_year' => 1999],
                '1960_1979' => ['multiplier' => 0.90, 'min_year' => 1960, 'max_year' => 1979],
            ],
        ]);
    }

    /**
     * Basis-Test: Grundberechnung ohne Modifikatoren
     */
    public function test_base_calculation() {
        $result = $this->calculator->calculate_rental_value([
            'size' => 70,  // Referenzgröße
            'city_id' => 'test_city',
            'condition' => 'good',
            'property_type' => 'apartment',
            'location_rating' => 3,
        ]);

        // Bei 70m², Basispreis 10€, alle Faktoren = 1.0
        // Erwartete Miete: 70 * 10 = 700€
        $this->assertEquals(700.00, $result['monthly_rent']['estimate']);
        $this->assertEquals(10.00, $result['price_per_sqm']);
    }

    /**
     * Test: Wohnfläche beeinflusst Berechnung (Size Degression)
     */
    public function test_size_affects_calculation() {
        $base_params = [
            'city_id' => 'test_city',
            'condition' => 'good',
            'property_type' => 'apartment',
            'location_rating' => 3,
        ];

        // Kleine Wohnung (50m²) sollte höheren m²-Preis haben
        $small = $this->calculator->calculate_rental_value(array_merge($base_params, ['size' => 50]));

        // Große Wohnung (120m²) sollte niedrigeren m²-Preis haben
        $large = $this->calculator->calculate_rental_value(array_merge($base_params, ['size' => 120]));

        $this->assertGreaterThan($large['price_per_sqm'], $small['price_per_sqm'],
            'FEHLER: Wohnfläche beeinflusst Preis/m² nicht korrekt (Size Degression fehlt)');
    }

    /**
     * Test: Zustand beeinflusst Berechnung
     */
    public function test_condition_affects_calculation() {
        $base_params = [
            'size' => 70,
            'city_id' => 'test_city',
            'property_type' => 'apartment',
            'location_rating' => 3,
        ];

        $new = $this->calculator->calculate_rental_value(array_merge($base_params, ['condition' => 'new']));
        $renovation = $this->calculator->calculate_rental_value(array_merge($base_params, ['condition' => 'needs_renovation']));

        $this->assertGreaterThan($renovation['monthly_rent']['estimate'], $new['monthly_rent']['estimate'],
            'FEHLER: Zustand (condition) beeinflusst Berechnung nicht');

        // Prüfe exakten Faktor: new = 1.25, needs_renovation = 0.80
        $ratio = $new['monthly_rent']['estimate'] / $renovation['monthly_rent']['estimate'];
        $expected_ratio = 1.25 / 0.80;
        $this->assertEqualsWithDelta($expected_ratio, $ratio, 0.01,
            'FEHLER: Condition-Multiplikatoren werden nicht korrekt angewendet');
    }

    /**
     * Test: Immobilientyp beeinflusst Berechnung
     */
    public function test_property_type_affects_calculation() {
        $base_params = [
            'size' => 70,
            'city_id' => 'test_city',
            'condition' => 'good',
            'location_rating' => 3,
        ];

        $apartment = $this->calculator->calculate_rental_value(array_merge($base_params, ['property_type' => 'apartment']));
        $house = $this->calculator->calculate_rental_value(array_merge($base_params, ['property_type' => 'house']));
        $commercial = $this->calculator->calculate_rental_value(array_merge($base_params, ['property_type' => 'commercial']));

        $this->assertGreaterThan($apartment['monthly_rent']['estimate'], $house['monthly_rent']['estimate'],
            'FEHLER: property_type=house sollte höher sein als apartment');
        $this->assertGreaterThan($commercial['monthly_rent']['estimate'], $apartment['monthly_rent']['estimate'],
            'FEHLER: property_type=commercial sollte niedriger sein als apartment');
    }

    /**
     * Test: Features beeinflussen Berechnung
     */
    public function test_features_affect_calculation() {
        $base_params = [
            'size' => 70,
            'city_id' => 'test_city',
            'condition' => 'good',
            'property_type' => 'apartment',
            'location_rating' => 3,
        ];

        $no_features = $this->calculator->calculate_rental_value(array_merge($base_params, ['features' => []]));
        $with_features = $this->calculator->calculate_rental_value(array_merge($base_params, [
            'features' => ['balcony', 'terrace', 'garage']
        ]));

        $this->assertGreaterThan($no_features['monthly_rent']['estimate'], $with_features['monthly_rent']['estimate'],
            'FEHLER: Features beeinflussen Berechnung nicht');

        // Erwarteter Aufschlag: balcony(0.50) + terrace(0.75) + garage(0.60) = 1.85 €/m²
        $expected_premium = (0.50 + 0.75 + 0.60) * 70;
        $actual_diff = $with_features['monthly_rent']['estimate'] - $no_features['monthly_rent']['estimate'];
        $this->assertEqualsWithDelta($expected_premium, $actual_diff, 1.00,
            'FEHLER: Feature-Premiums werden nicht korrekt addiert');
    }

    /**
     * Test: Baujahr beeinflusst Berechnung
     */
    public function test_year_built_affects_calculation() {
        $base_params = [
            'size' => 70,
            'city_id' => 'test_city',
            'condition' => 'good',
            'property_type' => 'apartment',
            'location_rating' => 3,
        ];

        $new_building = $this->calculator->calculate_rental_value(array_merge($base_params, ['year_built' => 2020]));
        $old_building = $this->calculator->calculate_rental_value(array_merge($base_params, ['year_built' => 1970]));

        $this->assertGreaterThan($old_building['monthly_rent']['estimate'], $new_building['monthly_rent']['estimate'],
            'FEHLER: Baujahr (year_built) beeinflusst Berechnung nicht');

        // Prüfe Faktoren: 2020 = 1.10, 1970 = 0.90
        $ratio = $new_building['monthly_rent']['estimate'] / $old_building['monthly_rent']['estimate'];
        $expected_ratio = 1.10 / 0.90;
        $this->assertEqualsWithDelta($expected_ratio, $ratio, 0.01,
            'FEHLER: Age-Multiplikatoren werden nicht korrekt angewendet');
    }

    /**
     * Test: Location Rating beeinflusst Berechnung
     */
    public function test_location_rating_affects_calculation() {
        $base_params = [
            'size' => 70,
            'city_id' => 'test_city',
            'condition' => 'good',
            'property_type' => 'apartment',
        ];

        $simple = $this->calculator->calculate_rental_value(array_merge($base_params, ['location_rating' => 1]));
        $premium = $this->calculator->calculate_rental_value(array_merge($base_params, ['location_rating' => 5]));

        $this->assertGreaterThan($simple['monthly_rent']['estimate'], $premium['monthly_rent']['estimate'],
            'FEHLER: location_rating beeinflusst Berechnung nicht');

        // Prüfe Faktoren: simple = 0.85, premium = 1.25
        $ratio = $premium['monthly_rent']['estimate'] / $simple['monthly_rent']['estimate'];
        $expected_ratio = 1.25 / 0.85;
        $this->assertEqualsWithDelta($expected_ratio, $ratio, 0.01,
            'FEHLER: Location-Multiplikatoren werden nicht korrekt angewendet');
    }

    /**
     * Test: Stadt (city_id) beeinflusst Berechnung
     */
    public function test_city_affects_calculation() {
        // Zweite Stadt mit anderem Basispreis
        $matrix = get_option('irp_price_matrix');
        $matrix['cities'][] = [
            'id' => 'expensive_city',
            'name' => 'Teure Stadt',
            'base_price' => 15.00,
            'size_degression' => 0.20,
        ];
        update_option('irp_price_matrix', $matrix);

        $calculator = new IRP_Calculator();

        $base_params = [
            'size' => 70,
            'condition' => 'good',
            'property_type' => 'apartment',
            'location_rating' => 3,
        ];

        $cheap = $calculator->calculate_rental_value(array_merge($base_params, ['city_id' => 'test_city']));
        $expensive = $calculator->calculate_rental_value(array_merge($base_params, ['city_id' => 'expensive_city']));

        $this->assertGreaterThan($cheap['monthly_rent']['estimate'], $expensive['monthly_rent']['estimate'],
            'FEHLER: city_id beeinflusst Berechnung nicht');

        // Erwartetes Verhältnis: 15/10 = 1.5
        $ratio = $expensive['monthly_rent']['estimate'] / $cheap['monthly_rent']['estimate'];
        $this->assertEqualsWithDelta(1.5, $ratio, 0.01,
            'FEHLER: Stadt-Basispreise werden nicht korrekt angewendet');
    }

    /**
     * WARNUNG: Dieser Test dokumentiert nicht verwendete Parameter
     */
    public function test_unused_parameters_warning() {
        // rooms wird abgefragt aber NICHT verwendet
        $this->markTestIncomplete(
            'WARNUNG: Parameter "rooms" wird abgefragt aber nicht in der Berechnung verwendet. ' .
            'Entscheidung erforderlich: Soll rooms die Berechnung beeinflussen?'
        );
    }
}
```

---

#### Testspezifikation: Verkaufswert-Rechner (Sale Value)

**Testdatei:** `tests/php/test-sale-calculator.php`

```php
<?php
/**
 * Verkaufswert-Rechner Berechnungstests
 */
class Test_Sale_Calculator extends WP_UnitTestCase {

    private IRP_Sale_Calculator $calculator;

    public function setUp(): void {
        parent::setUp();

        // Test-Konfiguration
        update_option('irp_price_matrix', [
            'cities' => [[
                'id' => 'test_city',
                'name' => 'Test Stadt',
                'land_price_per_sqm' => 150,
                'building_price_per_sqm' => 2500,
                'apartment_price_per_sqm' => 2200,
                'market_adjustment_factor' => 1.00,
            ]],
            'location_ratings' => [
                1 => ['multiplier' => 0.85],
                3 => ['multiplier' => 1.00],
                5 => ['multiplier' => 1.25],
            ],
        ]);

        update_option('irp_sale_value_settings', [
            'house_type_multipliers' => [
                'single_family' => ['multiplier' => 1.00],
                'multi_family' => ['multiplier' => 1.15],
                'bungalow' => ['multiplier' => 1.05],
            ],
            'quality_multipliers' => [
                'simple' => ['multiplier' => 0.85],
                'normal' => ['multiplier' => 1.00],
                'luxury' => ['multiplier' => 1.35],
            ],
            'modernization_year_shift' => [
                '1-3_years' => ['years' => 15],
                'never' => ['years' => 0],
            ],
            'age_depreciation' => [
                'rate_per_year' => 0.01,
                'max_depreciation' => 0.40,
                'base_year' => 2025,
            ],
            'features' => [
                'garage' => ['value' => 15000],
                'garden' => ['value' => 8000],
                'solar' => ['value' => 12000],
            ],
        ]);

        $this->calculator = new IRP_Sale_Calculator();
    }

    /**
     * Test: Wohnfläche beeinflusst Wohnung-Berechnung
     */
    public function test_living_space_affects_apartment() {
        $base = ['property_type' => 'apartment', 'city_id' => 'test_city', 'quality' => 'normal', 'location_rating' => 3, 'build_year' => 2000, 'modernization' => 'never'];

        $small = $this->calculator->calculate(array_merge($base, ['living_space' => 50]));
        $large = $this->calculator->calculate(array_merge($base, ['living_space' => 100]));

        $this->assertGreaterThan($small['price_estimate'], $large['price_estimate'],
            'FEHLER: living_space beeinflusst Apartment-Berechnung nicht');
    }

    /**
     * Test: Grundstücksfläche beeinflusst Haus-Berechnung
     */
    public function test_land_size_affects_house() {
        $base = ['property_type' => 'house', 'city_id' => 'test_city', 'living_space' => 150, 'quality' => 'normal', 'location_rating' => 3, 'build_year' => 2000, 'modernization' => 'never', 'house_type' => 'single_family'];

        $small_land = $this->calculator->calculate(array_merge($base, ['land_size' => 300]));
        $large_land = $this->calculator->calculate(array_merge($base, ['land_size' => 800]));

        $this->assertGreaterThan($small_land['price_estimate'], $large_land['price_estimate'],
            'FEHLER: land_size beeinflusst Haus-Berechnung nicht');
    }

    /**
     * Test: Haustyp beeinflusst Berechnung
     */
    public function test_house_type_affects_calculation() {
        $base = ['property_type' => 'house', 'city_id' => 'test_city', 'living_space' => 150, 'land_size' => 500, 'quality' => 'normal', 'location_rating' => 3, 'build_year' => 2000, 'modernization' => 'never'];

        $single = $this->calculator->calculate(array_merge($base, ['house_type' => 'single_family']));
        $multi = $this->calculator->calculate(array_merge($base, ['house_type' => 'multi_family']));

        $this->assertGreaterThan($single['price_estimate'], $multi['price_estimate'],
            'FEHLER: house_type beeinflusst Berechnung nicht');
    }

    /**
     * Test: Qualität beeinflusst Berechnung
     */
    public function test_quality_affects_calculation() {
        $base = ['property_type' => 'apartment', 'city_id' => 'test_city', 'living_space' => 80, 'location_rating' => 3, 'build_year' => 2000, 'modernization' => 'never'];

        $simple = $this->calculator->calculate(array_merge($base, ['quality' => 'simple']));
        $luxury = $this->calculator->calculate(array_merge($base, ['quality' => 'luxury']));

        $this->assertGreaterThan($simple['price_estimate'], $luxury['price_estimate'],
            'FEHLER: quality beeinflusst Berechnung nicht');
    }

    /**
     * Test: Baujahr beeinflusst Berechnung (Alterswertminderung)
     */
    public function test_build_year_affects_calculation() {
        $base = ['property_type' => 'house', 'city_id' => 'test_city', 'living_space' => 150, 'land_size' => 500, 'quality' => 'normal', 'location_rating' => 3, 'house_type' => 'single_family', 'modernization' => 'never'];

        $new = $this->calculator->calculate(array_merge($base, ['build_year' => 2020]));
        $old = $this->calculator->calculate(array_merge($base, ['build_year' => 1970]));

        $this->assertGreaterThan($old['price_estimate'], $new['price_estimate'],
            'FEHLER: build_year beeinflusst Berechnung nicht (Alterswertminderung fehlt)');
    }

    /**
     * Test: Modernisierung beeinflusst Berechnung
     */
    public function test_modernization_affects_calculation() {
        $base = ['property_type' => 'house', 'city_id' => 'test_city', 'living_space' => 150, 'land_size' => 500, 'quality' => 'normal', 'location_rating' => 3, 'house_type' => 'single_family', 'build_year' => 1980];

        $modernized = $this->calculator->calculate(array_merge($base, ['modernization' => '1-3_years']));
        $original = $this->calculator->calculate(array_merge($base, ['modernization' => 'never']));

        $this->assertGreaterThan($original['price_estimate'], $modernized['price_estimate'],
            'FEHLER: modernization beeinflusst Berechnung nicht');
    }

    /**
     * Test: Location Rating beeinflusst Berechnung
     */
    public function test_location_rating_affects_calculation() {
        $base = ['property_type' => 'apartment', 'city_id' => 'test_city', 'living_space' => 80, 'quality' => 'normal', 'build_year' => 2000, 'modernization' => 'never'];

        $simple = $this->calculator->calculate(array_merge($base, ['location_rating' => 1]));
        $premium = $this->calculator->calculate(array_merge($base, ['location_rating' => 5]));

        $this->assertGreaterThan($simple['price_estimate'], $premium['price_estimate'],
            'FEHLER: location_rating beeinflusst Berechnung nicht');
    }

    /**
     * Test: Features beeinflussen Berechnung
     */
    public function test_features_affect_calculation() {
        $base = ['property_type' => 'house', 'city_id' => 'test_city', 'living_space' => 150, 'land_size' => 500, 'quality' => 'normal', 'location_rating' => 3, 'build_year' => 2000, 'modernization' => 'never', 'house_type' => 'single_family'];

        $no_features = $this->calculator->calculate(array_merge($base, ['features' => []]));
        $with_features = $this->calculator->calculate(array_merge($base, ['features' => ['garage', 'garden', 'solar']]));

        // Erwarteter Aufschlag: 15000 + 8000 + 12000 = 35000€
        $diff = $with_features['price_estimate'] - $no_features['price_estimate'];
        $this->assertEqualsWithDelta(35000, $diff, 5000,
            'FEHLER: Features beeinflussen Berechnung nicht korrekt');
    }

    /**
     * KRITISCH: Nicht verwendete Parameter dokumentieren
     */
    public function test_unused_parameters_documented() {
        // Diese Parameter werden abgefragt aber NICHT in der Berechnung verwendet:
        $unused_params = ['usage_type', 'sale_intention', 'timeframe'];

        $this->markTestIncomplete(
            'KRITISCH: Folgende Parameter werden abgefragt aber NICHT in der Berechnung verwendet: ' .
            implode(', ', $unused_params) . '. ' .
            'Entscheidung erforderlich: Sollen diese Parameter die Berechnung beeinflussen ' .
            'oder nur für Lead-Qualifizierung dienen?'
        );
    }
}
```

---

#### Testspezifikation: Vergleichsrechner (Comparison)

**Testdatei:** `tests/php/test-comparison-calculator.php`

```php
<?php
/**
 * Verkaufen vs. Vermieten Berechnungstests
 */
class Test_Comparison_Calculator extends WP_UnitTestCase {

    private IRP_Calculator $calculator;

    /**
     * Test: Immobilienwert beeinflusst Berechnung
     */
    public function test_property_value_affects_calculation() {
        $base = $this->get_base_params();

        $low = $this->calculator->calculate_comparison(array_merge($base, ['property_value' => 200000]));
        $high = $this->calculator->calculate_comparison(array_merge($base, ['property_value' => 500000]));

        $this->assertGreaterThan($low['sale']['net_proceeds'], $high['sale']['net_proceeds'],
            'FEHLER: property_value beeinflusst Verkaufserlös nicht');
    }

    /**
     * Test: Restschuld beeinflusst Berechnung
     */
    public function test_remaining_mortgage_affects_calculation() {
        $base = array_merge($this->get_base_params(), ['property_value' => 300000]);

        $no_mortgage = $this->calculator->calculate_comparison(array_merge($base, ['remaining_mortgage' => 0]));
        $with_mortgage = $this->calculator->calculate_comparison(array_merge($base, ['remaining_mortgage' => 100000]));

        $this->assertGreaterThan($with_mortgage['sale']['net_proceeds'], $no_mortgage['sale']['net_proceeds'],
            'FEHLER: remaining_mortgage beeinflusst Berechnung nicht');
    }

    /**
     * Test: Hypothekenzins beeinflusst jährliche Kosten
     */
    public function test_mortgage_rate_affects_calculation() {
        $base = array_merge($this->get_base_params(), ['property_value' => 300000, 'remaining_mortgage' => 150000]);

        $low_rate = $this->calculator->calculate_comparison(array_merge($base, ['mortgage_rate' => 2.0]));
        $high_rate = $this->calculator->calculate_comparison(array_merge($base, ['mortgage_rate' => 5.0]));

        $this->assertGreaterThan($high_rate['rental_scenario']['net_annual_income'], $low_rate['rental_scenario']['net_annual_income'],
            'FEHLER: mortgage_rate beeinflusst jährliches Nettoeinkommen nicht');
    }

    /**
     * Test: Haltedauer beeinflusst Spekulationssteuer-Hinweis
     */
    public function test_holding_period_affects_tax_note() {
        $base = array_merge($this->get_base_params(), ['property_value' => 300000]);

        $short = $this->calculator->calculate_comparison(array_merge($base, ['holding_period_years' => 5]));
        $long = $this->calculator->calculate_comparison(array_merge($base, ['holding_period_years' => 15]));

        $this->assertNotNull($short['speculation_tax_note'],
            'FEHLER: Spekulationssteuer-Hinweis fehlt bei Haltedauer < 10 Jahre');
        $this->assertNull($long['speculation_tax_note'],
            'FEHLER: Spekulationssteuer-Hinweis sollte bei Haltedauer >= 10 Jahre fehlen');
    }

    /**
     * Test: Erwartete Wertsteigerung beeinflusst Projektion
     */
    public function test_expected_appreciation_affects_projection() {
        $base = array_merge($this->get_base_params(), ['property_value' => 300000]);

        $low_appreciation = $this->calculator->calculate_comparison(array_merge($base, ['expected_appreciation' => 1]));
        $high_appreciation = $this->calculator->calculate_comparison(array_merge($base, ['expected_appreciation' => 5]));

        // Prüfe 10-Jahres-Projektion
        $low_year_10 = $low_appreciation['projection'][9]['property_value'];
        $high_year_10 = $high_appreciation['projection'][9]['property_value'];

        $this->assertGreaterThan($low_year_10, $high_year_10,
            'FEHLER: expected_appreciation beeinflusst Projektion nicht');
    }

    private function get_base_params(): array {
        return [
            'size' => 80,
            'city_id' => 'test_city',
            'condition' => 'good',
            'property_type' => 'apartment',
            'location_rating' => 3,
        ];
    }
}
```

---

#### Zusammenfassung: Parameter-Einfluss-Matrix

| Parameter | Mietwert | Vergleich | Verkaufswert |
|-----------|:--------:|:---------:|:------------:|
| `size` / `living_space` | ✅ | ✅ | ✅ |
| `property_type` | ✅ | ✅ | ✅ |
| `city_id` | ✅ | ✅ | ✅ |
| `condition` | ✅ | ✅ | - |
| `features` | ✅ | ✅ | ✅ |
| `year_built` / `build_year` | ✅ | ✅ | ✅ |
| `location_rating` | ✅ | ✅ | ✅ |
| `rooms` | ❌ | ❌ | - |
| `zip_code` | ❌ | ❌ | ❌ |
| `property_value` | - | ✅ | - |
| `remaining_mortgage` | - | ✅ | - |
| `mortgage_rate` | - | ✅ | - |
| `holding_period_years` | - | ✅ | - |
| `expected_appreciation` | - | ✅ | - |
| `land_size` | - | - | ✅ |
| `house_type` | - | - | ✅ |
| `modernization` | - | - | ✅ |
| `quality` | - | - | ✅ |
| `usage_type` | - | - | ❌ |
| `sale_intention` | - | - | ❌ |
| `timeframe` | - | - | ❌ |

**Legende:** ✅ = wird verwendet | ❌ = wird NICHT verwendet | - = nicht relevant

### 8.6 Icon-Tests

| Test | Erwartetes Ergebnis |
|------|---------------------|
| Alle Icons im `_source/` Verzeichnis vorhanden | Validierungs-Script erfolgreich |
| Icons werden korrekt geladen | Keine 404-Fehler in Browser-Konsole |
| Icon-Komponente rendert korrekt | Bilder werden angezeigt |
| Responsive Icons | Größenanpassung funktioniert |
| Lazy Loading | Icons laden bei Bedarf |

```bash
# Icon-Validierung ausführen
npm run icons:validate

# Alle Icons auflisten
npm run icons:list
```

---

## Anhang: Dateistruktur nach Refactoring

```
immobilien-rechner-pro-v2/
├── assets/
│   └── icons/
│       ├── _source/                         # NEU: Zentrale Icon-Bibliothek
│       │   ├── property/                    # Immobilientypen
│       │   ├── house-types/                 # Haustypen
│       │   ├── features/                    # Ausstattungsmerkmale
│       │   ├── condition/                   # Zustand
│       │   ├── quality/                     # Qualitätsstufen
│       │   ├── usage/                       # Nutzungsart
│       │   ├── ui/                          # UI-Icons
│       │   └── timeline/                    # Zeitrahmen
│       ├── steps/                           # NEU: Step-basierte Referenzen
│       │   ├── property-type/
│       │   ├── features/
│       │   ├── condition/
│       │   └── ...
│       └── admin/                           # NEU: Admin-Icons
├── languages/
│   ├── immobilien-rechner-pro.pot           # NEU: Template
│   ├── immobilien-rechner-pro-de_DE.po      # NEU: Deutsche Übersetzung
│   ├── immobilien-rechner-pro-de_DE.mo      # NEU: Kompiliert
│   ├── immobilien-rechner-pro-de_DE-irp-calculator.json  # NEU: JS
│   ├── immobilien-rechner-pro-en_US.po      # NEU: Englische Übersetzung
│   ├── immobilien-rechner-pro-en_US.mo      # NEU: Kompiliert
│   └── immobilien-rechner-pro-en_US-irp-calculator.json  # NEU: JS
├── includes/
│   ├── class-error-handler.php              # NEU: Zentrale Fehlerbehandlung
│   ├── class-rest-api.php                   # Angepasst
│   ├── class-leads.php                      # Angepasst
│   ├── class-activator.php                  # Angepasst (SQL-Sicherheit)
│   └── ...
├── admin/
│   ├── class-admin.php                      # Angepasst (AJAX-Sicherheit)
│   └── views/                               # Angepasst (XSS-Fixes)
├── src/
│   ├── icons/
│   │   └── index.js                         # NEU: Icon-Registry
│   ├── utils/
│   │   └── errorHandler.js                  # NEU: Frontend Error-Handler
│   └── components/
│       ├── steps/                           # Angepasst (Icon-Imports)
│       └── ...                              # Angepasst für i18n
├── scripts/
│   └── validate-icons.js                    # NEU: Icon-Validierung
├── docs/
│   ├── REFACTORING-PLAN.md                  # Dieses Dokument
│   ├── ICONS.md                             # NEU: Icon-Dokumentation
│   ├── API.md                               # Aktualisiert (Error-Codes)
│   ├── BENUTZERHANDBUCH.md                  # Aktualisiert
│   ├── ENTWICKLER.md                        # Aktualisiert
│   └── KONFIGURATION.md                     # Aktualisiert (i18n)
└── CHANGELOG.md                             # Aktualisiert
```

### Zu löschende Verzeichnisse nach Migration

```
assets/images/          # Alte Icons → nach assets/icons/_source/ migriert
assets/icon/            # Alte Struktur → nach assets/icons/_source/ migriert
```

---

## Changelog

| Version | Datum | Änderungen |
|---------|-------|------------|
| 1.0 | 2026-01-18 | Initialer Plan erstellt |
| 1.1 | 2026-01-18 | Phase 5: Icon-Management-System hinzugefügt |
| 1.1 | 2026-01-18 | Sprint 6: Dokumentation hinzugefügt |
