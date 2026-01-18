# Immobilien Rechner Pro

Professionelles WordPress-Plugin zur Immobilienbewertung. White-Label-Lösung für Immobilienmakler mit Lead-Generierung und CRM-Integration.

**Version:** 2.1.1 | **Autor:** [Stefan Kühne](https://sk-online-marketing.de) | **Lizenz:** GPL v2+

---

## Features

### Drei Rechner-Modi

| Modus | Beschreibung |
|-------|--------------|
| **Mietwert-Rechner** | Schätzung potenzieller Mieteinnahmen basierend auf Immobiliendaten |
| **Verkaufen vs. Vermieten** | Break-Even-Analyse mit interaktiven Charts |
| **Verkaufswert-Rechner** | Immobilienbewertung nach Vergleichs- und Sachwertverfahren (ImmoWertV) |

### Integrationen & Features

- **Lead-Generierung** mit 2-Stufen-Flow (Partial → Complete)
- **Propstack CRM** - Automatische Lead-Synchronisation mit Makler-Zuweisung
- **E-Mail mit PDF** - Professionelle Bewertung als PDF-Anhang
- **Google Ads Tracking** - Conversion-Events für Anfragen
- **Google Maps** - Adress-Autocomplete und Karten-Integration
- **reCAPTCHA v3** - Spam-Schutz für Lead-Formulare
- **White-Label-Ready** - Vollständig anpassbares Branding
- **GitHub Auto-Updater** - Automatische Updates
- **DSGVO-konform** - Integrierte Einwilligungsverwaltung

---

## Anforderungen

- WordPress 6.0+
- PHP 7.4+
- Node.js 18+ (nur für Entwicklung)

---

## Installation

### Produktion (empfohlen)

1. Release-ZIP von [GitHub Releases](https://github.com/AImitSK/immobilien-rechner-pro-v2/releases) herunterladen
2. WordPress Admin → Plugins → Installieren → Plugin hochladen
3. Plugin aktivieren

### Entwicklung

```bash
cd wp-content/plugins/
git clone https://github.com/AImitSK/immobilien-rechner-pro-v2.git
cd immobilien-rechner-pro-v2
npm install
npm run build
```

### Updates

Das Plugin prüft automatisch auf neue Versionen bei GitHub. Verfügbare Updates erscheinen unter **Dashboard → Aktualisierungen**.

---

## Quick Start

### 1. Shortcode einbinden

```
[immobilien_rechner]
```

### 2. Shortcode-Parameter

| Parameter | Werte | Beschreibung |
|-----------|-------|--------------|
| `mode` | `rental`, `comparison`, `sale_value` | Modus festlegen |
| `city_id` | z.B. `berlin`, `muenchen` | Stadt festlegen (überspringt Auswahl) |
| `theme` | `light`, `dark` | Farbschema |
| `show_branding` | `true`, `false` | Branding anzeigen/ausblenden |

### 3. Beispiele

```
[immobilien_rechner mode="rental"]
[immobilien_rechner mode="sale_value" city_id="muenchen"]
[immobilien_rechner mode="comparison" theme="dark"]
```

---

## Dokumentation

| Dokument | Beschreibung |
|----------|--------------|
| [Benutzerhandbuch](docs/BENUTZERHANDBUCH.md) | Anleitung für Plugin-Admins und Makler |
| [Konfiguration](docs/KONFIGURATION.md) | Alle Einstellungen im Detail |
| [API-Referenz](docs/API.md) | REST API Endpoints und Beispiele |
| [Entwickler](docs/ENTWICKLER.md) | Technische Details, Hooks, Dateistruktur |
| [Changelog](CHANGELOG.md) | Versionshistorie |

---

## Support

Für Support und Feature-Requests: [GitHub Issues](https://github.com/AImitSK/immobilien-rechner-pro-v2/issues)

---

## Autor

**Stefan Kühne**
[sk-online-marketing.de](https://sk-online-marketing.de)
