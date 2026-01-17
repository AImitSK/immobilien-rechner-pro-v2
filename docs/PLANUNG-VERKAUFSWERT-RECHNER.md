# Planung: Verkaufswert-Rechner (mode="sale_value")

## Übersicht

Erweiterung des Immobilien-Rechner-Pro um einen neuen Modus zur Berechnung des Immobilien-Verkaufswertes.

**Neuer Shortcode:**
```
[immobilien_rechner mode="sale_value" city="bad_oeynhausen"]
```

**Bestehende Modi bleiben unverändert:**
- `mode="rental"` - Mietwertberechnung
- `mode="comparison"` - Verkaufen vs. Vermieten

---

## 1. Frontend: Steps (React) - OPTIMIERT auf 8 Steps

### Step-Reihenfolge (Conversion-optimiert)

| # | Step | Komponente | Beschreibung |
|---|------|------------|--------------|
| 1 | Immobilientyp | `SalePropertyTypeStep.js` | Grundstück / Wohnung / Haus + Haustyp (bei Haus) |
| 2 | Größe & Baujahr | `SaleSizeStep.js` | Grundstück + Wohnfläche + Baujahr + Modernisierung |
| 3 | Ausstattung | `SaleFeaturesStep.js` | Außen + Innen kombiniert auf einer Seite |
| 4 | Qualität & Lage | `SaleQualityLocationStep.js` | Qualität + Lagefaktor (bestehend wiederverwenden) |
| 5 | Adresse | `AddressStep.js` | PLZ, Stadt, Straße mit Google Maps Autocomplete |
| 6 | Nutzung & Ziel | `SalePurposeStep.js` | Nutzung + Verkauf/Kauf + Zeitrahmen kombiniert |
| 7 | Kontakt | `ContactStep.js` | **Besteht bereits** - wiederverwenden |
| 8 | Ergebnis | `SaleResultsDisplay.js` | Verkaufspreis-Anzeige |

**Vorteil:** Nur 6 Klicks bis zum Kontaktformular (statt 11), bessere Conversion-Rate.

### Bedingte Logik

```
Immobilientyp = "Grundstück" → Überspringe: Wohnfläche, Baujahr, Innenausstattung, Qualität
Immobilientyp = "Wohnung"    → Vergleichswertverfahren (keine Grundstücksgröße, kein Haustyp)
Immobilientyp = "Haus"       → Sachwertverfahren mit allen Feldern
```

---

## 2. Berechnungsverfahren (WICHTIG)

### Zwei unterschiedliche Verfahren je nach Immobilientyp

#### A) Wohnungen: Vergleichswertverfahren (einfach)

```php
// Wohnungen: Reiner Preis pro m² Wohnfläche
$price = $living_space * $city_data['apartment_price_per_sqm']
       * $quality_factor
       * $modernization_factor
       * $location_factor
       * $market_adjustment_factor
       + $features_value;
```

**Begründung:** Bei Wohnungen ist der Bodenwertanteil bereits im m²-Preis enthalten (über Miteigentumsanteil). Eine getrennte Boden-/Gebäudeberechnung ist methodisch falsch.

#### B) Häuser: Sachwertverfahren (erweitert)

```php
// Häuser: Bodenwert + Gebäudewert + Ausstattung + Marktanpassung
$land_value = $land_size * $city_data['land_price_per_sqm'];

$building_value = $living_space * $city_data['building_price_per_sqm']
                * $house_type_factor
                * $quality_factor
                * $effective_age_factor  // NEU: Berücksichtigt Modernisierung
                * $location_factor;

$total_before_market = $land_value + $building_value + $features_value;

// Marktanpassungsfaktor anwenden
$price = $total_before_market * $city_data['market_adjustment_factor'];
```

#### C) Grundstücke: Nur Bodenwert

```php
// Grundstücke: Reiner Bodenwert
$price = $land_size * $city_data['land_price_per_sqm']
       * $location_factor
       * $market_adjustment_factor;
```

---

## 3. Alter + Modernisierung = Fiktives Baujahr (ImmoWertV-konform)

### Problem mit einfacher Multiplikation

```php
// FALSCH: Doppelte Bestrafung/Belohnung
$age_factor = 0.60;           // 40 Jahre alt = 40% Abzug
$modernization_factor = 1.10; // Kürzlich modernisiert = 10% Zuschlag
$result = 0.60 * 1.10 = 0.66; // Ergibt keinen Sinn bei Kernsanierung
```

### Lösung: Fiktives Baujahr berechnen

```php
/**
 * Berechnet das effektive Baujahr unter Berücksichtigung von Modernisierungen
 * Angelehnt an ImmoWertV / Sachwertrichtlinie
 */
private function calculate_effective_build_year(int $original_year, string $modernization): int {
    $current_year = (int) date('Y');

    // Modernisierung verschiebt das fiktive Baujahr nach vorne
    $year_shift = match($modernization) {
        '1-3_years' => 15,      // Kernsanierung: 15 Jahre jünger
        '4-9_years' => 10,      // Große Modernisierung: 10 Jahre jünger
        '10-15_years' => 5,     // Mittlere Modernisierung: 5 Jahre jünger
        'over_15_years' => 0,   // Alte Modernisierung: kein Effekt
        'never' => 0,           // Nie modernisiert
        default => 0,
    };

    $effective_year = $original_year + $year_shift;

    // Nicht neuer als aktuelles Jahr
    return min($effective_year, $current_year);
}

private function calculate_age_factor(int $original_year, string $modernization, array $settings): float {
    $effective_year = $this->calculate_effective_build_year($original_year, $modernization);
    $effective_age = $settings['base_year'] - $effective_year;

    // 1% Abschlag pro Jahr, max 40%
    $depreciation = min(
        $effective_age * $settings['rate_per_year'],
        $settings['max_depreciation']
    );

    return max(0.60, 1 - $depreciation);
}
```

### Beispiele

| Baujahr | Modernisierung | Fiktives Baujahr | Effektives Alter | Faktor |
|---------|----------------|------------------|------------------|--------|
| 1980 | Nie | 1980 | 45 Jahre | 0.60 (max) |
| 1980 | Vor 1-3 Jahren | 1995 | 30 Jahre | 0.70 |
| 1980 | Vor 4-9 Jahren | 1990 | 35 Jahre | 0.65 |
| 2010 | Nie | 2010 | 15 Jahre | 0.85 |
| 2020 | Nie | 2020 | 5 Jahre | 0.95 |

---

## 4. Marktanpassungsfaktor (Regional)

### Problem

Der reine Sachwert (Boden + Gebäude) entspricht selten dem tatsächlichen Marktwert:
- In Boom-Städten (München, Berlin): Marktpreis >> Sachwert
- In ländlichen Regionen: Marktpreis << Sachwert

### Lösung: Neues Feld in der Städte-Matrix

```php
'cities' => [
    [
        'id' => 'bad_oeynhausen',
        'name' => 'Bad Oeynhausen',

        // Bestehende Felder
        'base_price' => 8.50,
        'size_degression' => 0.20,
        'sale_factor' => 25,

        // NEU für Verkaufswert
        'land_price_per_sqm' => 180,
        'building_price_per_sqm' => 2800,
        'apartment_price_per_sqm' => 2500,    // NEU: Für Vergleichswert Wohnungen
        'market_adjustment_factor' => 1.05,   // NEU: Marktanpassung (0.8 - 1.4)
    ],
    [
        'id' => 'munich',
        'name' => 'München',
        'land_price_per_sqm' => 1800,
        'building_price_per_sqm' => 3500,
        'apartment_price_per_sqm' => 8500,
        'market_adjustment_factor' => 1.35,   // Boom-Stadt: +35%
    ],
    [
        'id' => 'rural_example',
        'name' => 'Ländliche Region',
        'land_price_per_sqm' => 50,
        'building_price_per_sqm' => 2200,
        'apartment_price_per_sqm' => 1800,
        'market_adjustment_factor' => 0.85,   // Schwacher Markt: -15%
    ],
]
```

### Admin-UI für Marktanpassung

```
Marktanpassungsfaktor: [====|====] 1.05
                       0.8       1.4

Hinweis: Passt den berechneten Sachwert an die lokale Marktlage an.
- Unter 1.0: Käufermarkt / ländliche Region
- Über 1.0: Verkäufermarkt / Boom-Region
```

---

## 5. Ergebnisausgabe (SaleResultsDisplay.js)

### Anzeige je nach Immobilientyp

#### Für Häuser (Sachwertverfahren)

```
┌─────────────────────────────────────────────────────────┐
│  Geschätzter Verkaufswert Ihrer Immobilie               │
│                                                         │
│  ████████████████████████████████████████               │
│           385.000 € - 425.000 €                         │
│  ████████████████████████████████████████               │
│                                                         │
│  Mittelwert: 405.000 €                                  │
│                                                         │
│  ─────────────────────────────────────────              │
│                                                         │
│  Wertermittlung:                                        │
│  • Grundstückswert: 90.000 €                            │
│  • Gebäudewert: 295.000 €                               │
│  • Ausstattung: +17.000 €                               │
│  • Marktanpassung: ×1.05                                │
│                                                         │
│  ─────────────────────────────────────────              │
│                                                         │
│  Kennzahlen:                                            │
│  • Preis pro m² Wohnfläche: 3.375 €                     │
│  • Preis pro m² Grundstück: 180 €                       │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

#### Für Wohnungen (Vergleichswertverfahren)

```
┌─────────────────────────────────────────────────────────┐
│  Geschätzter Verkaufswert Ihrer Wohnung                 │
│                                                         │
│  ████████████████████████████████████████               │
│           195.000 € - 215.000 €                         │
│  ████████████████████████████████████████               │
│                                                         │
│  Mittelwert: 205.000 €                                  │
│                                                         │
│  ─────────────────────────────────────────              │
│                                                         │
│  Basierend auf:                                         │
│  • 85 m² × 2.400 €/m² Basispreis                        │
│  • Qualität: Gehoben (+15%)                             │
│  • Lage: Sehr gut (+10%)                                │
│  • Ausstattung: +8.000 €                                │
│                                                         │
│  ─────────────────────────────────────────              │
│                                                         │
│  Vergleich Region:                                      │
│  • Ø Preis/m² in Bad Oeynhausen: 2.500 €                │
│  • Ihre Wohnung: 2.412 €/m²                             │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

---

## 6. Backend: Matrix & Daten

### Erweiterung der Stadt-Konfiguration

```php
'cities' => [
    [
        'id' => 'bad_oeynhausen',
        'name' => 'Bad Oeynhausen',

        // Bestehend (Mietwert)
        'base_price' => 8.50,
        'size_degression' => 0.20,
        'sale_factor' => 25,

        // NEU (Verkaufswert)
        'land_price_per_sqm' => 180,          // Bodenrichtwert
        'building_price_per_sqm' => 2800,     // Normalherstellungskosten Haus
        'apartment_price_per_sqm' => 2500,    // Vergleichswert Wohnung
        'market_adjustment_factor' => 1.05,   // Marktanpassung
    ],
]
```

### Neue Faktoren-Tabellen (Tab: "Verkaufswert-Faktoren")

```php
'sale_value_settings' => [

    // Haustyp-Faktoren (nur bei Häusern)
    'house_type_multipliers' => [
        'single_family' => ['name' => 'Einfamilienhaus', 'multiplier' => 1.00],
        'multi_family' => ['name' => 'Mehrfamilienhaus', 'multiplier' => 1.15],
        'semi_detached' => ['name' => 'Doppelhaushälfte', 'multiplier' => 0.95],
        'townhouse_middle' => ['name' => 'Mittelreihenhaus', 'multiplier' => 0.88],
        'townhouse_end' => ['name' => 'Endreihenhaus', 'multiplier' => 0.92],
        'bungalow' => ['name' => 'Bungalow', 'multiplier' => 1.05],
    ],

    // Qualitäts-Faktoren
    'quality_multipliers' => [
        'simple' => ['name' => 'Einfach', 'multiplier' => 0.85],
        'normal' => ['name' => 'Normal', 'multiplier' => 1.00],
        'upscale' => ['name' => 'Gehoben', 'multiplier' => 1.15],
        'luxury' => ['name' => 'Luxuriös', 'multiplier' => 1.35],
    ],

    // Modernisierungs-Verschiebung (Jahre)
    'modernization_year_shift' => [
        '1-3_years' => ['name' => 'Vor 1-3 Jahren', 'years' => 15],
        '4-9_years' => ['name' => 'Vor 4-9 Jahren', 'years' => 10],
        '10-15_years' => ['name' => 'Vor 10-15 Jahren', 'years' => 5],
        'over_15_years' => ['name' => 'Vor mehr als 15 Jahren', 'years' => 0],
        'never' => ['name' => 'Noch nie', 'years' => 0],
    ],

    // Altersabschlag
    'age_depreciation' => [
        'rate_per_year' => 0.01,
        'max_depreciation' => 0.40,
        'base_year' => 2025,
    ],

    // Ausstattungs-Zuschläge (absolute Werte in €)
    'features' => [
        // Außen
        'balcony' => ['name' => 'Balkon', 'value' => 5000, 'type' => 'exterior'],
        'garage' => ['name' => 'Garage', 'value' => 15000, 'type' => 'exterior'],
        'parking' => ['name' => 'Stellplatz', 'value' => 8000, 'type' => 'exterior'],
        'garden' => ['name' => 'Garten', 'value' => 8000, 'type' => 'exterior'],
        'terrace' => ['name' => 'Terrasse', 'value' => 6000, 'type' => 'exterior'],
        'solar' => ['name' => 'Solaranlage', 'value' => 12000, 'type' => 'exterior'],

        // Innen
        'elevator' => ['name' => 'Aufzug', 'value' => 20000, 'type' => 'interior'],
        'fitted_kitchen' => ['name' => 'Einbauküche', 'value' => 8000, 'type' => 'interior'],
        'fireplace' => ['name' => 'Kamin', 'value' => 6000, 'type' => 'interior'],
        'parquet' => ['name' => 'Parkettboden', 'value' => 4000, 'type' => 'interior'],
        'cellar' => ['name' => 'Keller', 'value' => 10000, 'type' => 'interior'],
        'attic' => ['name' => 'Dachboden', 'value' => 5000, 'type' => 'interior'],
    ],
]
```

---

## 7. Berechnungslogik: class-sale-calculator.php

```php
class IRP_Sale_Calculator {

    public function calculate(array $data): array {
        $city_data = $this->get_city_data($data['city_id']);
        $settings = $this->get_sale_settings();
        $property_type = $data['property_type'];

        // Unterschiedliche Verfahren je nach Immobilientyp
        return match($property_type) {
            'apartment' => $this->calculate_apartment($data, $city_data, $settings),
            'house' => $this->calculate_house($data, $city_data, $settings),
            'land' => $this->calculate_land($data, $city_data, $settings),
            default => $this->calculate_house($data, $city_data, $settings),
        };
    }

    /**
     * Wohnungen: Vergleichswertverfahren
     */
    private function calculate_apartment(array $data, array $city, array $settings): array {
        $living_space = (float) $data['living_space'];

        // Basis: Preis pro m² für Wohnungen in dieser Stadt
        $base_value = $living_space * $city['apartment_price_per_sqm'];

        // Faktoren
        $quality_factor = $settings['quality_multipliers'][$data['quality']]['multiplier'] ?? 1.0;
        $location_factor = $this->get_location_factor($data['location_rating']);
        $age_factor = $this->calculate_effective_age_factor($data['build_year'], $data['modernization'], $settings);
        $market_factor = $city['market_adjustment_factor'] ?? 1.0;

        // Ausstattung
        $features_value = $this->calculate_features_value($data['features'] ?? [], $settings);

        // Gesamtberechnung
        $adjusted_value = $base_value * $quality_factor * $location_factor * $age_factor;
        $total = ($adjusted_value + $features_value) * $market_factor;

        return $this->format_result($total, $living_space, null, $features_value, [
            'quality' => $quality_factor,
            'location' => $location_factor,
            'age' => $age_factor,
            'market' => $market_factor,
        ], 'apartment');
    }

    /**
     * Häuser: Sachwertverfahren
     */
    private function calculate_house(array $data, array $city, array $settings): array {
        $living_space = (float) $data['living_space'];
        $land_size = (float) $data['land_size'];

        // Bodenwert
        $land_value = $land_size * $city['land_price_per_sqm'];

        // Gebäudewert (Basis)
        $building_base = $living_space * $city['building_price_per_sqm'];

        // Faktoren
        $house_type_factor = $settings['house_type_multipliers'][$data['house_type']]['multiplier'] ?? 1.0;
        $quality_factor = $settings['quality_multipliers'][$data['quality']]['multiplier'] ?? 1.0;
        $location_factor = $this->get_location_factor($data['location_rating']);
        $age_factor = $this->calculate_effective_age_factor($data['build_year'], $data['modernization'], $settings);
        $market_factor = $city['market_adjustment_factor'] ?? 1.0;

        // Gebäudewert mit Faktoren
        $building_value = $building_base * $house_type_factor * $quality_factor * $age_factor * $location_factor;

        // Ausstattung
        $features_value = $this->calculate_features_value($data['features'] ?? [], $settings);

        // Sachwert + Marktanpassung
        $sachwert = $land_value + $building_value + $features_value;
        $total = $sachwert * $market_factor;

        return $this->format_result($total, $living_space, $land_size, $features_value, [
            'house_type' => $house_type_factor,
            'quality' => $quality_factor,
            'location' => $location_factor,
            'age' => $age_factor,
            'market' => $market_factor,
        ], 'house', $land_value, $building_value);
    }

    /**
     * Grundstücke: Nur Bodenwert
     */
    private function calculate_land(array $data, array $city, array $settings): array {
        $land_size = (float) $data['land_size'];

        $land_value = $land_size * $city['land_price_per_sqm'];
        $location_factor = $this->get_location_factor($data['location_rating']);
        $market_factor = $city['market_adjustment_factor'] ?? 1.0;

        $total = $land_value * $location_factor * $market_factor;

        return $this->format_result($total, null, $land_size, 0, [
            'location' => $location_factor,
            'market' => $market_factor,
        ], 'land', $land_value, 0);
    }

    /**
     * Fiktives Baujahr basierend auf Modernisierung (ImmoWertV)
     */
    private function calculate_effective_age_factor(int $build_year, string $modernization, array $settings): float {
        // Modernisierung verschiebt das Baujahr nach vorne
        $year_shift = $settings['modernization_year_shift'][$modernization]['years'] ?? 0;
        $effective_year = min($build_year + $year_shift, (int) date('Y'));

        // Effektives Alter berechnen
        $effective_age = $settings['age_depreciation']['base_year'] - $effective_year;

        // Abschlag berechnen
        $depreciation = min(
            max(0, $effective_age) * $settings['age_depreciation']['rate_per_year'],
            $settings['age_depreciation']['max_depreciation']
        );

        return max(0.60, 1 - $depreciation);
    }

    private function format_result(
        float $total,
        ?float $living_space,
        ?float $land_size,
        float $features_value,
        array $factors,
        string $type,
        float $land_value = 0,
        float $building_value = 0
    ): array {
        $result = [
            'price_estimate' => round($total, -3),
            'price_min' => round($total * 0.95, -3),
            'price_max' => round($total * 1.05, -3),
            'features_value' => round($features_value, -2),
            'factors' => $factors,
            'calculation_type' => $type,
        ];

        if ($living_space) {
            $result['price_per_sqm_living'] = round($total / $living_space, 0);
        }
        if ($land_size) {
            $result['price_per_sqm_land'] = round($land_value / $land_size, 0);
            $result['land_value'] = round($land_value, -2);
        }
        if ($building_value > 0) {
            $result['building_value'] = round($building_value, -2);
        }

        return $result;
    }
}
```

---

## 8. PDF-Anpassung

### Template: `pdf-sale-value.php`

Das PDF zeigt je nach Berechnungstyp unterschiedliche Details:
- **Wohnung:** Vergleichswert-Darstellung
- **Haus:** Sachwert-Aufschlüsselung (Boden + Gebäude)
- **Grundstück:** Nur Bodenwert

---

## 9. Adress-Validierung mit Google Maps

### Bereits implementiert (v1.5.2)

Die Google Maps Autocomplete-Funktion aus dem Mietrechner wird wiederverwendet:

```javascript
// AddressStep.js - Wiederverwendung der bestehenden Logik
<GooglePlacesAutocomplete
    apiKey={settings.google_maps_api_key}
    onSelect={(place) => {
        setAddress({
            street: place.street,
            zip: place.zip,
            city: place.city,
            lat: place.lat,
            lng: place.lng,
        });
    }}
/>
```

**Vorteil:** Seriöse Darstellung + Validierung der Adresse.

---

## 10. Implementierungs-Reihenfolge

### Phase 1: Backend-Grundlagen
1. [ ] Datenbank-Migration erstellen
2. [ ] `class-sale-calculator.php` mit 3 Verfahren erstellen
3. [ ] Matrix um Verkaufswert-Felder erweitern (inkl. Marktanpassung)
4. [ ] Admin-Tab "Verkaufswert-Faktoren" erstellen

### Phase 2: Frontend-Steps (8 Steps)
5. [ ] `SalePropertyTypeStep.js` erstellen (mit Haustyp integriert)
6. [ ] `SaleSizeStep.js` erstellen (Größe + Baujahr + Modernisierung)
7. [ ] `SaleFeaturesStep.js` erstellen (Außen + Innen kombiniert)
8. [ ] `SaleQualityLocationStep.js` erstellen (Qualität + Lage)
9. [ ] `AddressStep.js` erstellen (Google Maps Autocomplete)
10. [ ] `SalePurposeStep.js` erstellen (Nutzung + Ziel kombiniert)

### Phase 3: Ergebnis & Ausgabe
11. [ ] `SaleResultsDisplay.js` erstellen (3 Varianten)
12. [ ] PDF-Template `pdf-sale-value.php` erstellen
13. [ ] E-Mail-Template für Verkaufswert erstellen

### Phase 4: Integration
14. [ ] Shortcode-Handler für `mode="sale_value"` erweitern
15. [ ] Propstack-Integration anpassen
16. [ ] Lead-Verwaltung für neue Felder erweitern

### Phase 5: Testing & Feinschliff
17. [ ] Alle Steps testen
18. [ ] Berechnung validieren (Vergleich mit echten Werten)
19. [ ] PDF prüfen
20. [ ] Responsive Design testen

---

## 11. Offene Fragen (Geklärt)

| Frage | Entscheidung |
|-------|-------------|
| Google Maps für Adresse? | ✅ Ja, wiederverwenden |
| Vergleichswerte anzeigen? | ✅ Ja, Ø-Preis/m² der Region |
| Preisaufschlüsselung? | ✅ Ja, aber nur bei Häusern (Sachwert) |
| Unterschied Wohnung/Haus? | ✅ Ja, verschiedene Verfahren |
| Schnellrechner? | ❌ Später, erst Vollversion |

---

## 12. Dateistruktur (Neu)

```
src/
├── components/
│   ├── App.js                          (erweitern)
│   ├── steps/
│   │   ├── SalePropertyTypeStep.js     (NEU)
│   │   ├── SaleSizeStep.js             (NEU)
│   │   ├── SaleFeaturesStep.js         (NEU)
│   │   ├── SaleQualityLocationStep.js  (NEU)
│   │   ├── AddressStep.js              (NEU)
│   │   ├── SalePurposeStep.js          (NEU)
│   │   └── ...bestehende Steps
│   └── SaleResultsDisplay.js           (NEU)
│
includes/
├── class-calculator.php                (bestehend)
├── class-sale-calculator.php           (NEU)
├── class-pdf-generator.php             (erweitern)
└── templates/
    ├── pdf.php                         (bestehend)
    └── pdf-sale-value.php              (NEU)

admin/
└── views/
    └── matrix.php                      (erweitern: neuer Tab)
```

---

## 13. Kritische Review-Punkte (Adressiert)

| Kritik | Lösung |
|--------|--------|
| Wohnungen: Falsche Boden-/Gebäude-Trennung | Vergleichswertverfahren implementiert |
| Alter × Modernisierung = ungenaue Werte | Fiktives Baujahr nach ImmoWertV |
| Fehlender Marktanpassungsfaktor | In Städte-Matrix ergänzt |
| 13 Steps = hohe Absprungrate | Auf 8 Steps reduziert |
| Adress-Validierung | Google Maps Autocomplete bestätigt |

---

**Erstellt:** 2026-01-17
**Version:** 2.0 (nach kritischem Review)
**Status:** Bereit zur Implementierung
