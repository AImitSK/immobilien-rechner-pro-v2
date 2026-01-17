# Immobilien Rechner Pro

Professionelles WordPress-Plugin für Mietwertberechnung und Verkaufen-vs-Vermieten-Vergleich. White-Label-Lösung für Immobilienmakler.

## Features

- **Mietwert-Rechner**: Schätzung potenzieller Mieteinnahmen basierend auf Immobiliendaten
- **Verkaufen vs. Vermieten Vergleich**: Visuelle Break-Even-Analyse mit interaktiven Charts
- **Städte-System**: Individuelle Konfiguration pro Stadt mit eigenem Basis-Mietpreis und Vervielfältiger
- **Lage-Bewertung**: 5-stufige Lage-Bewertung mit konfigurierbaren Multiplikatoren und Google Maps Integration
- **Lead-Generierung**: Erfassung und Verwaltung von Leads mit E-Mail-Benachrichtigungen
- **E-Mail mit PDF**: Automatischer Versand einer professionellen Immobilienbewertung als PDF an Leads
- **Propstack CRM Integration**: Automatische Lead-Synchronisation mit Makler-Zuweisung nach Stadt
- **Google Ads Conversion Tracking**: Zwei Conversions (Anfrage gestartet/abgeschlossen) + DataLayer Events für GTM
- **White-Label-Ready**: Vollständig anpassbares Branding (Farben, Logo, Firmeninfo, mehrzeilige Signatur)
- **reCAPTCHA v3**: Spam-Schutz für Lead-Formulare
- **GitHub Auto-Updater**: Automatische Updates direkt von GitHub Releases
- **Responsives Design**: Funktioniert auf allen Geräten
- **DSGVO-konform**: Integrierte Einwilligungsverwaltung

## Anforderungen

- WordPress 6.0+
- PHP 7.4+
- Node.js 18+ (für Entwicklung)

## Installation

### Für Entwicklung

1. Repository in das WordPress-Plugins-Verzeichnis klonen:
   ```bash
   cd wp-content/plugins/
   git clone https://github.com/AImitSK/immobilien-rechner-pro.git
   ```

2. Abhängigkeiten installieren:
   ```bash
   cd immobilien-rechner-pro
   npm install
   ```

3. React-Frontend bauen:
   ```bash
   npm run build
   ```

4. Plugin aktivieren unter WordPress Admin → Plugins

### Für Produktion

1. Release-ZIP von [GitHub Releases](https://github.com/AImitSK/immobilien-rechner-pro/releases) herunterladen
2. Hochladen über WordPress Admin → Plugins → Installieren → Plugin hochladen
3. Plugin aktivieren

### Updates

Das Plugin prüft automatisch auf neue Versionen bei GitHub. Verfügbare Updates erscheinen unter **Dashboard → Aktualisierungen**.

Manuell prüfen: **Immo Rechner → Settings → Nach Updates suchen**

---

## Shortcode-Verwendung

### Basis-Shortcode

```
[immobilien_rechner]
```

Zeigt den kompletten Rechner mit Modus-Auswahl und Städte-Dropdown.

### Shortcode-Parameter

| Parameter | Werte | Beschreibung |
|-----------|-------|--------------|
| `mode` | `""`, `"rental"`, `"comparison"` | Modus festlegen oder Benutzer wählen lassen |
| `city_id` | z.B. `"muenchen"`, `"berlin"` | Stadt festlegen (überspringt Standort-Auswahl) |
| `theme` | `"light"`, `"dark"` | Farbschema |
| `show_branding` | `"true"`, `"false"` | Firmenbranding anzeigen/ausblenden |

### Beispiele

**Nur Mietwert-Rechner:**
```
[immobilien_rechner mode="rental"]
```

**Nur Verkaufen vs. Vermieten Vergleich:**
```
[immobilien_rechner mode="comparison"]
```

**Für eine bestimmte Stadt (z.B. München):**
```
[immobilien_rechner city_id="muenchen"]
```

**Kombination: Mietwert-Rechner für Berlin mit Dark-Theme:**
```
[immobilien_rechner mode="rental" city_id="berlin" theme="dark"]
```

---

## Admin-Bereich

### Dashboard
**WordPress Admin → Immo Rechner → Dashboard**

- Übersicht über Leads und Berechnungen
- Schnellstart-Anleitung mit Shortcode-Beispielen
- Letzte Leads auf einen Blick

### Leads
**WordPress Admin → Immo Rechner → Leads**

- Liste aller erfassten Leads mit Status-Anzeige
- E-Mail-Versand-Status (✓ gesendet / – ausstehend)
- Filterung nach Modus und Status
- Detailansicht mit Berechnungsdaten
- CSV-Export

### Shortcode Generator
**WordPress Admin → Immo Rechner → Shortcode**

Visueller Generator für Shortcodes mit Live-Vorschau.

### Matrix & Daten
**WordPress Admin → Immo Rechner → Matrix & Daten**

Zentrale Konfiguration aller Berechnungsparameter:

- **Städte**: Stadt-ID, Name, Basis-Mietpreis, Degression, Vervielfältiger
- **Multiplikatoren**: Zustand und Objekttyp
- **Ausstattung**: Zuschläge pro m² für Features
- **Lage-Faktoren**: 5-stufige Bewertung mit Multiplikatoren
- **Globale Parameter**: Zinssätze, Wertsteigerung

### Integrationen
**WordPress Admin → Immo Rechner → Integrationen**

Propstack CRM Integration in 3 Tabs:

#### Tab: Verbindung
- API-Key Eingabe und Speicherung
- Verbindungstest-Button
- Status-Anzeige (verbunden/nicht verbunden)

#### Tab: Makler-Zuweisung
- Tabelle aller konfigurierten Städte
- Dropdown zur Makler-Zuweisung pro Stadt
- Makler werden automatisch von Propstack API geladen

#### Tab: Newsletter
- Newsletter-spezifische Einstellungen
- Separater Makler für Newsletter-Leads
- Filter: Nur Leads mit Newsletter-Consent syncen

### Settings
**WordPress Admin → Immo Rechner → Settings**

Einstellungen in 5 Tabs organisiert:

#### Tab: Allgemein
- Darstellung (Breite, Farben)
- Rechner-Standardwerte
- Datenschutz-Einstellungen

#### Tab: Branding & Kontakt
- Logo mit konfigurierbarer Breite
- Firmenname (bis zu 3 Zeilen)
- Adresse und Kontaktdaten
- Wird in E-Mail-Signatur und PDF-Footer verwendet

#### Tab: E-Mail
- E-Mail-Versand aktivieren/deaktivieren
- Absender konfigurieren
- E-Mail-Betreff und Inhalt mit WYSIWYG-Editor
- Verfügbare Variablen: `{name}`, `{city}`, `{property_type}`, `{size}`, `{result_value}`
- Test-E-Mail versenden

#### Tab: reCAPTCHA
- Google reCAPTCHA v3 Keys
- Mindest-Score konfigurieren

#### Tab: Google Maps
- API Key für Karten und Autocomplete
- Karte im Lage-Step anzeigen

#### Tab: Tracking
- Google Ads Conversion-ID (Format: AW-XXXXXXXXX)
- Conversion-Label für "Anfrage gestartet" (Berechnung durchgeführt)
- Conversion-Label für "Anfrage abgeschlossen" (Kontaktformular gesendet)
- DataLayer Events für Google Tag Manager (automatisch)

---

## E-Mail & PDF Feature

Bei aktivierter E-Mail-Funktion erhält jeder Lead automatisch eine professionelle E-Mail mit:

- **Personalisierter Anrede** aus dem Lead-Namen
- **Anpassbarer Text** über den WYSIWYG-Editor
- **PDF-Anhang** mit:
  - Zentriertes Firmenlogo (Breite konfigurierbar)
  - Berechnungsergebnis mit Mietspanne
  - Objektdaten im Überblick
  - Lage-Bewertung mit Sternen
  - Disclaimer-Hinweis
  - Footer mit Firmenkontaktdaten

Die E-Mail wird nach der Response via `register_shutdown_function` gesendet, sodass der Nutzer nicht warten muss.

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

### Verkaufen vs. Vermieten Vergleich

- Brutto- und Nettorendite
- Break-Even-Analyse
- Empfehlung basierend auf Rendite und Haltedauer

---

## REST API Endpoints

| Endpoint | Method | Beschreibung |
|----------|--------|--------------|
| `/irp/v1/calculate/rental` | POST | Mietwert berechnen |
| `/irp/v1/calculate/comparison` | POST | Verkaufen vs. Vermieten berechnen |
| `/irp/v1/leads` | POST | Lead übermitteln (legacy) |
| `/irp/v1/leads/partial` | POST | Partial Lead erstellen |
| `/irp/v1/leads/complete` | POST | Partial Lead vervollständigen |
| `/irp/v1/cities` | GET | Alle konfigurierten Städte abrufen |

---

## Dateistruktur

```
immobilien-rechner-pro/
├── immobilien-rechner-pro.php    # Haupt-Plugin-Datei
├── includes/                      # PHP-Klassen
│   ├── class-activator.php       # Datenbank-Setup
│   ├── class-assets.php          # Script/Style-Loading
│   ├── class-calculator.php      # Berechnungslogik
│   ├── class-email.php           # E-Mail-Versand
│   ├── class-github-updater.php  # Auto-Updates von GitHub
│   ├── class-leads.php           # Lead-Verwaltung
│   ├── class-pdf-generator.php   # PDF-Generierung mit DOMPDF
│   ├── class-propstack.php       # Propstack CRM Integration
│   ├── class-recaptcha.php       # reCAPTCHA v3
│   ├── class-rest-api.php        # REST API Endpoints
│   ├── class-shortcode.php       # Shortcode-Handler
│   └── templates/
│       ├── email.php             # E-Mail HTML Template
│       └── pdf.php               # PDF HTML Template
├── admin/                         # Admin-Panel
│   ├── class-admin.php
│   └── views/
├── vendor/                        # Gebündelte Libraries
│   ├── autoload.php              # Custom Autoloader
│   ├── dompdf/                   # DOMPDF 2.0.4
│   ├── php-font-lib/
│   └── php-svg-lib/
├── src/                           # React-Source (Entwicklung)
│   ├── components/               # React-Komponenten
│   └── utils/
│       ├── debug.js              # Debug-Funktionen
│       └── tracking.js           # Google Ads Tracking
├── build/                         # Kompiliertes React (Produktion)
└── languages/                     # Übersetzungen
```

---

## Datenbank-Tabellen

### wp_irp_leads
| Spalte | Typ | Beschreibung |
|--------|-----|--------------|
| id | bigint | Primary Key |
| name | varchar(255) | Name des Leads |
| email | varchar(255) | E-Mail (Pflicht) |
| phone | varchar(50) | Telefon |
| mode | varchar(20) | 'rental' oder 'comparison' |
| property_type | varchar(50) | Immobilientyp |
| property_size | decimal | Fläche in m² |
| property_location | varchar(255) | Standort/Stadt |
| zip_code | varchar(10) | PLZ |
| calculation_data | longtext | JSON mit Berechnungsergebnissen |
| consent | tinyint | Einwilligung gegeben |
| newsletter_consent | tinyint | Newsletter-Einwilligung |
| status | varchar(20) | 'partial' oder 'complete' |
| recaptcha_score | decimal(3,2) | reCAPTCHA Score |
| ip_address | varchar(45) | IP-Adresse |
| source | varchar(100) | Quelle |
| created_at | datetime | Erstellungsdatum |
| completed_at | datetime | Vervollständigt am |
| email_sent | tinyint | E-Mail versendet |
| email_sent_at | datetime | E-Mail versendet am |
| propstack_id | bigint | Propstack Kontakt-ID |
| propstack_synced | tinyint | Sync erfolgreich |
| propstack_error | text | Fehlermeldung bei Sync |
| propstack_synced_at | datetime | Zeitpunkt des Syncs |

---

## Technologie-Stack

- **Backend**: PHP 7.4+, WordPress REST API
- **Frontend**: React 18, WordPress Element
- **PDF**: DOMPDF 2.0.4 (gebündelt)
- **Charts**: ApexCharts
- **Icons**: Heroicons
- **Animationen**: Framer Motion
- **Styling**: SCSS

---

## Changelog

### Version 1.5.8
- Fix: Telefonnummer wird jetzt auch bei bestehenden Kontakten aktualisiert
- Fix: Automatisches DB-Upgrade bei Plugin-Updates (nicht nur bei Aktivierung)
- Fix: Fehlende Propstack-Spalten werden automatisch hinzugefügt
- Entfernt: Debug-Box aus Lead-Detail-Ansicht

### Version 1.5.7
- Fix: Telefonnummer wird jetzt korrekt an Propstack übertragen (verwendet `home_cell` statt `phone`)
- Fix: Propstack-Status liest jetzt direkt aus DB statt aus gecachtem Lead-Objekt
- Debug: Temporäre Debug-Box in Lead-Detail-Ansicht für Propstack-Werte

### Version 1.5.6
- Fix: Vollständige Kontaktdaten werden jetzt abgerufen (inkl. Beschreibung) bevor Update
- Fix: DB-Update verwendet jetzt direktes SQL für mehr Zuverlässigkeit

### Version 1.5.5
- Neu: Bei existierenden Kontakten wird die neue Anfrage zur Beschreibung hinzugefügt (keine Duplikate)
- Fix: Propstack Sync speichert jetzt propstack_id korrekt in der Datenbank
- Fix: NULL-Wert bei propstack_error durch leeren String ersetzt
- Verbesserung: Besseres Logging bei DB-Update Fehlern

### Version 1.5.4
- Fix: AJAX-Handler für manuellen Propstack-Sync korrigiert (behandelt jetzt WP_Error korrekt)

### Version 1.5.3
- Neu: "Jetzt synchronisieren" Button für ausstehende Leads in der Detail-Ansicht
- Fix: Propstack-Sync kann jetzt manuell ausgelöst werden (nicht nur bei Fehlern)

### Version 1.5.2
- Neu: Google Maps Autocomplete auf gewählte Stadt eingeschränkt (nur Adressen in der konfigurierten Stadt)
- Neu: Eingegebene Adresse wird im PDF angezeigt (Standort zeigt jetzt Stadt + Straße)
- Verbesserung: Placeholder im Adressfeld zeigt "Straße und Hausnummer in [Stadt]..."
- Verbesserung: Karte wird automatisch auf die gewählte Stadt zentriert

### Version 1.5.1
- Fix: Propstack API-Authentifizierung korrigiert (X-API-KEY Header statt Bearer Token)
- Fix: Verbindungstest funktioniert jetzt auch vor dem Speichern des API-Keys

### Version 1.5.0
- Neu: Konfigurierbare Baualters-Multiplikatoren im Admin unter "Matrix & Daten → Multiplikatoren"
- Neu: 7 branchenübliche Baualtersklassen (Altbau bis 1945, Nachkriegsbau, 60er/70er, 80er, 90er, 2000er, Neubau ab 2015)
- Geändert: Zustandsoption "Neubau / Erstbezug" zu "Neubau / Kernsaniert" umbenannt
- Geändert: Währungsbeträge werden jetzt mit 2 Dezimalstellen angezeigt (z.B. 7,20 € statt 7 €)
- Entfernt: Multiplikator-Anzeige bei der Lage-Bewertung im Frontend ausgeblendet

### Version 1.4.2
- Fix: Lead-Löschen-Button in Detail-Ansicht funktioniert jetzt
- Neu: Mehrfachauswahl und Bulk-Delete in Lead-Tabelle
- Objekt-Spalte aus Lead-Tabelle entfernt (Platzersparnis)

### Version 1.4.1
- Fix: Complete Lead Tracking wird jetzt auf der Results-Seite gefeuert
- Behebt Timing-Probleme mit GTM Event-Verarbeitung

### Version 1.4.0
- Google Ads Conversion Tracking
- Neuer Settings-Tab "Tracking"
- Zwei Conversion-Events: Anfrage gestartet / Anfrage abgeschlossen
- DataLayer Events für Google Tag Manager (`irp_partial_lead`, `irp_complete_lead`)

### Version 1.3.0
- Propstack CRM Integration
- Neue Admin-Seite "Integrationen" mit 3 Tabs
- Automatische Lead-Synchronisation bei Vervollständigung
- Makler-Zuweisung nach Stadt
- Propstack-Status in Lead-Liste und Detail-Ansicht
- Retry-Button für fehlgeschlagene Syncs
- 4 neue Datenbank-Spalten für Sync-Tracking

### Version 1.2.0
- E-Mail mit PDF-Anhang an Leads
- DOMPDF für PDF-Generierung gebündelt
- Erweiterte Branding-Einstellungen (mehrzeilige Firma, Adresse, Logo-Breite)
- Settings-Seite in Tabs reorganisiert
- E-Mail-Status in Lead-Liste
- PHP 7.4 Kompatibilität

### Version 1.1.0
- Lead Magnet Flow mit Partial Leads
- reCAPTCHA v3 Integration
- GitHub Auto-Updater
- Debug-Logging

### Version 1.0.0
- Initiales Release

---

## Lizenz

GPL v2 oder später

## Support

Für Support und Feature-Requests bitte ein [Issue auf GitHub](https://github.com/AImitSK/immobilien-rechner-pro/issues) öffnen.

## Autor

**Stefan Kühne**
[sk-online-marketing.de](https://sk-online-marketing.de)
