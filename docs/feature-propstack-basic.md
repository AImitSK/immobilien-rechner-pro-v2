# Feature: Propstack Integration - Basic

## Ziel

Absolut minimale Integration:
1. Leads werden an Propstack gesendet
2. Als "Immobilien-Rechner" Anfrage gekennzeichnet

**Kein:**
- Newsletter
- Makler-Zuweisung
- Retry-Mechanismus
- Kontaktquellen-Dropdown

---

## Umfang Basic

| Feature | Enthalten |
|---------|-----------|
| Lead an Propstack senden | ✅ |
| Als "Immo-Rechner" kennzeichnen | ✅ |
| API-Key Einstellung | ✅ |
| Verbindungstest | ✅ |
| Sync-Status am Lead | ✅ |
| Newsletter | ❌ |
| Kontaktquellen-Auswahl | ❌ |
| Makler-Zuweisung | ❌ |
| Fehler-Retry UI | ❌ |

---

## Admin-Einstellungen

### Neuer Tab "Propstack" in Einstellungen

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
└─────────────────────────────────────────────────────────────┘
```

---

## API-Aufruf

### POST /v1/contacts

```json
{
  "client": {
    "first_name": "Max",
    "last_name": "Mustermann",
    "email": "max@beispiel.de",
    "phone": "+49 123 456789",
    "description": "📊 Anfrage über Immobilien-Rechner Pro\n\n--- Berechnungsergebnis ---\nModus: Mietwertberechnung\nObjekttyp: Wohnung\nGröße: 85 m²\nStadt: Berlin\nZustand: Gut\nLage-Bewertung: 4/5\n\n💰 Geschätzte Miete: 1.250 €/Monat\n📐 Preis pro m²: 14,70 €"
  }
}
```

Die Kennzeichnung erfolgt über das Präfix "📊 Anfrage über Immobilien-Rechner Pro" im Beschreibungsfeld.

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
| Erfolgreich | ✅ |
| Fehler | ❌ |
| Nicht aktiv | — |

---

## Implementierung

### Neue Datei

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
        $response = self::request('GET', '/brokers');
        return is_wp_error($response) ? $response : true;
    }

    /**
     * Erstellt einen Kontakt in Propstack
     */
    public static function create_contact(array $lead_data): int|WP_Error {
        $client = [
            'first_name' => $lead_data['name'] ?? '',
            'email' => $lead_data['email'],
            'phone' => $lead_data['phone'] ?? '',
            'description' => self::build_description($lead_data),
        ];

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
]
```

---

## Checkliste Basic

- [ ] `class-propstack.php` erstellen
- [ ] Einstellungs-UI in Admin (Tab in Settings)
- [ ] API-Key speichern
- [ ] Verbindungstest implementieren
- [ ] Datenbank-Migration (3 neue Felder)
- [ ] Hook nach Lead-Completion
- [ ] Status-Spalte in Lead-Liste
- [ ] Testen mit echtem API-Key

---

## Später erweitern

Nach erfolgreichem Basic:
1. **MVP**: + Newsletter DOI + Kontaktquellen-Auswahl
2. **Phase 2**: + Makler-Zuweisung + Retry-Button + Bulk-Sync
