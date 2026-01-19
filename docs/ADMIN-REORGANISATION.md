# Admin-Backend Reorganisation - Planungsdokument

## Problemstellung

Nach Integration des Verkaufswertrechners ist das WordPress-Backend unübersichtlich:
- Zu viele Eingabefelder an verschiedenen Stellen verstreut
- Berechnungsparameter unter "Settings" statt bei den Berechnungen
- Städte-Tabelle hat zu viele Spalten (10 Felder pro Zeile)
- Keine Hilfe-Tooltips für komplexe Felder
- Standard Number-Inputs mit unbrauchbaren Spinner-Buttons

---

## Entscheidung: Option C - Komplette Neustrukturierung

### Zusätzliche UI-Verbesserungen

1. **Hilfe-Tooltips [?]** an allen Berechnungsfeldern
2. **Keine Number-Spinner** - Text-Inputs mit Pattern oder Range-Slider
3. **Städte als Toggle/Accordion** statt Tabelle mit 10 Spalten
4. **Bessere Gruppierung** der Felder nach Rechnertyp

---

## Verfügbare UI-Komponenten

WordPress Admin bringt bereits mit:
- **Dashicons** - Icon-Font (z.B. `dashicons-editor-help` für [?])
- **jQuery** - für Toggle/Accordion
- **Eigene CSS-Klassen** - bereits vorhanden:
  - `.irp-range-slider` - schöner Slider mit Wert-Anzeige
  - `.irp-info-box` - Info-Boxen
  - `.irp-settings-section` - Gruppierte Bereiche

Kein externes Framework (Bootstrap etc.) nötig!

---

## Neue Tab-Struktur für "Matrix & Daten"

### VORHER (6 Tabs):
```
Städte | Lage-Faktoren | Multiplikatoren | Ausstattung | Verkaufswert | Globale Parameter
```

### NACHHER (5 Tabs):
```
Städte | Lage-Faktoren | Mietwert | Verkaufswert | Globale Parameter
```

**Änderungen:**
- "Multiplikatoren" → "Mietwert" (klarer Name)
- "Ausstattung" → in "Mietwert" integriert
- "Globale Parameter" erweitert um 3 Felder aus Settings

---

## Tab 1: Städte (Accordion-Ansicht)

### Neue UI: Aufklappbare Stadt-Karten

```
┌─────────────────────────────────────────────────────────────┐
│ ▶ München (muenchen)                              [Löschen] │
└─────────────────────────────────────────────────────────────┘
┌─────────────────────────────────────────────────────────────┐
│ ▼ Berlin (berlin)                                 [Löschen] │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌─ Basisdaten ──────────────────────────────────────────┐ │
│  │  Stadt-ID: [berlin    ]  Name: [Berlin           ]    │ │
│  └───────────────────────────────────────────────────────┘ │
│                                                             │
│  ┌─ Mietwert-Parameter ──────────────────────────────────┐ │
│  │  Basis-Mietpreis [?]     ════════════○═══ 14.50 €/m² │ │
│  │  Größendegression [?]    ════════○═══════    0.20    │ │
│  │  Vervielfältiger [?]     ════════════════○══   28    │ │
│  └───────────────────────────────────────────────────────┘ │
│                                                             │
│  ┌─ Verkaufswert-Parameter ──────────────────────────────┐ │
│  │  Bodenrichtwert [?]      [  450 ] €/m²                │ │
│  │  Gebäude [?]             [ 2800 ] €/m²                │ │
│  │  Wohnung [?]             [ 4500 ] €/m²                │ │
│  │  Marktfaktor [?]         ════════════○═══    1.15    │ │
│  └───────────────────────────────────────────────────────┘ │
│                                                             │
└─────────────────────────────────────────────────────────────┘

                    [+ Stadt hinzufügen]
```

### Vorteile:
- Nur relevante Stadt sichtbar (weniger Überforderung)
- Logische Gruppierung (Mietwert vs. Verkaufswert)
- Slider für relative Werte (Degression, Marktfaktor)
- Text-Inputs für absolute Werte (€/m²)

---

## Tab 2: Lage-Faktoren (bleibt ähnlich)

Kleine Verbesserung: [?] Tooltips an Multiplikator-Feldern

---

## Tab 3: Mietwert (zusammengeführt)

### Enthält jetzt:
1. Zustands-Multiplikatoren (vorher: Multiplikatoren)
2. Objekttyp-Multiplikatoren (vorher: Multiplikatoren)
3. Baualters-Multiplikatoren (vorher: Multiplikatoren)
4. Ausstattungs-Zuschläge €/m² (vorher: separater Tab)

### UI-Verbesserungen:
- Slider für Multiplikatoren (0.5 - 2.0)
- Text-Inputs für €/m² Zuschläge
- [?] Tooltips mit Erklärung

---

## Tab 4: Verkaufswert (bleibt, mit UI-Verbesserungen)

### Enthält:
1. Haustyp-Multiplikatoren
2. Qualitäts-Multiplikatoren
3. Modernisierung → Fiktives Baujahr
4. Altersabschlag
5. Ausstattungs-Zuschläge (absolute €)

### UI-Verbesserungen:
- Slider für Multiplikatoren
- [?] Tooltips mit ImmoWertV-Erklärungen

---

## Tab 5: Globale Parameter (erweitert)

### Felder verschieben von Settings → hierher:
- Instandhaltungsrate (%)
- Leerstandsrate (%)
- Maklerprovision (%)

### Bereits vorhanden:
- Kapitalanlage-Zinssatz (%)
- Wertsteigerung Immobilie (%)
- Jährliche Mietsteigerung (%)

### UI: Alle als Slider mit Prozent-Anzeige

---

## Settings-Seite bereinigen

### Tab "Allgemein" - Entfernen:
- ~~Instandhaltungsrate~~ → Matrix/Globale Parameter
- ~~Leerstandsrate~~ → Matrix/Globale Parameter
- ~~Maklerprovision~~ → Matrix/Globale Parameter

### Tab "Allgemein" - Behalten:
- Maximale Breite (Slider)
- Primärfarbe
- Sekundärfarbe
- Datenschutz-Einstellungen

---

## UI-Komponenten: Tooltip mit [?] Icon

### HTML-Struktur:
```html
<label>
    Basis-Mietpreis
    <span class="irp-tooltip">
        <span class="dashicons dashicons-editor-help"></span>
        <span class="irp-tooltip-text">
            Der Ausgangspreis für eine 70m² Referenzwohnung
            in durchschnittlicher Lage und Zustand.
        </span>
    </span>
</label>
```

### CSS:
```css
.irp-tooltip {
    position: relative;
    display: inline-block;
    cursor: help;
    margin-left: 5px;
}

.irp-tooltip .dashicons {
    color: #2271b1;
    font-size: 16px;
    width: 16px;
    height: 16px;
}

.irp-tooltip-text {
    visibility: hidden;
    opacity: 0;
    position: absolute;
    z-index: 100;
    bottom: 125%;
    left: 50%;
    transform: translateX(-50%);
    width: 250px;
    background: #1d2327;
    color: #fff;
    padding: 10px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: normal;
    line-height: 1.4;
    transition: opacity 0.2s, visibility 0.2s;
}

.irp-tooltip-text::after {
    content: '';
    position: absolute;
    top: 100%;
    left: 50%;
    margin-left: -6px;
    border: 6px solid transparent;
    border-top-color: #1d2327;
}

.irp-tooltip:hover .irp-tooltip-text,
.irp-tooltip:focus .irp-tooltip-text {
    visibility: visible;
    opacity: 1;
}
```

---

## UI-Komponenten: Text-Input ohne Spinner

### Statt:
```html
<input type="number" step="0.01" min="0" max="100">
```

### Besser:
```html
<input type="text"
       inputmode="decimal"
       pattern="[0-9]*[.,]?[0-9]*"
       class="irp-number-input">
```

### CSS:
```css
.irp-number-input {
    width: 80px;
    text-align: right;
    font-variant-numeric: tabular-nums;
}
```

---

## UI-Komponenten: Stadt-Accordion

### HTML-Struktur:
```html
<div class="irp-city-accordion">
    <div class="irp-city-item" data-city-index="0">
        <div class="irp-city-header">
            <span class="irp-city-toggle dashicons dashicons-arrow-right-alt2"></span>
            <span class="irp-city-title">München</span>
            <code class="irp-city-id">muenchen</code>
            <button type="button" class="irp-city-delete">
                <span class="dashicons dashicons-trash"></span>
            </button>
        </div>
        <div class="irp-city-content">
            <!-- Gruppierte Felder hier -->
        </div>
    </div>
</div>
```

### CSS:
```css
.irp-city-item {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    margin-bottom: 10px;
}

.irp-city-header {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    cursor: pointer;
    gap: 10px;
}

.irp-city-header:hover {
    background: #f6f7f7;
}

.irp-city-toggle {
    transition: transform 0.2s;
}

.irp-city-item.open .irp-city-toggle {
    transform: rotate(90deg);
}

.irp-city-title {
    font-weight: 600;
    flex: 1;
}

.irp-city-id {
    background: #f0f6fc;
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 12px;
}

.irp-city-content {
    display: none;
    padding: 20px;
    border-top: 1px solid #c3c4c7;
}

.irp-city-item.open .irp-city-content {
    display: block;
}

.irp-city-group {
    background: #f6f7f7;
    border-radius: 4px;
    padding: 15px;
    margin-bottom: 15px;
}

.irp-city-group h4 {
    margin: 0 0 15px;
    font-size: 13px;
    color: #1d2327;
}
```

### JavaScript:
```javascript
jQuery(document).ready(function($) {
    // Toggle city accordion
    $('.irp-city-header').on('click', function(e) {
        if ($(e.target).closest('.irp-city-delete').length) return;
        $(this).closest('.irp-city-item').toggleClass('open');
    });

    // Open first city by default
    $('.irp-city-item:first').addClass('open');
});
```

---

## Tooltip-Texte für alle Felder

### Städte - Mietwert:
| Feld | Tooltip |
|------|---------|
| Basis-Mietpreis | Ausgangspreis für eine 70m² Referenzwohnung in durchschnittlicher Lage und normalem Zustand. |
| Größendegression | Steuert wie stark der m²-Preis bei größeren Wohnungen sinkt. 0.20 = Standard, 0 = keine Anpassung. |
| Vervielfältiger | Anzahl Jahresnettokaltmieten für den Kaufpreis. Bei 1.000€/Monat und Faktor 25 → 300.000€ Kaufpreis. |

### Städte - Verkaufswert:
| Feld | Tooltip |
|------|---------|
| Bodenrichtwert | Durchschnittlicher Grundstückspreis pro m² in dieser Stadt (vom Gutachterausschuss). |
| Gebäude €/m² | Normalherstellungskosten für Wohngebäude - Neubaukosten pro m² Wohnfläche. |
| Wohnung €/m² | Durchschnittlicher Verkaufspreis pro m² für Eigentumswohnungen in dieser Stadt. |
| Marktfaktor | Marktanpassung: 0.8 = schwacher Markt, 1.0 = normal, 1.2+ = Boom-Markt. |

### Globale Parameter:
| Feld | Tooltip |
|------|---------|
| Instandhaltungsrate | Jährliche Rücklagen für Reparaturen und Instandhaltung (% vom Immobilienwert). |
| Leerstandsrate | Erwarteter Mietausfall durch Leerstand oder Mieterwechsel pro Jahr. |
| Maklerprovision | Übliche Maklergebühr bei Immobilienverkauf in Ihrer Region. |
| Wertsteigerung | Angenommene jährliche Wertsteigerung für die 30-Jahres-Prognose. |
| Mietsteigerung | Angenommene jährliche Mieterhöhung für die Prognose. |
| Zinssatz | Vergleichszinssatz für alternative Kapitalanlage des Verkaufserlöses. |

---

## Implementierungsreihenfolge

### Phase 1: CSS & Komponenten (1-2h)
- [ ] Tooltip-CSS hinzufügen
- [ ] Text-Input-Styling (ohne Spinner)
- [ ] Accordion-CSS für Städte
- [ ] Range-Slider für Multiplikatoren anpassen

### Phase 2: Städte-Tab umbauen (2-3h)
- [ ] Tabelle → Accordion umbauen
- [ ] Felder in Gruppen organisieren
- [ ] Tooltips hinzufügen
- [ ] JavaScript für Toggle

### Phase 3: Tabs zusammenführen (1-2h)
- [ ] "Multiplikatoren" → "Mietwert" umbenennen
- [ ] "Ausstattung" in "Mietwert" integrieren
- [ ] Tabs-Navigation anpassen

### Phase 4: Settings bereinigen (1h)
- [ ] 3 Felder aus Settings entfernen
- [ ] In "Globale Parameter" einfügen
- [ ] Settings-Bereich "Rechner-Standardwerte" entfernen

### Phase 5: Tooltips überall (1-2h)
- [ ] Lage-Faktoren Tab
- [ ] Mietwert Tab
- [ ] Verkaufswert Tab
- [ ] Globale Parameter Tab

---

## Mehrsprachigkeit (i18n)

Alle neuen Texte werden übersetzbar gemacht:
- Labels: `esc_html_e('Text', 'immobilien-rechner-pro')`
- Attribute: `esc_attr__('Text', 'immobilien-rechner-pro')`
- Variablen: `__('Text', 'immobilien-rechner-pro')`

Nach Abschluss aller Phasen:
- [ ] POT-Datei aktualisieren: `wp i18n make-pot . languages/immobilien-rechner-pro.pot`
- [ ] Deutsche PO-Datei aktualisieren

---

## Dateien die geändert werden

1. `admin/css/admin.css` - Neue CSS-Komponenten
2. `admin/js/admin.js` - Accordion-Toggle, Input-Validierung
3. `admin/views/matrix.php` - Kompletter Umbau
4. `admin/views/settings.php` - Felder entfernen
5. `includes/class-admin.php` - Falls Settings-Registrierung angepasst werden muss
6. `languages/immobilien-rechner-pro.pot` - Neue Strings

---

## Nächster Schritt

Sollen wir mit Phase 1 (CSS & Komponenten) beginnen?
