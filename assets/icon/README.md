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
  zustand/           # Zustandsicons (Neubau, Renoviert, etc.)
```

## Icon-Kategorien

| Kategorie | Verzeichnis | Beschreibung |
|-----------|-------------|--------------|
| FEATURES | `ausstattung/` | Balkon, Terrasse, Garten, Garage, Keller, etc. |
| PROPERTY_TYPES | `immobilientyp/` | Wohnung, Haus, Gewerbe, Grundstuck |
| CONDITIONS | `zustand/` | Neubau, Renoviert, Gut, Reparaturen |
| QUALITY | `qualitaetsstufen/` | Einfach, Normal, Gehoben, Luxurios |
| USAGE | `nutzung/` | Kaufen, Verkaufen, Selbstgenutzt, etc. |
| HOUSE_TYPES | `haustypen/` | Einfamilienhaus, Mehrfamilienhaus, etc. |
| MISC | `modernisierung/`, `zeitrahmen/` | Sonstige Icons |

## Namenskonventionen

- Dateinamen in Kleinbuchstaben
- Keine Leerzeichen, stattdessen Bindestriche oder Unterstriche
- Deutsche Begriffe (z.B. `einfamilienhaus.svg`, nicht `single-family-home.svg`)
- Umlaute vermeiden (z.B. `qualitaetsstufen`, nicht `qualitaetsstufen`)

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

### Icon-Komponente verwenden

```javascript
import Icon from '../components/Icon';
import { ICON_PATHS } from '../utils/iconPaths';

// Mit Icon-Komponente (ladt SVG inline, ermoglicht CSS-Theming)
<Icon path={ICON_PATHS.FEATURES.balkon} size={48} />
<Icon path={ICON_PATHS.FEATURES.terrasse} width={64} height={64} />

// Theme-Farben werden automatisch angewendet
```

## CSS-Variablen fur Icon-Theming

Die Icons verwenden CSS-Variablen fur dynamische Farbanpassung:

```css
:root {
  --irp-icon-primary: #428dff;    /* Hauptfarbe */
  --irp-icon-secondary: #7facfa;  /* Sekundarfarbe (50% heller) */
  --irp-icon-light: #a4c2f7;      /* Helle Farbe (70% heller) */
  --irp-icon-bg: #e8edfc;         /* Hintergrundfarbe (90% heller) */
}
```

Diese Variablen werden automatisch basierend auf der `primaryColor` aus den Plugin-Einstellungen generiert.

## Neue Icons hinzufugen

1. SVG-Datei im passenden Unterverzeichnis ablegen
2. Pfad in `src/utils/iconPaths.js` zur entsprechenden Kategorie hinzufugen
3. Bei neuer Kategorie: Neue Kategorie im ICON_PATHS-Objekt anlegen

## SVG-Richtlinien

- Format: SVG (Scalable Vector Graphics)
- Empfohlene ViewBox: `0 0 60 60` oder ahnlich
- Keine eingebetteten Rastergrafiken
- Optimiert mit SVGO oder ahnlichem Tool
- CSS-Variablen fur Farben verwenden:
  - `fill="var(--irp-icon-primary, #428dff)"` fur Hauptfarbe
  - `fill="var(--irp-icon-secondary, #7facfa)"` fur Sekundarfarbe
  - `fill="var(--irp-icon-light, #a4c2f7)"` fur helle Bereiche
  - `fill="var(--irp-icon-bg, #e8edfc)"` fur Hintergrundbereiche

### SVG-Template

Jedes SVG sollte folgenden Style-Block nach dem offnenden `<svg>` Tag enthalten:

```xml
<svg ...>
<style>:root{--irp-icon-primary:#428dff;--irp-icon-secondary:#7facfa;--irp-icon-light:#a4c2f7;--irp-icon-bg:#e8edfc;}</style>
...
</svg>
```
