# Feature: Lage-Bewertung

**Status:** Geplant
**Priorität:** Hoch
**Erstellt:** 2026-01-09

---

## Übersicht

Integration einer Lage-Bewertung in den Immobilien-Rechner. Der Benutzer gibt die Adresse ein, sieht diese auf einer Google Map und bewertet die Lage über einen Schieberegler. Die Bewertung fließt als Multiplikator in die Mietpreisberechnung ein.

---

## User Interface

### Neuer Step im Wizard: "Lage der Immobilie"

```
┌─────────────────────────────────────────────────────────────┐
│  LAGE DER IMMOBILIE                                         │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Adresse der Immobilie:                                     │
│  ┌─────────────────────────────────────────────────────┐    │
│  │ Musterstraße 123, 80331 München                     │    │
│  └─────────────────────────────────────────────────────┘    │
│        ↑ Google Places Autocomplete                        │
│                                                             │
│  ┌─────────────────────────────────────────────────────┐    │
│  │                                                     │    │
│  │              [Google Maps Kartenansicht]            │    │
│  │                                                     │    │
│  │                        📍                           │    │
│  │                                                     │    │
│  └─────────────────────────────────────────────────────┘    │
│                                                             │
│  Wie bewerten Sie die Lage?                                 │
│                                                             │
│     Einfach ──────●────────────────────── Premium           │
│        [1]   [2]   [3]   [4]   [5]                          │
│                                                             │
│  ┌─────────────────────────────────────────────────────┐    │
│  │ ⭐⭐⭐ GUTE LAGE                                      │    │
│  │                                                     │    │
│  │ • Gute Anbindung an öffentliche Verkehrsmittel     │    │
│  │ • Einkaufsmöglichkeiten und Schulen in der Nähe    │    │
│  │ • Ruhige Wohngegend                                │    │
│  │ • Gepflegtes Umfeld                                │    │
│  └─────────────────────────────────────────────────────┘    │
│                     ↑                                       │
│          Ändert sich dynamisch je nach Slider-Position      │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### Verhalten

1. **Adresseingabe**: Google Places Autocomplete für schnelle Eingabe
2. **Karte**: Zeigt die eingegebene Adresse mit Marker
3. **Schieberegler**: 5 Stufen von "Einfach" bis "Premium"
4. **Beschreibungsbox**: Aktualisiert sich live beim Verschieben des Reglers

---

## Die 5 Lage-Stufen

| Stufe | Name | Multiplikator | Sterne |
|-------|------|---------------|--------|
| 1 | Einfache Lage | ×0.85 | ⭐ |
| 2 | Normale Lage | ×0.95 | ⭐⭐ |
| 3 | Gute Lage | ×1.00 | ⭐⭐⭐ |
| 4 | Sehr gute Lage | ×1.10 | ⭐⭐⭐⭐ |
| 5 | Premium-Lage | ×1.25 | ⭐⭐⭐⭐⭐ |

### Default-Beschreibungen (konfigurierbar in Matrix)

**Stufe 1 - Einfache Lage:**
- Eingeschränkte Anbindung an öffentliche Verkehrsmittel
- Wenig Infrastruktur in direkter Umgebung
- Lärm durch Verkehr, Gewerbe oder Industrie
- Einfache Wohngegend

**Stufe 2 - Normale Lage:**
- Akzeptable Anbindung an öffentliche Verkehrsmittel
- Grundversorgung (Supermarkt) erreichbar
- Durchschnittliche Wohngegend
- Mäßiger Geräuschpegel

**Stufe 3 - Gute Lage:**
- Gute Anbindung an öffentliche Verkehrsmittel
- Einkaufsmöglichkeiten und Schulen in der Nähe
- Ruhige Wohngegend
- Gepflegtes Umfeld

**Stufe 4 - Sehr gute Lage:**
- Sehr gute Verkehrsanbindung (ÖPNV und Straße)
- Umfangreiche Infrastruktur (Ärzte, Restaurants, Kultur)
- Grünflächen und Parks in der Nähe
- Gehobene Wohngegend

**Stufe 5 - Premium-Lage:**
- Beste Verkehrsanbindung
- Exklusive Nachbarschaft
- Top-Infrastruktur und Freizeitmöglichkeiten
- Besondere Lagevorteile (Seenähe, Altstadt, Villenviertel)

---

## Admin-Bereich Erweiterungen

### 1. Settings-Seite: Google Maps API

**Pfad:** WordPress Admin → Immo Rechner → Settings

```
┌─────────────────────────────────────────────────────────────┐
│  GOOGLE MAPS INTEGRATION                                    │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Google Maps API Key:                                       │
│  ┌─────────────────────────────────────────────────────┐    │
│  │ AIzaSy.....................................         │    │
│  └─────────────────────────────────────────────────────┘    │
│                                                             │
│  ☑ Karte im Lage-Step anzeigen                             │
│                                                             │
│  ┌─────────────────────────────────────────────────────┐    │
│  │ ℹ️ So erhalten Sie einen API-Key:                    │    │
│  │                                                     │    │
│  │ 1. Google Cloud Console öffnen                      │    │
│  │ 2. Neues Projekt erstellen                          │    │
│  │ 3. APIs aktivieren:                                 │    │
│  │    • Maps JavaScript API                            │    │
│  │    • Places API                                     │    │
│  │ 4. API-Key erstellen und hier einfügen              │    │
│  │                                                     │    │
│  │ 🔗 console.cloud.google.com                         │    │
│  └─────────────────────────────────────────────────────┘    │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

**Felder:**
- `google_maps_api_key` (string) - Der API-Key
- `show_map_in_location_step` (boolean) - Karte anzeigen ja/nein

### 2. Matrix-Seite: Neuer Tab "Lage-Faktoren"

**Pfad:** WordPress Admin → Immo Rechner → Matrix & Daten → Lage-Faktoren

```
┌─────────────────────────────────────────────────────────────┐
│  LAGE-FAKTOREN                                              │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Konfigurieren Sie die Multiplikatoren und Beschreibungen   │
│  für die 5 Lage-Stufen.                                     │
│                                                             │
│  ┌───────────────────────────────────────────────────────┐  │
│  │ Stufe │ Bezeichnung    │ Faktor │ Beschreibung      │  │
│  ├───────┼────────────────┼────────┼───────────────────┤  │
│  │   1   │ Einfache Lage  │  0.85  │ [Bearbeiten]     │  │
│  │   2   │ Normale Lage   │  0.95  │ [Bearbeiten]     │  │
│  │   3   │ Gute Lage      │  1.00  │ [Bearbeiten]     │  │
│  │   4   │ Sehr gute Lage │  1.10  │ [Bearbeiten]     │  │
│  │   5   │ Premium-Lage   │  1.25  │ [Bearbeiten]     │  │
│  └───────────────────────────────────────────────────────┘  │
│                                                             │
│  Beschreibung bearbeiten (Stufe 3):                         │
│  ┌─────────────────────────────────────────────────────┐    │
│  │ • Gute Anbindung an öffentliche Verkehrsmittel     │    │
│  │ • Einkaufsmöglichkeiten und Schulen in der Nähe    │    │
│  │ • Ruhige Wohngegend                                │    │
│  │ • Gepflegtes Umfeld                                │    │
│  └─────────────────────────────────────────────────────┘    │
│  ℹ️ Jede Zeile wird als Aufzählungspunkt angezeigt.         │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

**Konfigurierbare Felder pro Stufe:**
- `name` (string) - Bezeichnung der Stufe
- `multiplier` (float) - Multiplikator für Mietpreis
- `description` (text) - Mehrzeilige Beschreibung (jede Zeile = ein Bullet Point)

---

## Datenstruktur

### Neue Felder in `irp_settings`

```php
[
    'google_maps_api_key' => 'AIzaSy...',
    'show_map_in_location_step' => true,
]
```

### Neue Felder in `irp_price_matrix`

```php
[
    'location_ratings' => [
        1 => [
            'name' => 'Einfache Lage',
            'multiplier' => 0.85,
            'description' => "Eingeschränkte Anbindung an öffentliche Verkehrsmittel\nWenig Infrastruktur in direkter Umgebung\nLärm durch Verkehr, Gewerbe oder Industrie\nEinfache Wohngegend"
        ],
        2 => [
            'name' => 'Normale Lage',
            'multiplier' => 0.95,
            'description' => "Akzeptable Anbindung an öffentliche Verkehrsmittel\nGrundversorgung (Supermarkt) erreichbar\nDurchschnittliche Wohngegend\nMäßiger Geräuschpegel"
        ],
        3 => [
            'name' => 'Gute Lage',
            'multiplier' => 1.00,
            'description' => "Gute Anbindung an öffentliche Verkehrsmittel\nEinkaufsmöglichkeiten und Schulen in der Nähe\nRuhige Wohngegend\nGepflegtes Umfeld"
        ],
        4 => [
            'name' => 'Sehr gute Lage',
            'multiplier' => 1.10,
            'description' => "Sehr gute Verkehrsanbindung (ÖPNV und Straße)\nUmfangreiche Infrastruktur (Ärzte, Restaurants, Kultur)\nGrünflächen und Parks in der Nähe\nGehobene Wohngegend"
        ],
        5 => [
            'name' => 'Premium-Lage',
            'multiplier' => 1.25,
            'description' => "Beste Verkehrsanbindung\nExklusive Nachbarschaft\nTop-Infrastruktur und Freizeitmöglichkeiten\nBesondere Lagevorteile (Seenähe, Altstadt, Villenviertel)"
        ],
    ],
]
```

### Neue Felder in Formular-Daten (Frontend)

```javascript
{
    address: "Musterstraße 123, 80331 München",
    address_lat: 48.1351,
    address_lng: 11.5820,
    location_rating: 3,  // 1-5
}
```

### Neue Felder in API-Response

```php
[
    'factors' => [
        // ... bestehende Faktoren ...
        'location_rating' => 3,
        'location_name' => 'Gute Lage',
        'location_impact' => 1.00,
    ],
]
```

---

## Berechnungsintegration

### Aktualisierte Formel

```
Mietpreis/m² = Basis-Mietpreis (Stadt)
             × Größendegression-Faktor
             × Lage-Multiplikator          ← NEU
             × Zustands-Multiplikator
             × Objekttyp-Multiplikator
             + Ausstattungs-Zuschläge
             × Alters-Anpassung
```

### Beispielrechnung

| Faktor | Wert | Ergebnis |
|--------|------|----------|
| Basis-Preis (München) | 15,00 €/m² | 15,00 € |
| Größendegression (100m²) | ×0,93 | 13,95 € |
| **Lage (Sehr gut)** | **×1,10** | **15,35 €** |
| Zustand (Renoviert) | ×1,10 | 16,88 € |
| Objekttyp (Wohnung) | ×1,00 | 16,88 € |
| Balkon | +0,50 € | 17,38 €/m² |

---

## Technische Implementierung

### Zu ändernde/erstellende Dateien

| Datei | Änderung |
|-------|----------|
| `admin/views/settings.php` | Google Maps API Key Sektion hinzufügen |
| `admin/views/matrix.php` | Neuer Tab "Lage-Faktoren" |
| `admin/class-admin.php` | Sanitize für API Key + Lage-Faktoren + Defaults |
| `admin/js/admin.js` | Beschreibungs-Editor Interaktion |
| `admin/css/admin.css` | Styling für Lage-Faktoren Tab |
| `includes/class-shortcode.php` | API Key + Lage-Daten an Frontend übergeben |
| `includes/class-assets.php` | Google Maps Script laden (conditional) |
| `includes/class-calculator.php` | Lage-Multiplikator in Berechnung integrieren |
| `includes/class-rest-api.php` | Lage-Rating Endpoint erweitern |
| `src/components/steps/LocationStep.js` | **NEU**: Kompletter Lage-Step |
| `src/components/RentalCalculator.js` | LocationStep einbinden |
| `src/components/ComparisonCalculator.js` | LocationStep einbinden |
| `src/styles/main.scss` | Styling für Map, Slider, Beschreibungsbox |
| `README.md` | Dokumentation aktualisieren |

### Google Maps laden

```php
// In class-assets.php - nur laden wenn API Key vorhanden
$settings = get_option('irp_settings', []);
$api_key = $settings['google_maps_api_key'] ?? '';

if (!empty($api_key)) {
    wp_enqueue_script(
        'google-maps',
        'https://maps.googleapis.com/maps/api/js?key=' . esc_attr($api_key) . '&libraries=places',
        [],
        null,
        true
    );
}
```

### Frontend: LocationStep.js (Struktur)

```javascript
function LocationStep({ data, onChange, locationRatings, apiKey }) {
    const [mapLoaded, setMapLoaded] = useState(false);
    const mapRef = useRef(null);

    // Google Places Autocomplete
    // Map initialisierung
    // Slider für Rating
    // Dynamische Beschreibung

    return (
        <div className="irp-location-step">
            <AddressInput />
            {apiKey && <GoogleMap />}
            <RatingSlider
                value={data.location_rating}
                onChange={(val) => onChange({ location_rating: val })}
            />
            <RatingDescription rating={data.location_rating} />
        </div>
    );
}
```

---

## Fallback ohne Google Maps

Wenn kein API-Key konfiguriert ist:

- Adressfeld wird als normales Textfeld angezeigt (ohne Autocomplete)
- Keine Kartenansicht
- Slider und Beschreibung funktionieren weiterhin

---

## Validierung

Der Lage-Step ist valide wenn:
- `location_rating` zwischen 1 und 5 liegt

Die Adresse ist optional (für die Berechnung wird nur das Rating benötigt).

---

## Zukünftige Erweiterungen (nicht in v1)

- Automatische Lage-Bewertung basierend auf PLZ-Daten
- Integration von Mietspiegeldaten
- Heatmap-Overlay für Mietpreise
- POI-Anzeige (Schulen, ÖPNV, Supermärkte)
