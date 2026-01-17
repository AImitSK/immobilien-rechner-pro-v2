# Feature: Propstack Integration - MVP (Minimal Viable Product)

## Ziel

Minimale Integration:
1. Leads werden an Propstack gesendet
2. Als "Immobilien-Rechner" Anfrage gekennzeichnet
3. Bei Newsletter-Consent: In Newsletter-Liste eintragen

**Kein:**
- Makler-Zuweisung nach Stadt
- Suchprofile
- Retry-Mechanismus

---

## Umfang MVP

| Feature | Enthalten |
|---------|-----------|
| Lead an Propstack senden | ✅ |
| Als "Immo-Rechner" kennzeichnen | ✅ |
| Newsletter-Liste eintragen | ✅ |
| API-Key Einstellung | ✅ |
| Verbindungstest | ✅ |
| Sync-Status am Lead | ✅ |
| Makler-Zuweisung | ❌ |
| Fehler-Retry UI | ❌ |

---

## Admin-Einstellungen

### Neuer Tab in "Einstellungen" oder eigene Seite "Integrationen"

```
┌─────────────────────────────────────────────────────────────┐
│ Propstack Integration                                       │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ☑ Integration aktivieren                                   │
│                                                             │
│  API-Key:                                                   │
│  [••••••••••••••••••••••••••••••••]                         │
│                                                             │
│  [Verbindung testen]  ✅ Verbunden                          │
│                                                             │
│  ───────────────────────────────────────────────────────── │
│                                                             │
│  Kontaktquelle (optional):                                  │
│  [Dropdown: Immobilien-Rechner ▼]  [Neu laden]              │
│                                                             │
│  Hinweis: Wenn keine Quelle ausgewählt wird, wird           │
│  "Immobilien-Rechner Pro" im Beschreibungsfeld vermerkt.    │
│                                                             │
│  ───────────────────────────────────────────────────────── │
│                                                             │
│  Newsletter-Einstellungen:                                  │
│                                                             │
│  ☑ Newsletter-Anmeldung aktivieren                          │
│                                                             │
│  Textbaustein-ID für DOI-Mail:                              │
│  [1234                                        ]             │
│                                                             │
│  Absender (Broker-ID):                                      │
│  [Dropdown: Info Account ▼]                                 │
│                                                             │
│  Hinweis: Der Textbaustein muss in Propstack angelegt sein  │
│  und die Variable {{ kontakt_link }} enthalten.             │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

## API-Aufruf

### POST /v1/contacts

```json
{
  "client": {
    "salutation": "mr",
    "first_name": "Max",
    "last_name": "Mustermann",
    "email": "max@beispiel.de",
    "phone": "+49 123 456789",
    "client_source_id": 123,
    "description": "📊 Anfrage über Immobilien-Rechner Pro\n\n--- Berechnungsergebnis ---\nModus: Mietwertberechnung\nObjekttyp: Wohnung\nGröße: 85 m²\nStadt: Berlin\nZustand: Gut\nLage-Bewertung: 4/5\n\n💰 Geschätzte Miete: 1.250 €/Monat\n📐 Preis pro m²: 14,70 €\n\n--- Ausstattung ---\n✓ Balkon\n✓ Einbauküche\n✓ Aufzug"
  }
}
```

### Kennzeichnung als Immo-Rechner Lead

**Option A: Über Kontaktquelle (empfohlen)**
- `client_source_id` setzen
- Kontaktquelle muss in Propstack angelegt sein (z.B. "Immobilien-Rechner")
- Ermöglicht Filterung und Auswertungen in Propstack

**Option B: Über Beschreibung (Fallback)**
- Präfix im `description` Feld: "📊 Anfrage über Immobilien-Rechner Pro"
- Funktioniert immer, auch ohne Kontaktquelle

**Umsetzung:** Beide kombinieren - Kontaktquelle wenn vorhanden, Beschreibung immer.

---

## Newsletter Double-Opt-In

### Ablauf

```
Lead mit newsletter_consent = true
         │
         ▼
┌─────────────────────────────────────────┐
│  1. Kontakt in Propstack anlegen        │
│     (wie oben beschrieben)              │
└─────────────────┬───────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────┐
│  2. POST /v1/messages                   │
│     - broker_id: Absender               │
│     - to: [email]                       │
│     - snippet_id: Textbaustein          │
└─────────────────┬───────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────┐
│  Propstack sendet DOI-Mail mit          │
│  {{ kontakt_link }}                     │
└─────────────────┬───────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────┐
│  User klickt Link → Newsletter-Flags    │
│  werden in Propstack aktiviert:         │
│  - "Newsletter gewünscht"               │
│  - "Kontaktierung erlaubt"              │
└─────────────────────────────────────────┘
```

### API-Aufruf Newsletter

```json
POST /v1/messages

{
  "message": {
    "broker_id": 123,
    "to": ["max@beispiel.de"],
    "snippet_id": 1234
  }
}
```

### Voraussetzung in Propstack

1. **Textbaustein anlegen** mit:
   - Betreff: "Bitte bestätigen Sie Ihre Newsletter-Anmeldung"
   - Inhalt mit `{{ kontakt_link }}` Variable

2. **Broker mit E-Mail-Konto** für Versand (z.B. info@firma.de)

---

## Datenbank-Erweiterung

```sql
ALTER TABLE wp_irp_leads
ADD COLUMN propstack_id BIGINT DEFAULT NULL,
ADD COLUMN propstack_synced TINYINT(1) DEFAULT 0,
ADD COLUMN propstack_error VARCHAR(255) DEFAULT NULL;
```

---

## Lead-Liste Anpassung

Neue Spalte "Propstack":

| Status | Anzeige |
|--------|---------|
| Erfolgreich | ✅ Sync |
| Fehler | ❌ Fehler |
| Nicht aktiv | — |

Kein Retry-Button im MVP - nur Status-Anzeige.

---

## Implementierung

### Neue Dateien

```
includes/
└── class-propstack.php
```

### Klasse: IRP_Propstack

```php
<?php
class IRP_Propstack {

    private const API_BASE = 'https://api.propstack.de/v1';

    /**
     * Prüft ob Integration aktiv und konfiguriert
     */
    public static function is_enabled(): bool {
        $settings = get_option('irp_propstack_settings', []);
        return !empty($settings['enabled']) && !empty($settings['api_key']);
    }

    /**
     * Testet die API-Verbindung
     */
    public static function test_connection(): bool|WP_Error {
        $response = self::request('GET', '/contact_sources');
        return is_wp_error($response) ? $response : true;
    }

    /**
     * Holt Kontaktquellen für Dropdown
     */
    public static function get_contact_sources(): array|WP_Error {
        return self::request('GET', '/contact_sources');
    }

    /**
     * Holt Broker/Nutzer für Dropdown
     */
    public static function get_brokers(): array|WP_Error {
        return self::request('GET', '/brokers');
    }

    /**
     * Sendet Newsletter DOI-Mail
     */
    public static function send_newsletter_doi(string $email): bool|WP_Error {
        $settings = get_option('irp_propstack_settings', []);

        if (empty($settings['newsletter_enabled'])) {
            return false;
        }

        if (empty($settings['newsletter_snippet_id']) || empty($settings['newsletter_broker_id'])) {
            return new WP_Error('config_error', 'Newsletter nicht konfiguriert');
        }

        $response = self::request('POST', '/messages', [
            'message' => [
                'broker_id' => (int) $settings['newsletter_broker_id'],
                'to' => [$email],
                'snippet_id' => (int) $settings['newsletter_snippet_id'],
            ]
        ]);

        return is_wp_error($response) ? $response : true;
    }

    /**
     * Erstellt einen Kontakt in Propstack
     */
    public static function create_contact(array $lead_data): int|WP_Error {
        $settings = get_option('irp_propstack_settings', []);

        $client = [
            'first_name' => $lead_data['name'] ?? '',
            'email' => $lead_data['email'],
            'phone' => $lead_data['phone'] ?? '',
            'description' => self::build_description($lead_data),
        ];

        // Kontaktquelle wenn konfiguriert
        if (!empty($settings['contact_source_id'])) {
            $client['client_source_id'] = (int) $settings['contact_source_id'];
        }

        $response = self::request('POST', '/contacts', ['client' => $client]);

        if (is_wp_error($response)) {
            return $response;
        }

        return $response['id'] ?? 0;
    }

    /**
     * Baut die Beschreibung für den Kontakt
     */
    private static function build_description(array $data): string {
        $lines = ["📊 Anfrage über Immobilien-Rechner Pro", ""];

        // Berechnungsdaten
        $calc = $data['calculation_data'] ?? [];
        if (!empty($calc)) {
            $lines[] = "--- Berechnungsergebnis ---";
            $lines[] = "Modus: " . ($data['mode'] === 'rental' ? 'Mietwertberechnung' : 'Verkaufen vs. Vermieten');

            if (!empty($calc['property_type'])) {
                $lines[] = "Objekttyp: " . ucfirst($calc['property_type']);
            }
            if (!empty($calc['size'])) {
                $lines[] = "Größe: " . $calc['size'] . " m²";
            }
            if (!empty($calc['city_name'])) {
                $lines[] = "Stadt: " . $calc['city_name'];
            }
            if (!empty($calc['condition'])) {
                $lines[] = "Zustand: " . ucfirst($calc['condition']);
            }
            if (!empty($calc['location_rating'])) {
                $lines[] = "Lage-Bewertung: " . $calc['location_rating'] . "/5";
            }

            // Ergebnis
            $result = $calc['result'] ?? [];
            if (!empty($result['monthly_rent']['estimate'])) {
                $lines[] = "";
                $lines[] = "💰 Geschätzte Miete: " . number_format($result['monthly_rent']['estimate'], 0, ',', '.') . " €/Monat";
            }
            if (!empty($result['price_per_sqm'])) {
                $lines[] = "📐 Preis pro m²: " . number_format($result['price_per_sqm'], 2, ',', '.') . " €";
            }
        }

        return implode("\n", $lines);
    }

    /**
     * API Request Helper
     */
    private static function request(string $method, string $endpoint, array $body = []): array|WP_Error {
        $settings = get_option('irp_propstack_settings', []);
        $api_key = $settings['api_key'] ?? '';

        if (empty($api_key)) {
            return new WP_Error('no_api_key', 'Propstack API-Key nicht konfiguriert');
        }

        $args = [
            'method' => $method,
            'headers' => [
                'X-API-KEY' => $api_key,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'timeout' => 15,
        ];

        if (!empty($body)) {
            $args['body'] = json_encode($body);
        }

        $response = wp_remote_request(self::API_BASE . $endpoint, $args);

        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);
        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($code >= 400) {
            $error_msg = $data['errors'][0] ?? 'Unbekannter Fehler';
            return new WP_Error('api_error', $error_msg);
        }

        return $data;
    }
}
```

---

## Hook: Lead-Sync

```php
// In class-leads.php oder class-rest-api.php

add_action('irp_lead_completed', function($lead_id, $lead_data) {
    if (!IRP_Propstack::is_enabled()) {
        return;
    }

    $result = IRP_Propstack::create_contact($lead_data);

    global $wpdb;
    $table = $wpdb->prefix . 'irp_leads';

    if (is_wp_error($result)) {
        $wpdb->update($table, [
            'propstack_synced' => 0,
            'propstack_error' => $result->get_error_message(),
        ], ['id' => $lead_id]);
    } else {
        $wpdb->update($table, [
            'propstack_id' => $result,
            'propstack_synced' => 1,
            'propstack_error' => null,
        ], ['id' => $lead_id]);
    }
}, 10, 2);
```

---

## Einstellungen speichern

```php
// Option Name: irp_propstack_settings
[
    'enabled' => true,
    'api_key' => 'QMQzktjp0-xxx',
    'contact_source_id' => 123,  // Optional
]
```

---

## Checkliste MVP

- [ ] `class-propstack.php` erstellen
- [ ] Einstellungs-UI in Admin (Tab oder eigene Seite)
- [ ] API-Key speichern
- [ ] Verbindungstest implementieren
- [ ] Kontaktquellen-Dropdown (optional)
- [ ] Datenbank-Migration (3 neue Felder)
- [ ] Hook nach Lead-Completion
- [ ] Status-Spalte in Lead-Liste
- [ ] Testen mit echtem API-Key

---

## Später erweitern (Phase 2)

Nach erfolgreichem MVP:
- Makler-Zuweisung nach Stadt
- Newsletter Double-Opt-In
- Retry-Button bei Fehlern
- Bulk-Sync
