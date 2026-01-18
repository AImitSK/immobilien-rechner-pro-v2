# REST API Referenz

Dokumentation der REST API Endpoints des Immobilien Rechner Pro.

**Basis-URL:** `/wp-json/irp/v1/`

---

## Inhaltsverzeichnis

1. [Berechnungs-Endpoints](#berechnungs-endpoints)
2. [Lead-Endpoints](#lead-endpoints)
3. [Daten-Endpoints](#daten-endpoints)
4. [Response-Format](#response-format)

---

## Berechnungs-Endpoints

### POST /calculate/rental

Berechnet den Mietwert einer Immobilie.

**Request:**

```json
{
    "property_type": "apartment",
    "size": 85,
    "rooms": 3,
    "city_id": "muenchen",
    "condition": "renovated",
    "location_rating": 4,
    "year_built": 1995,
    "features": ["balcony", "fitted_kitchen", "elevator"],
    "address": "Maximilianstraße 1"
}
```

**Parameter:**

| Parameter | Typ | Pflicht | Werte |
|-----------|-----|---------|-------|
| `property_type` | string | Ja | `apartment`, `house`, `commercial` |
| `size` | number | Ja | Fläche in m² |
| `rooms` | number | Nein | Anzahl Zimmer |
| `city_id` | string | Ja | Stadt-ID aus Matrix |
| `condition` | string | Ja | `new`, `renovated`, `good`, `needs_renovation` |
| `location_rating` | number | Ja | 1-5 |
| `year_built` | number | Nein | Baujahr |
| `features` | array | Nein | Array von Feature-IDs |
| `address` | string | Nein | Adresse |

**Response:**

```json
{
    "success": true,
    "data": {
        "monthly_rent": 1450.50,
        "yearly_rent": 17406.00,
        "rent_per_sqm": 17.06,
        "rent_range": {
            "min": 1305.45,
            "max": 1595.55
        },
        "gross_yield": 4.2,
        "net_yield": 3.5,
        "maintenance_costs": 217.59,
        "vacancy_loss": 522.18,
        "factors_applied": {
            "condition": 1.10,
            "type": 1.00,
            "location": 1.10,
            "age": 1.00,
            "features_bonus": 1.20
        }
    }
}
```

---

### POST /calculate/comparison

Vergleicht Verkaufen vs. Vermieten.

**Request:**

```json
{
    "property_type": "apartment",
    "size": 85,
    "city_id": "muenchen",
    "condition": "good",
    "location_rating": 3,
    "features": ["balcony"],
    "property_value": 450000,
    "remaining_mortgage": 150000,
    "mortgage_rate": 3.5,
    "holding_period_years": 10,
    "expected_appreciation": 2.0
}
```

**Zusätzliche Parameter:**

| Parameter | Typ | Pflicht | Beschreibung |
|-----------|-----|---------|--------------|
| `property_value` | number | Ja | Aktueller Immobilienwert in € |
| `remaining_mortgage` | number | Nein | Restschuld in € |
| `mortgage_rate` | number | Nein | Hypothekenzins in % |
| `holding_period_years` | number | Ja | Geplante Haltedauer |
| `expected_appreciation` | number | Nein | Erwartete Wertsteigerung p.a. |

**Response:**

```json
{
    "success": true,
    "data": {
        "rental_scenario": {
            "monthly_rent": 1200.00,
            "yearly_rent": 14400.00,
            "total_rent_income": 144000.00,
            "property_value_end": 548673.64,
            "total_wealth": 692673.64
        },
        "sale_scenario": {
            "gross_proceeds": 450000.00,
            "broker_commission": 16065.00,
            "net_proceeds": 433935.00,
            "invested_value_end": 583452.87,
            "total_wealth": 583452.87
        },
        "recommendation": "rent",
        "break_even_years": 15,
        "wealth_difference": 109220.77
    }
}
```

---

### POST /calculate/sale_value

Berechnet den Verkaufswert einer Immobilie.

**Request (Wohnung):**

```json
{
    "property_type": "apartment",
    "living_space": 85,
    "city_id": "muenchen",
    "build_year": 1995,
    "modernization": "4-9_years",
    "quality": "upscale",
    "location_rating": 4,
    "features": ["balcony", "fitted_kitchen", "elevator"]
}
```

**Request (Haus):**

```json
{
    "property_type": "house",
    "living_space": 150,
    "land_size": 500,
    "house_type": "single_family",
    "city_id": "muenchen",
    "build_year": 1985,
    "modernization": "1-3_years",
    "quality": "upscale",
    "location_rating": 4,
    "features": ["garage", "garden", "terrace", "solar"]
}
```

**Request (Grundstück):**

```json
{
    "property_type": "land",
    "land_size": 800,
    "city_id": "muenchen",
    "location_rating": 3
}
```

**Parameter:**

| Parameter | Typ | Pflicht | Werte |
|-----------|-----|---------|-------|
| `property_type` | string | Ja | `apartment`, `house`, `land` |
| `living_space` | number | Ja* | Wohnfläche in m² (* nicht bei land) |
| `land_size` | number | Ja* | Grundstücksfläche (* nur bei house/land) |
| `house_type` | string | Nein | Haustyp (nur bei house) |
| `city_id` | string | Ja | Stadt-ID |
| `build_year` | number | Ja* | Baujahr (* nicht bei land) |
| `modernization` | string | Nein | Modernisierungsstatus |
| `quality` | string | Ja* | Qualitätsstufe (* nicht bei land) |
| `location_rating` | number | Ja | 1-5 |
| `features` | array | Nein | Ausstattungsmerkmale |

**Werte für `house_type`:**
- `single_family` - Einfamilienhaus
- `multi_family` - Mehrfamilienhaus
- `semi_detached` - Doppelhaushälfte
- `townhouse_middle` - Mittelreihenhaus
- `townhouse_end` - Endreihenhaus
- `bungalow` - Bungalow

**Werte für `modernization`:**
- `1-3_years` - Vor 1-3 Jahren
- `4-9_years` - Vor 4-9 Jahren
- `10-15_years` - Vor 10-15 Jahren
- `over_15_years` - Vor mehr als 15 Jahren
- `never` - Noch nie

**Werte für `quality`:**
- `simple` - Einfach
- `normal` - Normal
- `upscale` - Gehoben
- `luxury` - Luxuriös

**Response (Haus):**

```json
{
    "success": true,
    "data": {
        "price_estimate": 785000,
        "price_min": 745750,
        "price_max": 824250,
        "calculation_type": "house",
        "land_value": 90000,
        "building_value": 654000,
        "features_value": 41000,
        "price_per_sqm_living": 5233,
        "price_per_sqm_land": 180,
        "factors": {
            "house_type": 1.00,
            "quality": 1.15,
            "location": 1.10,
            "age": 0.85,
            "market": 1.35
        }
    }
}
```

**Response (Wohnung):**

```json
{
    "success": true,
    "data": {
        "price_estimate": 425000,
        "price_min": 403750,
        "price_max": 446250,
        "calculation_type": "apartment",
        "features_value": 33000,
        "price_per_sqm_living": 5000,
        "factors": {
            "quality": 1.15,
            "location": 1.10,
            "age": 0.90,
            "market": 1.35
        }
    }
}
```

---

## Lead-Endpoints

### POST /leads

Erstellt einen vollständigen Lead (Legacy-Endpoint).

**Request:**

```json
{
    "name": "Max Mustermann",
    "email": "max@example.com",
    "phone": "+49 123 456789",
    "mode": "rental",
    "property_type": "apartment",
    "property_size": 85,
    "property_location": "München",
    "calculation_data": { ... },
    "consent": true,
    "newsletter_consent": false
}
```

**Response:**

```json
{
    "success": true,
    "lead_id": 123,
    "message": "Vielen Dank! Wir werden uns in Kürze bei Ihnen melden."
}
```

---

### POST /leads/partial

Erstellt einen Partial Lead (nur Immobiliendaten, ohne Kontakt).

**Request:**

```json
{
    "mode": "sale_value",
    "property_type": "house",
    "property_size": 150,
    "land_size": 500,
    "house_type": "single_family",
    "build_year": 1985,
    "modernization": "1-3_years",
    "quality": "upscale",
    "property_location": "München",
    "zip_code": "80331",
    "street_address": "Maximilianstraße 1",
    "calculation_result": { ... }
}
```

**Parameter:**

| Parameter | Typ | Pflicht |
|-----------|-----|---------|
| `mode` | string | Ja |
| `property_type` | string | Ja |
| `property_size` | number | Ja |
| `land_size` | number | Nein |
| `house_type` | string | Nein |
| `build_year` | number | Nein |
| `modernization` | string | Nein |
| `quality` | string | Nein |
| `usage_type` | string | Nein |
| `sale_intention` | string | Nein |
| `timeframe` | string | Nein |
| `property_location` | string | Nein |
| `zip_code` | string | Nein |
| `street_address` | string | Nein |
| `calculation_result` | object | Nein |

**Response:**

```json
{
    "success": true,
    "lead_id": 124,
    "status": "partial"
}
```

---

### POST /leads/complete

Vervollständigt einen Partial Lead mit Kontaktdaten.

**Request:**

```json
{
    "lead_id": 124,
    "name": "Max Mustermann",
    "email": "max@example.com",
    "phone": "+49 123 456789",
    "consent": true,
    "newsletter_consent": true,
    "recaptcha_token": "03AGdBq24..."
}
```

**Parameter:**

| Parameter | Typ | Pflicht |
|-----------|-----|---------|
| `lead_id` | number | Ja |
| `name` | string | Ja |
| `email` | string | Ja |
| `phone` | string | Nein |
| `consent` | boolean | Ja |
| `newsletter_consent` | boolean | Nein |
| `recaptcha_token` | string | Nein* |

*Pflicht wenn reCAPTCHA aktiviert

**Response:**

```json
{
    "success": true,
    "lead_id": 124,
    "status": "complete",
    "calculation_data": {
        "price_estimate": 785000,
        "price_min": 745750,
        "price_max": 824250,
        ...
    },
    "message": "Vielen Dank! Ein Makler wird sich in Kürze bei Ihnen melden."
}
```

---

## Daten-Endpoints

### GET /cities

Ruft alle konfigurierten Städte ab.

**Response:**

```json
{
    "success": true,
    "data": [
        {
            "id": "muenchen",
            "name": "München",
            "base_price": 19.00,
            "sale_factor": 35
        },
        {
            "id": "berlin",
            "name": "Berlin",
            "base_price": 18.50,
            "sale_factor": 30
        }
    ]
}
```

---

### GET /locations

Sucht nach Orten (für Autocomplete).

**Request:**

```
GET /irp/v1/locations?search=Münch
```

**Response:**

```json
{
    "success": true,
    "data": [
        {
            "id": "muenchen",
            "name": "München",
            "match": "München"
        }
    ]
}
```

---

## Response-Format

### Erfolgreiche Response

```json
{
    "success": true,
    "data": { ... }
}
```

### Fehler-Response

```json
{
    "success": false,
    "code": "invalid_parameter",
    "message": "Der Parameter 'property_type' ist ungültig."
}
```

### HTTP Status Codes

| Code | Beschreibung |
|------|--------------|
| 200 | Erfolg |
| 400 | Ungültige Parameter |
| 401 | Nicht autorisiert |
| 404 | Nicht gefunden |
| 500 | Server-Fehler |

---

## Authentifizierung

Die öffentlichen Endpoints (calculate, cities, locations) benötigen keine Authentifizierung.

Für Lead-Endpoints wird optional ein WordPress Nonce verwendet:

**Header:**
```
X-WP-Nonce: [nonce_value]
```

Der Nonce wird automatisch vom React-Frontend mitgesendet.

---

## Rate Limiting

Für Lead-Endpoints ist Rate Limiting implementiert:

| Endpoint | Limit | Zeitraum |
|----------|-------|----------|
| `/leads/partial` | 10 Requests | pro Stunde |
| `/leads/complete` | 10 Requests | pro Stunde |

Das Limit gilt pro IP-Adresse. Bei Überschreitung wird HTTP 429 (Too Many Requests) zurückgegeben.

---

## Error-Codes

Das Plugin verwendet ein strukturiertes Error-Code-System für konsistente Fehlerbehandlung.

### Error-Code-Kategorien

| Bereich | Codes | Beschreibung |
|---------|-------|--------------|
| Validierung | E1xxx | Ungültige Eingabedaten |
| Authentifizierung | E2xxx | Berechtigungs- und Session-Fehler |
| Datenbank | E3xxx | Datenbank-Operationen |
| Externe APIs | E4xxx | Externe Dienste (Propstack, E-Mail) |
| System | E5xxx | Server- und Systemfehler |

### Fehler-Response-Format

```json
{
    "success": false,
    "code": "E1001",
    "message": "Bitte geben Sie eine gültige E-Mail-Adresse ein.",
    "data": null
}
```

### Validierungsfehler (E1xxx)

| Code | Beschreibung |
|------|--------------|
| E1001 | Ungültige E-Mail-Adresse |
| E1002 | Ungültige Telefonnummer |
| E1003 | Pflichtfeld fehlt |
| E1004 | Ungültige Wohnfläche (min. 10 m²) |
| E1005 | Ungültiger Immobilientyp |
| E1006 | Standort nicht gefunden |
| E1007 | Ungültige Postleitzahl |
| E1008 | Ungültiges Baujahr |
| E1009 | Ungültiger Preis |
| E1010 | Ungültige Eingabedaten |

### Authentifizierungsfehler (E2xxx)

| Code | Beschreibung |
|------|--------------|
| E2001 | Keine Berechtigung |
| E2002 | Session abgelaufen |
| E2003 | Ungültiger Nonce (CSRF) |
| E2004 | reCAPTCHA-Prüfung fehlgeschlagen |
| E2005 | Ungültiges Token |

### Datenbankfehler (E3xxx)

| Code | Beschreibung |
|------|--------------|
| E3001 | Datenbankverbindung fehlgeschlagen |
| E3002 | Datenbankabfrage fehlgeschlagen |
| E3003 | Lead nicht gefunden |
| E3004 | Doppelter Eintrag |
| E3005 | Einfügen fehlgeschlagen |
| E3006 | Aktualisieren fehlgeschlagen |
| E3007 | Löschen fehlgeschlagen |

### Externe API-Fehler (E4xxx)

| Code | Beschreibung |
|------|--------------|
| E4001 | API-Verbindung fehlgeschlagen |
| E4002 | Rate-Limit überschritten |
| E4003 | Ungültige API-Antwort |
| E4004 | Propstack-Sync fehlgeschlagen |
| E4005 | Geocoding fehlgeschlagen |
| E4006 | E-Mail-Versand fehlgeschlagen |

### Systemfehler (E5xxx)

| Code | Beschreibung |
|------|--------------|
| E5001 | Datei nicht gefunden |
| E5002 | Zugriff verweigert |
| E5003 | Speicherlimit erreicht |
| E5004 | Timeout |
| E5005 | PDF-Generierung fehlgeschlagen |
| E5006 | Export fehlgeschlagen |
| E5007 | Berechnung fehlgeschlagen |

---

## Beispiel-Integration

### JavaScript (Fetch)

```javascript
// Mietwert berechnen
const response = await fetch('/wp-json/irp/v1/calculate/rental', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({
        property_type: 'apartment',
        size: 85,
        city_id: 'muenchen',
        condition: 'good',
        location_rating: 3,
        features: ['balcony']
    })
});

const result = await response.json();

if (result.success) {
    console.log('Monatliche Miete:', result.data.monthly_rent);
}
```

### PHP (WordPress)

```php
// Intern: Direkte Klassen-Nutzung
$calculator = new IRP_Calculator();
$result = $calculator->calculate_rental([
    'property_type' => 'apartment',
    'size' => 85,
    'city_id' => 'muenchen',
    'condition' => 'good',
    'location_rating' => 3,
]);

// Extern: REST API
$response = wp_remote_post(
    rest_url('irp/v1/calculate/rental'),
    [
        'body' => json_encode($data),
        'headers' => ['Content-Type' => 'application/json'],
    ]
);
```

### cURL

```bash
curl -X POST https://example.com/wp-json/irp/v1/calculate/rental \
  -H "Content-Type: application/json" \
  -d '{
    "property_type": "apartment",
    "size": 85,
    "city_id": "muenchen",
    "condition": "good",
    "location_rating": 3
  }'
```
