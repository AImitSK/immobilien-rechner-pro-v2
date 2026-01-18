# Benutzerhandbuch

Dieses Handbuch richtet sich an Plugin-Administratoren und Makler, die den Immobilien Rechner Pro auf ihrer WordPress-Website einsetzen.

---

## Inhaltsverzeichnis

1. [Shortcode-Verwendung](#shortcode-verwendung)
2. [Admin-Bereich](#admin-bereich)
3. [Lead-Verwaltung](#lead-verwaltung)
4. [E-Mail & PDF](#e-mail--pdf)
5. [Berechnungslogik](#berechnungslogik)

---

## Shortcode-Verwendung

### Basis-Shortcode

```
[immobilien_rechner]
```

Zeigt den kompletten Rechner mit Modus-Auswahl und Städte-Dropdown.

### Verfügbare Parameter

| Parameter | Werte | Standard | Beschreibung |
|-----------|-------|----------|--------------|
| `mode` | `rental`, `comparison`, `sale_value` | (alle) | Modus festlegen oder Benutzer wählen lassen |
| `city_id` | z.B. `muenchen`, `berlin` | (keiner) | Stadt festlegen (überspringt Standort-Auswahl) |
| `city_name` | Text | (aus Matrix) | Anzeigename für die Stadt |
| `theme` | `light`, `dark` | `light` | Farbschema |
| `show_branding` | `true`, `false` | `true` | Firmenbranding anzeigen/ausblenden |

### Modi im Detail

#### Mietwert-Rechner (`mode="rental"`)

Berechnet die potenzielle Monatsmiete basierend auf:
- Immobilientyp (Wohnung, Haus, Gewerbe)
- Größe und Zimmeranzahl
- Baujahr
- Zustand
- Lage (5 Stufen)
- Ausstattungsmerkmale

#### Verkaufen vs. Vermieten (`mode="comparison"`)

Vergleicht beide Optionen mit:
- Break-Even-Analyse
- Renditeberechnung
- Interaktiven Charts
- Empfehlung basierend auf Haltedauer

#### Verkaufswert-Rechner (`mode="sale_value"`)

Ermittelt den Verkaufswert nach professionellen Bewertungsverfahren:

| Immobilientyp | Verfahren |
|---------------|-----------|
| Wohnung | Vergleichswertverfahren (Preis/m² × Faktoren) |
| Haus | Sachwertverfahren (Bodenwert + Gebäudewert) |
| Grundstück | Bodenwertverfahren |

### Shortcode-Beispiele

**Nur Mietwert-Rechner:**
```
[immobilien_rechner mode="rental"]
```

**Nur Verkaufswert für München:**
```
[immobilien_rechner mode="sale_value" city_id="muenchen"]
```

**Vergleichsrechner mit Dark-Theme:**
```
[immobilien_rechner mode="comparison" theme="dark"]
```

**White-Label (ohne Branding):**
```
[immobilien_rechner show_branding="false"]
```

**Alle Modi für Berlin:**
```
[immobilien_rechner city_id="berlin"]
```

---

## Admin-Bereich

Der Admin-Bereich ist unter **WordPress Admin → Immo Rechner** erreichbar.

### Dashboard

**Pfad:** Immo Rechner → Dashboard

- Übersicht über Leads und Berechnungen
- Schnellstart-Anleitung mit Shortcode-Beispielen
- Letzte Leads auf einen Blick
- Statistiken nach Modus

### Shortcode Generator

**Pfad:** Immo Rechner → Shortcode

Visueller Generator für Shortcodes:
- Modus auswählen
- Stadt festlegen
- Theme wählen
- Live-Vorschau des Shortcodes
- Kopieren mit einem Klick

### Matrix & Daten

**Pfad:** Immo Rechner → Matrix & Daten

Zentrale Konfiguration aller Berechnungsparameter:

#### Tab: Städte

| Feld | Beschreibung |
|------|--------------|
| Stadt-ID | Technischer Identifier (z.B. `muenchen`) |
| Name | Anzeigename (z.B. "München") |
| Basis-Mietpreis | €/m² für 70m² Referenzwohnung |
| Größendegression | Prozentsatz für Preisanpassung nach Größe |
| Vervielfältiger | Faktor für Kaufpreis aus Miete |
| Bodenrichtwert | €/m² Grundstück (für Verkaufswert) |
| Gebäudepreis/m² | €/m² Gebäude (für Sachwert) |
| Wohnungspreis/m² | €/m² Wohnung (für Vergleichswert) |
| Marktanpassung | Faktor 0.8-1.4 für regionale Marktlage |

#### Tab: Multiplikatoren

- **Zustand**: Neu (1.25×), Renoviert (1.10×), Gut (1.00×), Sanierungsbedürftig (0.80×)
- **Objekttyp**: Wohnung (1.00×), Haus (1.15×), Gewerbe (0.85×)
- **Baualter**: 7 Kategorien von Altbau bis Neubau
- **Haustyp** (Verkaufswert): EFH, MFH, DHH, Reihenhaus, Bungalow
- **Qualität** (Verkaufswert): Einfach, Normal, Gehoben, Luxuriös

#### Tab: Ausstattung

Zuschläge pro m² für Features:
- Balkon, Terrasse, Garten
- Aufzug, Stellplatz, Garage
- Einbauküche, Fußbodenheizung
- Keller, Gast-WC, Barrierefrei

#### Tab: Lage-Faktoren

5-stufige Bewertung:
| Stufe | Beschreibung | Faktor |
|-------|--------------|--------|
| 1 | Einfach | 0.85× |
| 2 | Normal | 0.95× |
| 3 | Gut | 1.00× |
| 4 | Sehr gut | 1.10× |
| 5 | Premium | 1.25× |

#### Tab: Globale Parameter

- Zinssatz (Standard: 3.0%)
- Wertsteigerung (Standard: 2.0%)
- Mietentwicklung (Standard: 2.0%)

---

## Lead-Verwaltung

**Pfad:** Immo Rechner → Leads

### Lead-Liste

| Spalte | Beschreibung |
|--------|--------------|
| Name | Name des Interessenten |
| E-Mail | Kontakt-E-Mail |
| Modus | rental / comparison / sale_value |
| Stadt | Gewählte Stadt |
| Status | partial (unvollständig) / complete (vollständig) |
| E-Mail | ✓ gesendet / – ausstehend |
| Propstack | Sync-Status |
| Datum | Erstellungsdatum |

### Funktionen

- **Filterung**: Nach Modus, Status, Datum
- **Suche**: Nach Name oder E-Mail
- **Detailansicht**: Klick auf Lead zeigt alle Daten
- **CSV-Export**: Alle Leads exportieren
- **Bulk-Delete**: Mehrere Leads gleichzeitig löschen

### Lead-Status

| Status | Bedeutung |
|--------|-----------|
| `partial` | Berechnung durchgeführt, aber Kontaktformular nicht ausgefüllt |
| `complete` | Vollständiger Lead mit Kontaktdaten |

### Propstack-Sync

Bei aktivierter Propstack-Integration:
- Automatische Synchronisation bei Lead-Vervollständigung
- Makler-Zuweisung nach Stadt
- Retry-Button bei Fehlern
- Status und Fehlermeldung in Detailansicht

---

## E-Mail & PDF

Bei aktivierter E-Mail-Funktion erhält jeder vollständige Lead automatisch eine E-Mail.

### E-Mail-Inhalt

- **Personalisierte Anrede** aus dem Lead-Namen
- **Anpassbarer Text** über den WYSIWYG-Editor
- **Verfügbare Variablen:**
  - `{name}` - Name des Leads
  - `{city}` - Gewählte Stadt
  - `{property_type}` - Immobilientyp
  - `{size}` - Fläche in m²
  - `{result_value}` - Berechnungsergebnis

### PDF-Anhang

Bei aktiviertem PDF-Export enthält die E-Mail einen Anhang mit:
- Zentriertes Firmenlogo (Breite konfigurierbar)
- Berechnungsergebnis
  - Mietwert: Mietspanne und Empfehlung
  - Verkaufswert: Wertspanne und Aufschlüsselung
- Objektdaten im Überblick
- Lage-Bewertung mit Sternen
- Disclaimer-Hinweis
- Footer mit Firmenkontaktdaten

### Technischer Hinweis

Die E-Mail wird asynchron nach der Response gesendet (`register_shutdown_function`), sodass der Nutzer nicht warten muss.

---

## Berechnungslogik

### Mietwert-Berechnung

```
Mietpreis/m² = Basis-Mietpreis (Stadt)
             × Größendegression-Faktor
             × Lage-Multiplikator
             × Zustands-Multiplikator
             × Objekttyp-Multiplikator
             + Ausstattungs-Zuschläge
             × Alters-Anpassung

Monatliche Miete = Fläche × Mietpreis/m²
```

**Größendegression:** Der Basis-Mietpreis bezieht sich auf eine 70 m² Referenzwohnung. Größere Wohnungen werden pro m² günstiger, kleinere teurer.

### Verkaufswert-Berechnung

#### Wohnungen (Vergleichswertverfahren)

```
Verkaufswert = Wohnfläche × Wohnungspreis/m² (Stadt)
             × Qualitäts-Faktor
             × Lage-Faktor
             × Alters-Faktor (mit Modernisierung)
             + Ausstattungs-Werte
             × Marktanpassungsfaktor
```

#### Häuser (Sachwertverfahren)

```
Bodenwert    = Grundstücksfläche × Bodenrichtwert
Gebäudewert  = Wohnfläche × Gebäudepreis/m²
             × Haustyp-Faktor
             × Qualitäts-Faktor
             × Alters-Faktor (mit Modernisierung)
             × Lage-Faktor

Verkaufswert = (Bodenwert + Gebäudewert + Ausstattung) × Marktanpassung
```

#### Grundstücke (Bodenwertverfahren)

```
Verkaufswert = Grundstücksfläche × Bodenrichtwert × Lage-Faktor × Marktanpassung
```

### Fiktives Baujahr (ImmoWertV)

Modernisierungen verschieben das fiktive Baujahr:

| Modernisierung | Verschiebung |
|----------------|--------------|
| Vor 1-3 Jahren | +15 Jahre |
| Vor 4-9 Jahren | +10 Jahre |
| Vor 10-15 Jahren | +5 Jahre |
| Vor 15+ Jahren | keine |
| Nie | keine |

**Beispiel:** Baujahr 1980 + Modernisierung vor 1-3 Jahren = Fiktives Baujahr 1995

### Verkaufen vs. Vermieten

- Brutto- und Nettorendite
- Zinseszins-Kalkulation für Verkaufserlös
- Mietentwicklung über Haltedauer
- Wertsteigerung der Immobilie
- Break-Even-Punkt

---

## Vorkonfigurierte Städte

Das Plugin enthält 10 deutsche Großstädte mit realistischen Standardwerten:

| Stadt | Basis-Miete | Vervielfältiger |
|-------|-------------|-----------------|
| Leipzig/Dresden | 10,50 €/m² | 21× |
| Berlin | 18,50 €/m² | 30× |
| Hamburg | 16,00 €/m² | 28× |
| Hannover | 11,50 €/m² | 22× |
| Düsseldorf | 11,00 €/m² | 23× |
| Köln/Bonn | 11,50 €/m² | 24× |
| Frankfurt | 13,50 €/m² | 27× |
| Stuttgart | 13,00 €/m² | 26× |
| München | 19,00 €/m² | 35× |
| Nürnberg | 10,00 €/m² | 20× |

Alle Werte können im Admin unter **Matrix & Daten** angepasst werden.
