# Changelog

Alle bemerkenswerten Änderungen an diesem Projekt werden in dieser Datei dokumentiert.

Das Format basiert auf [Keep a Changelog](https://keepachangelog.com/de/1.0.0/),
und dieses Projekt folgt [Semantic Versioning](https://semver.org/lang/de/).

## [2.0.0] - 2026-01-18

### Erste stabile Version

Professioneller Immobilien-Rechner für WordPress mit React-Frontend.

### Features

#### Rechner-Modi
- **Mietwert-Rechner**: Ermittelt den geschätzten Mietwert einer Immobilie
- **Verkauf vs. Vermietung**: Vergleicht die finanziellen Ergebnisse beider Optionen
- **Verkaufswert-Rechner**: Ermittelt den geschätzten Verkaufswert

#### Lead-Management
- Mehrstufiges Formular mit Zwischenspeicherung
- Automatische E-Mail-Benachrichtigungen
- Export-Funktionen (CSV)
- Propstack CRM-Integration

#### Internationalisierung
- Vollständige i18n-Unterstützung
- Deutsche Übersetzungsdateien (POT/PO)
- JavaScript-Übersetzungen via wp_set_script_translations

#### Sicherheit
- SQL Injection Prevention mit Prepared Statements
- Rate-Limiting (max 10 Anfragen/Stunde pro IP)
- reCAPTCHA v3 Integration
- CSRF-Schutz via WordPress Nonces
- Cookie-Validierung für Sessions

#### Icon-System
- 40+ SVG-Icons in kategorisierter Struktur
- CSS-Variablen für dynamische Farbgebung
- Icons passen sich automatisch an die Theme-Farbe an
- Inline-SVG-Loader mit Caching

#### Code-Qualität
- Zentrale Fehlerbehandlung (Error-Code-System E1xxx-E5xxx)
- Dokumentierte Parameter-Trennung (Berechnung vs. Lead-Daten)
- React-Komponenten mit WordPress-Patterns

### Technische Details

- WordPress 6.0+ kompatibel
- PHP 7.4+ erforderlich
- React 18 mit @wordpress/element
- @wordpress/i18n für Übersetzungen
- @heroicons/react für Standard-Icons
- ApexCharts für Diagramme
- Framer Motion für Animationen

### Dateien

#### Neue Verzeichnisstruktur
```
assets/icon/
├── ausstattung/      (16 Icons)
├── immobilientyp/    (4 Icons)
├── zustand/          (4 Icons)
├── haustypen/        (6 Icons)
├── qualitaetsstufen/ (4 Icons)
├── nutzung/          (5 Icons)
└── modernisierung/   (1 Icon)
```

#### Wichtige Dateien
- `includes/class-error-handler.php` - Zentrale Fehlerbehandlung
- `src/utils/errorHandler.js` - Frontend Error-Handler
- `src/components/Icon.js` - Inline-SVG-Komponente
- `src/utils/iconPaths.js` - Icon-Pfad-Verwaltung
- `languages/immobilien-rechner-pro.pot` - Übersetzungs-Template
- `languages/immobilien-rechner-pro-de_DE.po` - Deutsche Übersetzung
