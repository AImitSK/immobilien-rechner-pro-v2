# Icon-Verzeichnis

Dieses Verzeichnis enthalt die SVG-Icons fur das Immobilien Rechner Pro Plugin.

## Verzeichnisstruktur

```
assets/icon/
  ausstattung/       # Ausstattungsmerkmale (Features)
  haustypen/         # Haustypen
  immobilientyp/     # Immobilientypen
  modernisierung/    # Modernisierungsicons
  nutzung/           # Nutzungsart-Icons
  qualitaetsstufen/  # Qualitats-Icons
  zeitrahmen/        # Zeitrahmen-Icons

assets/images/       # Legacy-Icons (altere Struktur)
```

## Icon-Kategorien

| Kategorie | Verzeichnis | Beschreibung |
|-----------|-------------|--------------|
| FEATURES | `ausstattung/`, `images/` | Balkon, Terrasse, Garten, etc. |
| PROPERTY_TYPES | `immobilientyp/`, `images/` | Wohnung, Haus, Gewerbe, Grundstuck |
| CONDITIONS | `images/` | Neubau, Renoviert, Gut, Reparaturen |
| QUALITY | `qualitaetsstufen/` | Einfach, Normal, Gehoben, Luxurios |
| USAGE | `nutzung/` | Kaufen, Verkaufen, Selbstgenutzt, etc. |
| HOUSE_TYPES | `haustypen/` | Einfamilienhaus, Mehrfamilienhaus, etc. |

## Namenskonventionen

- Dateinamen in Kleinbuchstaben
- Keine Leerzeichen, stattdessen Bindestriche oder Unterstriche
- Deutsche Begriffe (z.B. `einfamilienhaus.svg`, nicht `single-family-home.svg`)
- Umlaute vermeiden (z.B. `qualitaetsstufen`, nicht `qualitätsstufen`)

## Zentrale Icon-Verwaltung

Alle Icon-Pfade sind zentral in folgender Datei definiert:

```
src/utils/iconPaths.js
```

### Verwendung in Komponenten

```javascript
import { ICON_PATHS, getIconUrl } from '../../utils/iconPaths';

// Einzelnes Icon abrufen
const balkonUrl = getIconUrl(ICON_PATHS.FEATURES.balkon);

// Alle verfugbaren Kategorien:
// - ICON_PATHS.FEATURES
// - ICON_PATHS.PROPERTY_TYPES
// - ICON_PATHS.CONDITIONS
// - ICON_PATHS.QUALITY
// - ICON_PATHS.USAGE
// - ICON_PATHS.HOUSE_TYPES
// - ICON_PATHS.MISC
```

## Neue Icons hinzufugen

1. SVG-Datei im passenden Unterverzeichnis ablegen
2. Pfad in `src/utils/iconPaths.js` zur entsprechenden Kategorie hinzufugen
3. Bei neuer Kategorie: Neue Kategorie im ICON_PATHS-Objekt anlegen

## SVG-Richtlinien

- Format: SVG (Scalable Vector Graphics)
- Empfohlene ViewBox: `0 0 24 24` oder `0 0 48 48`
- Keine eingebetteten Rastergrafiken
- Optimiert mit SVGO oder ahnlichem Tool
