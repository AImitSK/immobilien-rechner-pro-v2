# Internationalisierung (i18n)

## Dateien

- `immobilien-rechner-pro.pot` - Template-Datei mit allen übersetzbaren Strings
- `immobilien-rechner-pro-de_DE.po` - Deutsche Übersetzungsdatei (Quelle: Deutsch)
- `immobilien-rechner-pro-de_DE.mo` - Kompilierte deutsche Übersetzungsdatei (muss erstellt werden)

## MO-Datei kompilieren

Die MO-Datei muss aus der PO-Datei kompiliert werden. Dies kann auf verschiedene Weisen erfolgen:

### Option 1: Mit msgfmt (gettext)

```bash
cd languages
msgfmt -o immobilien-rechner-pro-de_DE.mo immobilien-rechner-pro-de_DE.po
```

### Option 2: Mit WP-CLI

```bash
wp i18n make-mo languages/immobilien-rechner-pro-de_DE.po
```

### Option 3: Mit Poedit

1. Öffnen Sie `immobilien-rechner-pro-de_DE.po` in Poedit
2. Klicken Sie auf "Katalog speichern" (Strg+S)
3. Die MO-Datei wird automatisch erstellt

### Option 4: Online-Konverter

Verwenden Sie einen Online-PO-zu-MO-Konverter wie:
- https://localise.biz/free/poeditor

## Neue Sprache hinzufügen

1. Kopieren Sie die POT-Datei und benennen Sie sie nach dem Sprachcode:
   - Beispiel für Englisch: `immobilien-rechner-pro-en_US.po`
   - Beispiel für Französisch: `immobilien-rechner-pro-fr_FR.po`

2. Übersetzen Sie alle `msgstr ""`-Einträge in der neuen PO-Datei

3. Kompilieren Sie die PO-Datei zu einer MO-Datei

## JavaScript-Übersetzungen

Für JavaScript-Übersetzungen (React-Komponenten) verwendet WordPress das JSON-Format.

### JSON-Übersetzungsdateien generieren

```bash
wp i18n make-json languages/immobilien-rechner-pro-de_DE.po --no-purge
```

Dies erstellt JSON-Dateien wie `immobilien-rechner-pro-de_DE-[hash].json` für jeden JavaScript-Handle.

## Text Domain

Die Text Domain für dieses Plugin ist: `immobilien-rechner-pro`

Alle übersetzbaren Strings im JavaScript-Code verwenden:
```javascript
import { __ } from '@wordpress/i18n';

__('Übersetzungstext', 'immobilien-rechner-pro')
```

## Build-Prozess

Nach dem Webpack-Build werden die Übersetzungen automatisch geladen, wenn:
1. Die MO-Datei existiert (für PHP)
2. Die JSON-Dateien existieren (für JavaScript)
3. `wp_set_script_translations()` in `class-assets.php` aufgerufen wird
