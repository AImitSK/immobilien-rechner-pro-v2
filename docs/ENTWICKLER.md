# Entwickler-Dokumentation

Technische Dokumentation für Entwickler, die das Plugin erweitern oder anpassen möchten.

---

## Inhaltsverzeichnis

1. [Dateistruktur](#dateistruktur)
2. [PHP-Klassen](#php-klassen)
3. [React-Komponenten](#react-komponenten)
4. [Datenbank-Schema](#datenbank-schema)
5. [WordPress Options](#wordpress-options)
6. [Hooks & Filter](#hooks--filter)
7. [Technologie-Stack](#technologie-stack)
8. [Build-Prozess](#build-prozess)

---

## Dateistruktur

```
immobilien-rechner-pro-v2/
├── immobilien-rechner-pro.php       # Haupt-Plugin-Datei
│
├── includes/                         # PHP-Klassen
│   ├── class-activator.php          # Datenbank-Setup, Migrations
│   ├── class-assets.php             # Script/Style-Loading
│   ├── class-calculator.php         # Mietwert-Berechnungslogik
│   ├── class-sale-calculator.php    # Verkaufswert-Berechnungslogik
│   ├── class-email.php              # E-Mail-Versand
│   ├── class-github-updater.php     # Auto-Updates von GitHub
│   ├── class-leads.php              # Lead-Verwaltung
│   ├── class-pdf-generator.php      # PDF-Generierung mit DOMPDF
│   ├── class-propstack.php          # Propstack CRM Integration
│   ├── class-recaptcha.php          # reCAPTCHA v3
│   ├── class-rest-api.php           # REST API Endpoints
│   ├── class-shortcode.php          # Shortcode-Handler
│   └── templates/
│       ├── email.php                # E-Mail HTML Template
│       ├── pdf.php                  # PDF Template (Mietwert)
│       └── pdf-sale-value.php       # PDF Template (Verkaufswert)
│
├── admin/                            # Admin-Panel
│   ├── class-admin.php              # Admin-Klasse
│   └── views/
│       ├── dashboard.php
│       ├── leads.php
│       ├── lead-detail.php
│       ├── matrix.php
│       ├── shortcode.php
│       ├── settings.php
│       └── integrations.php
│
├── vendor/                           # Gebündelte Libraries
│   ├── autoload.php                 # Custom Autoloader
│   ├── dompdf/                      # DOMPDF 2.0.4
│   ├── php-font-lib/
│   └── php-svg-lib/
│
├── src/                              # React-Source (Entwicklung)
│   ├── index.js                     # Entry Point
│   ├── components/
│   │   ├── App.js                   # Hauptkomponente
│   │   ├── ModeSelector.js          # Modus-Auswahl
│   │   ├── RentalCalculator.js      # Mietwert-Wizard
│   │   ├── ComparisonCalculator.js  # Vergleichs-Wizard
│   │   ├── SaleValueCalculator.js   # Verkaufswert-Wizard
│   │   ├── ResultsDisplay.js        # Ergebnis-Anzeige
│   │   ├── SaleResultsDisplay.js    # Verkaufswert-Ergebnis
│   │   ├── LeadForm.js              # Kontaktformular
│   │   ├── ProgressBar.js           # Fortschrittsanzeige
│   │   └── steps/
│   │       ├── PropertyTypeStep.js
│   │       ├── PropertyDetailsStep.js
│   │       ├── CityStep.js
│   │       ├── ConditionStep.js
│   │       ├── LocationRatingStep.js
│   │       ├── FeaturesStep.js
│   │       ├── FinancialStep.js
│   │       ├── SalePropertyTypeStep.js
│   │       ├── SaleSizeStep.js
│   │       ├── SaleFeaturesStep.js
│   │       ├── SaleQualityLocationStep.js
│   │       ├── SaleAddressStep.js
│   │       ├── SalePurposeStep.js
│   │       ├── CalculationPendingStep.js
│   │       └── ContactFormStep.js
│   ├── hooks/
│   │   └── useDebounce.js
│   ├── utils/
│   │   ├── debug.js                 # Debug-Funktionen
│   │   └── tracking.js              # Google Ads Tracking
│   └── styles/
│       └── main.scss
│
├── build/                            # Kompiliertes React (Produktion)
│   ├── index.js
│   ├── index.css
│   └── index.asset.php
│
├── languages/                        # Übersetzungen
│   └── immobilien-rechner-pro.pot
│
├── docs/                             # Dokumentation
│   ├── BENUTZERHANDBUCH.md
│   ├── KONFIGURATION.md
│   ├── API.md
│   ├── ENTWICKLER.md
│   └── PLANUNG-VERKAUFSWERT-RECHNER.md
│
├── README.md
├── CHANGELOG.md
├── package.json
└── webpack.config.js
```

---

## PHP-Klassen

### IRP_Activator

**Datei:** `includes/class-activator.php`

Verantwortlich für Plugin-Aktivierung und Datenbank-Management.

```php
// Methoden
activate()              // Plugin aktivieren
deactivate()            // Plugin deaktivieren
create_tables()         // Datenbank-Tabellen erstellen
upgrade_database()      // Migrations ausführen
set_default_options()   // Standard-Optionen setzen
```

### IRP_Calculator

**Datei:** `includes/class-calculator.php`

Mietwert-Berechnungslogik.

```php
// Öffentliche Methoden
calculate_rental(array $data): array
calculate_comparison(array $data): array

// Private Methoden
get_city_data(string $city_id): array
apply_size_degression(float $base_price, float $size): float
calculate_features_bonus(array $features, array $settings): float
```

### IRP_Sale_Calculator

**Datei:** `includes/class-sale-calculator.php`

Verkaufswert-Berechnungslogik mit drei Verfahren.

```php
// Öffentliche Methoden
calculate(array $data): array

// Private Methoden (je nach Immobilientyp)
calculate_apartment(array $data, array $city, array $settings): array
calculate_house(array $data, array $city, array $settings): array
calculate_land(array $data, array $city, array $settings): array

// Hilfsmethoden
calculate_effective_age_factor(int $build_year, string $modernization, array $settings): float
calculate_features_value(array $features, array $settings): float
get_location_factor(int $rating): float
```

### IRP_Rest_API

**Datei:** `includes/class-rest-api.php`

REST API Endpoints.

```php
// Registrierte Routes
/irp/v1/calculate/rental      POST
/irp/v1/calculate/comparison  POST
/irp/v1/calculate/sale_value  POST
/irp/v1/leads                 POST
/irp/v1/leads/partial         POST
/irp/v1/leads/complete        POST
/irp/v1/cities                GET
/irp/v1/locations             GET
```

### IRP_Leads

**Datei:** `includes/class-leads.php`

Lead-Verwaltung und Datenbank-Operationen.

```php
// Methoden
create(array $data): int|WP_Error
create_partial(array $data): int|WP_Error
complete(int $lead_id, array $data): array|WP_Error
get(int $id): object|null
get_all(array $args): array
delete(int $id): bool
send_notification(int $lead_id): bool
```

### IRP_Propstack

**Datei:** `includes/class-propstack.php`

Propstack CRM Integration.

```php
// Methoden
sync_lead(int $lead_id): bool|WP_Error
test_connection(): bool|WP_Error
get_brokers(): array
get_broker_for_city(string $city_id): int|null
create_activity(int $contact_id, int $lead_id): bool
```

### IRP_PDF_Generator

**Datei:** `includes/class-pdf-generator.php`

PDF-Generierung mit DOMPDF.

```php
// Methoden
generate(int $lead_id, string $mode = 'rental'): string  // Gibt Dateipfad zurück
get_template(string $mode): string
render_html(object $lead, array $settings): string
```

---

## React-Komponenten

### App.js

Hauptkomponente mit State-Management.

**State-Flow:**
```
MODE_SELECT → CALCULATOR → CALCULATION_PENDING → CONTACT_FORM → RESULTS
```

**Props vom Shortcode:**
```javascript
{
    instanceId: 'uuid',
    mode: 'rental|comparison|sale_value|null',
    cityId: 'muenchen|null',
    cityName: 'München|null',
    theme: 'light|dark',
    showBranding: true|false
}
```

### Calculator-Komponenten

| Komponente | Modi | Beschreibung |
|------------|------|--------------|
| `RentalCalculator` | rental | Mietwert-Wizard |
| `ComparisonCalculator` | comparison | Vergleichs-Wizard |
| `SaleValueCalculator` | sale_value | Verkaufswert-Wizard |

### Step-Komponenten

Jede Step-Komponente erhält:

```javascript
{
    formData: object,        // Aktueller Formular-State
    updateFormData: func,    // State-Update-Funktion
    onNext: func,            // Zum nächsten Step
    onBack: func,            // Zum vorherigen Step
    settings: object,        // Plugin-Settings
    priceMatrix: object      // Preismatrix
}
```

### Lokalisierte Daten

Verfügbar über `window.irpSettings`:

```javascript
{
    ajaxUrl: '/wp-admin/admin-ajax.php',
    restUrl: '/wp-json/irp/v1/',
    nonce: 'abc123',
    pluginUrl: '/wp-content/plugins/immobilien-rechner-pro-v2/',
    settings: {
        primaryColor: '#2563eb',
        secondaryColor: '#1e40af',
        companyName: 'Firma GmbH',
        companyLogo: 'https://...',
        requireConsent: true,
        privacyPolicyUrl: '/datenschutz',
        googleMapsApiKey: 'AIza...',
        recaptchaSiteKey: '6Lc...',
        gadsConversionId: 'AW-123456789',
        calculatorMaxWidth: 680
    },
    priceMatrix: {
        cities: [...],
        condition_multipliers: {...},
        ...
    },
    locationRatings: [...],
    i18n: {
        // Übersetzungen
    }
}
```

---

## Datenbank-Schema

### wp_irp_leads

```sql
CREATE TABLE wp_irp_leads (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Kontaktdaten
    name VARCHAR(255),
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),

    -- Modus und Immobilien-Grunddaten
    mode VARCHAR(20) NOT NULL DEFAULT 'rental',
    property_type VARCHAR(50),
    property_size DECIMAL(10,2),
    land_size DECIMAL(10,2),

    -- Sale-Value-spezifische Felder
    house_type VARCHAR(50),
    build_year INT(4),
    modernization VARCHAR(50),
    quality VARCHAR(50),
    usage_type VARCHAR(50),
    sale_intention VARCHAR(50),
    timeframe VARCHAR(50),

    -- Lokalisierung
    property_location VARCHAR(255),
    zip_code VARCHAR(10),
    street_address VARCHAR(255),

    -- Berechnungen und Zustimmungen
    calculation_data LONGTEXT,
    consent TINYINT(1) DEFAULT 0,
    newsletter_consent TINYINT(1) DEFAULT 0,

    -- Status-Tracking
    status VARCHAR(20) DEFAULT 'partial',
    recaptcha_score DECIMAL(3,2),
    ip_address VARCHAR(45),
    source VARCHAR(100),

    -- Zeitstempel
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME,
    email_sent TINYINT(1) DEFAULT 0,
    email_sent_at DATETIME,

    -- Propstack-Integration
    propstack_id BIGINT(20) UNSIGNED,
    propstack_synced TINYINT(1) DEFAULT 0,
    propstack_error TEXT,
    propstack_synced_at DATETIME,

    -- Indizes
    KEY idx_email (email),
    KEY idx_mode (mode),
    KEY idx_status (status),
    KEY idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### wp_irp_calculations

```sql
CREATE TABLE wp_irp_calculations (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id BIGINT(20) UNSIGNED,
    session_id VARCHAR(64) NOT NULL,
    mode VARCHAR(20) NOT NULL,
    input_data LONGTEXT NOT NULL,
    result_data LONGTEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    KEY idx_lead_id (lead_id),
    KEY idx_session_id (session_id),
    KEY idx_mode (mode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## WordPress Options

| Option | Typ | Beschreibung |
|--------|-----|--------------|
| `irp_db_version` | string | Aktuelle Datenbank-Version |
| `irp_settings` | array | Haupteinstellungen |
| `irp_price_matrix` | array | Städte, Multiplikatoren, Features |
| `irp_email_settings` | array | E-Mail-Konfiguration |
| `irp_propstack_settings` | array | Propstack-Integration |
| `irp_sale_value_settings` | array | Verkaufswert-spezifisch |

### irp_settings Struktur

```php
[
    'primary_color' => '#2563eb',
    'secondary_color' => '#1e40af',
    'calculator_max_width' => 680,
    'company_name' => '',
    'company_logo' => '',
    'company_email' => '',
    'require_consent' => true,
    'privacy_policy_url' => '',
    'default_maintenance_rate' => 1.5,
    'default_vacancy_rate' => 3,
    'default_broker_commission' => 3.57,
    'enable_pdf_export' => false,
    'google_maps_api_key' => '',
    'show_map_in_location_step' => false,
    'recaptcha_site_key' => '',
    'recaptcha_secret_key' => '',
    'recaptcha_threshold' => 0.5,
    'gads_conversion_id' => '',
    'gads_partial_label' => '',
    'gads_complete_label' => ''
]
```

### irp_price_matrix Struktur

```php
[
    'cities' => [
        [
            'id' => 'muenchen',
            'name' => 'München',
            'base_price' => 19.00,
            'size_degression' => 0.20,
            'sale_factor' => 35,
            // Verkaufswert-Felder
            'land_price_per_sqm' => 1800,
            'building_price_per_sqm' => 3500,
            'apartment_price_per_sqm' => 8500,
            'market_adjustment_factor' => 1.35
        ],
        // ...
    ],
    'condition_multipliers' => [
        'new' => 1.25,
        'renovated' => 1.10,
        'good' => 1.00,
        'needs_renovation' => 0.80
    ],
    'type_multipliers' => [
        'apartment' => 1.00,
        'house' => 1.15,
        'commercial' => 0.85
    ],
    'feature_premiums' => [
        'balcony' => 0.50,
        'terrace' => 0.75,
        // ...
    ],
    'location_ratings' => [
        ['rating' => 1, 'multiplier' => 0.85],
        ['rating' => 2, 'multiplier' => 0.95],
        ['rating' => 3, 'multiplier' => 1.00],
        ['rating' => 4, 'multiplier' => 1.10],
        ['rating' => 5, 'multiplier' => 1.25]
    ],
    'age_multipliers' => [
        'before_1945' => 1.05,
        '1946_1959' => 0.95,
        // ...
    ],
    'interest_rate' => 3.0,
    'appreciation_rate' => 2.0,
    'rent_increase_rate' => 2.0
]
```

---

## Hooks & Filter

### Actions

```php
// Plugin-Lifecycle
do_action('irp_activated');
do_action('irp_deactivated');

// Lead-Events
do_action('irp_lead_created', $lead_id, $data);
do_action('irp_lead_completed', $lead_id, $data);
do_action('irp_lead_deleted', $lead_id);

// Berechnungen
do_action('irp_calculation_completed', $mode, $result, $input);

// Propstack
do_action('irp_propstack_synced', $lead_id, $propstack_id);
do_action('irp_propstack_sync_failed', $lead_id, $error);
```

### Filter

```php
// Berechnungen anpassen
apply_filters('irp_rental_result', $result, $input);
apply_filters('irp_comparison_result', $result, $input);
apply_filters('irp_sale_value_result', $result, $input);

// Multiplikatoren anpassen
apply_filters('irp_condition_multiplier', $multiplier, $condition);
apply_filters('irp_location_multiplier', $multiplier, $rating);
apply_filters('irp_age_multiplier', $multiplier, $year_built);

// E-Mail anpassen
apply_filters('irp_email_subject', $subject, $lead);
apply_filters('irp_email_content', $content, $lead);
apply_filters('irp_email_headers', $headers, $lead);

// PDF anpassen
apply_filters('irp_pdf_html', $html, $lead);
apply_filters('irp_pdf_options', $options);

// REST API
apply_filters('irp_rest_response', $response, $request);
```

### Beispiel: Eigenen Multiplikator hinzufügen

```php
add_filter('irp_rental_result', function($result, $input) {
    // Beispiel: 5% Aufschlag für barrierefreie Wohnungen
    if (in_array('barrier_free', $input['features'] ?? [])) {
        $result['monthly_rent'] *= 1.05;
    }
    return $result;
}, 10, 2);
```

---

## Technologie-Stack

### Backend

| Technologie | Version | Verwendung |
|-------------|---------|------------|
| PHP | 7.4+ | Server-Logik |
| WordPress | 6.0+ | CMS-Plattform |
| DOMPDF | 2.0.4 | PDF-Generierung |

### Frontend

| Technologie | Version | Verwendung |
|-------------|---------|------------|
| React | 18 | UI-Framework |
| @wordpress/element | - | WordPress React-Wrapper |
| @wordpress/api-fetch | - | REST API Client |
| Framer Motion | - | Animationen |
| ApexCharts | - | Diagramme |
| Heroicons | - | Icons |
| SCSS | - | Styling |

### Build

| Tool | Verwendung |
|------|------------|
| @wordpress/scripts | Build-Konfiguration |
| webpack | Bundling |
| Babel | Transpilation |
| PostCSS | CSS-Processing |

---

## Build-Prozess

### Entwicklung

```bash
# Abhängigkeiten installieren
npm install

# Development-Build mit Watch
npm run start

# Einmaliger Development-Build
npm run build
```

### Produktion

```bash
# Optimierter Production-Build
npm run build

# Mit Source Maps
npm run build:dev
```

### Dateien nach Build

```
build/
├── index.js           # Kompiliertes JavaScript
├── index.css          # Kompiliertes CSS
├── index.asset.php    # Dependencies und Version
└── *.map              # Source Maps (optional)
```

### Release erstellen

1. Version in `immobilien-rechner-pro.php` erhöhen
2. CHANGELOG.md aktualisieren
3. `npm run build` ausführen
4. Git-Tag erstellen und pushen
5. GitHub Release mit ZIP erstellen

---

## Konstanten

```php
// Plugin-Verzeichnisse
IRP_VERSION           // '2.1.1'
IRP_PLUGIN_DIR        // Absoluter Pfad zum Plugin
IRP_PLUGIN_URL        // URL zum Plugin
IRP_PLUGIN_BASENAME   // 'immobilien-rechner-pro-v2/immobilien-rechner-pro.php'
IRP_GITHUB_REPO       // 'AImitSK/immobilien-rechner-pro-v2'
```

---

## Debugging

### Debug-Modus aktivieren

In `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('IRP_DEBUG', true);
```

### Debug-Logging

```php
// Im Plugin-Code
if (defined('IRP_DEBUG') && IRP_DEBUG) {
    error_log('[IRP] ' . print_r($data, true));
}
```

### React DevTools

Das Plugin unterstützt React DevTools im Entwicklungsmodus.

### REST API testen

```bash
# Mit WP-CLI
wp rest get /irp/v1/cities

# Mit cURL
curl -X POST https://example.com/wp-json/irp/v1/calculate/rental \
  -H "Content-Type: application/json" \
  -d '{"property_type":"apartment","size":85,"city_id":"muenchen","condition":"good","location_rating":3}'
```
