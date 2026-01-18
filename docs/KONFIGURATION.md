# Konfiguration

Detaillierte Dokumentation aller Einstellungsmöglichkeiten des Immobilien Rechner Pro.

---

## Inhaltsverzeichnis

1. [Settings (Allgemeine Einstellungen)](#settings)
2. [Integrationen](#integrationen)
3. [Matrix & Daten](#matrix--daten)

---

## Settings

**Pfad:** WordPress Admin → Immo Rechner → Settings

### Tab: Allgemein

| Einstellung | Typ | Standard | Beschreibung |
|-------------|-----|----------|--------------|
| Maximale Breite | Pixel | 680 | Breite des Rechner-Containers |
| Primärfarbe | Hex | #2563eb | Hauptfarbe für Buttons und Akzente |
| Sekundärfarbe | Hex | #1e40af | Farbe für Hover-Effekte |
| Datenschutz-URL | URL | - | Link zur Datenschutzerklärung |
| Einwilligung erforderlich | Checkbox | Ja | Consent-Checkbox im Kontaktformular |
| Wartungsrate | % | 1.5 | Standard-Instandhaltungskosten |
| Leerstandsrate | % | 3.0 | Standard-Leerstandsquote |
| Makler-Provision | % | 3.57 | Standard-Maklerprovision |

### Tab: Branding & Kontakt

| Einstellung | Typ | Beschreibung |
|-------------|-----|--------------|
| Firmenlogo | Bild-Upload | Logo für E-Mails und PDFs |
| Logo-Breite | Pixel | Breite des Logos (Höhe proportional) |
| Firmenname | Text (3 Zeilen) | Name für E-Mail-Signatur |
| Straße | Text | Adresse für Footer |
| PLZ/Ort | Text | Ort für Footer |
| Telefon | Text | Kontakttelefon |
| E-Mail | E-Mail | Kontakt-E-Mail |
| Website | URL | Firmen-Website |

### Tab: E-Mail

| Einstellung | Typ | Standard | Beschreibung |
|-------------|-----|----------|--------------|
| E-Mail-Versand aktivieren | Checkbox | Nein | Aktiviert automatischen E-Mail-Versand |
| Absender-Name | Text | - | Name im "Von"-Feld |
| Absender-E-Mail | E-Mail | - | E-Mail im "Von"-Feld |
| An Admin senden | Checkbox | Ja | Benachrichtigung an Admin |
| An Lead senden | Checkbox | Nein | Ergebnis-E-Mail an Lead |
| PDF anhängen | Checkbox | Nein | PDF-Bewertung als Anhang |

**SMTP-Einstellungen (optional):**

| Einstellung | Typ | Standard |
|-------------|-----|----------|
| SMTP-Host | Text | - |
| SMTP-Port | Zahl | 587 |
| SMTP-Benutzer | Text | - |
| SMTP-Passwort | Passwort | - |
| Verschlüsselung | Select | TLS |

**E-Mail-Vorlage:**

- WYSIWYG-Editor für E-Mail-Inhalt
- Variablen: `{name}`, `{city}`, `{property_type}`, `{size}`, `{result_value}`
- Test-E-Mail versenden (Button)

### Tab: reCAPTCHA

| Einstellung | Typ | Beschreibung |
|-------------|-----|--------------|
| Site Key | Text | reCAPTCHA v3 Website-Schlüssel |
| Secret Key | Text | reCAPTCHA v3 Geheimer Schlüssel |
| Mindest-Score | Zahl (0.0-1.0) | Schwellenwert für Validierung (Standard: 0.5) |

**Hinweis:** reCAPTCHA v3 arbeitet unsichtbar. Ein Score von 1.0 bedeutet "definitiv Mensch", 0.0 bedeutet "definitiv Bot".

[reCAPTCHA-Schlüssel erstellen](https://www.google.com/recaptcha/admin)

### Tab: Google Maps

| Einstellung | Typ | Beschreibung |
|-------------|-----|--------------|
| API Key | Text | Google Maps JavaScript API Key |
| Karte anzeigen | Checkbox | Karte im Lage-Step einblenden |

**Benötigte Google APIs:**
- Maps JavaScript API
- Places API
- Geocoding API

[Google Cloud Console](https://console.cloud.google.com/apis)

### Tab: Tracking

| Einstellung | Typ | Beispiel | Beschreibung |
|-------------|-----|----------|--------------|
| Conversion ID | Text | AW-123456789 | Google Ads Conversion-ID |
| Partial Label | Text | AbCdEfGhI | Label für "Anfrage gestartet" |
| Complete Label | Text | JkLmNoPqR | Label für "Anfrage abgeschlossen" |

**DataLayer Events (automatisch):**

```javascript
// Bei Partial Lead (Berechnung durchgeführt)
dataLayer.push({
    event: 'irp_partial_lead',
    mode: 'rental',
    city: 'muenchen'
});

// Bei Complete Lead (Kontaktformular gesendet)
dataLayer.push({
    event: 'irp_complete_lead',
    mode: 'rental',
    city: 'muenchen',
    lead_id: 123
});
```

---

## Integrationen

**Pfad:** WordPress Admin → Immo Rechner → Integrationen

### Propstack CRM

#### Tab: Verbindung

| Einstellung | Typ | Beschreibung |
|-------------|-----|--------------|
| Integration aktivieren | Checkbox | Aktiviert Propstack-Synchronisation |
| API Key | Text | Propstack API-Schlüssel |
| Verbindung testen | Button | Testet die API-Verbindung |

**Status-Anzeige:**
- Verbunden (grün)
- Nicht verbunden (rot)
- API-Fehler mit Meldung

#### Tab: Makler-Zuweisung

Tabelle mit allen konfigurierten Städten:

| Stadt | Zugewiesener Makler |
|-------|---------------------|
| München | [Dropdown: Makler aus Propstack] |
| Berlin | [Dropdown: Makler aus Propstack] |
| ... | ... |

- Makler werden automatisch von der Propstack API geladen
- Standard-Makler für nicht zugewiesene Städte konfigurierbar

#### Tab: Newsletter

| Einstellung | Typ | Beschreibung |
|-------------|-----|--------------|
| Newsletter aktivieren | Checkbox | Sendet Newsletter-Consent an Propstack |
| Newsletter Snippet ID | Text | Propstack Snippet für Newsletter-Tag |

#### Tab: Aktivitäten

| Einstellung | Typ | Beschreibung |
|-------------|-----|--------------|
| Aktivitäten erstellen | Checkbox | Erstellt Aktivität bei neuem Lead |
| Aktivitätstyp | Dropdown | Typ der Aktivität in Propstack |
| Task erstellen | Checkbox | Erstellt Follow-up Task |
| Task-Fälligkeit | Zahl | Tage bis zur Fälligkeit |

---

## Matrix & Daten

**Pfad:** WordPress Admin → Immo Rechner → Matrix & Daten

### Tab: Städte

Konfiguration der verfügbaren Städte:

| Feld | Typ | Verwendung |
|------|-----|------------|
| ID | Text | Technischer Identifier (URL-freundlich) |
| Name | Text | Anzeigename |
| Basis-Mietpreis | €/m² | Referenzpreis für 70m² Wohnung |
| Größendegression | % | Prozentuale Preisanpassung pro m² |
| Vervielfältiger | Faktor | Kaufpreis = Jahresmiete × Faktor |

**Verkaufswert-Felder (NEU):**

| Feld | Typ | Verwendung |
|------|-----|------------|
| Bodenrichtwert | €/m² | Grundstückspreis |
| Gebäudepreis/m² | €/m² | Für Sachwertverfahren (Häuser) |
| Wohnungspreis/m² | €/m² | Für Vergleichswertverfahren |
| Marktanpassung | Faktor | 0.8-1.4 für regionale Marktlage |

**Marktanpassungsfaktor:**
- < 1.0: Käufermarkt / ländliche Region
- = 1.0: Ausgeglichener Markt
- > 1.0: Verkäufermarkt / Boom-Region

### Tab: Multiplikatoren

#### Zustands-Multiplikatoren (Mietwert)

| Zustand | Faktor | Beschreibung |
|---------|--------|--------------|
| Neubau / Kernsaniert | 1.25 | Erstbezug oder komplett saniert |
| Renoviert | 1.10 | Kürzlich renoviert |
| Gut | 1.00 | Basis-Referenz |
| Sanierungsbedürftig | 0.80 | Renovierungsbedarf |

#### Objekttyp-Multiplikatoren (Mietwert)

| Typ | Faktor |
|-----|--------|
| Wohnung | 1.00 |
| Haus | 1.15 |
| Gewerbe | 0.85 |

#### Baualters-Multiplikatoren (Mietwert)

| Baualter | Faktor |
|----------|--------|
| Altbau (bis 1945) | 1.05 |
| Nachkriegsbau (1946-1959) | 0.95 |
| 60er/70er Jahre | 0.90 |
| 80er Jahre | 0.95 |
| 90er Jahre | 1.00 |
| 2000-2014 | 1.05 |
| Neubau (ab 2015) | 1.10 |

#### Haustyp-Multiplikatoren (Verkaufswert)

| Typ | Faktor |
|-----|--------|
| Einfamilienhaus | 1.00 |
| Mehrfamilienhaus | 1.15 |
| Doppelhaushälfte | 0.95 |
| Mittelreihenhaus | 0.88 |
| Endreihenhaus | 0.92 |
| Bungalow | 1.05 |

#### Qualitäts-Multiplikatoren (Verkaufswert)

| Qualität | Faktor |
|----------|--------|
| Einfach | 0.85 |
| Normal | 1.00 |
| Gehoben | 1.15 |
| Luxuriös | 1.35 |

#### Modernisierungs-Verschiebung (Verkaufswert)

| Modernisierung | Jahre |
|----------------|-------|
| Vor 1-3 Jahren | +15 |
| Vor 4-9 Jahren | +10 |
| Vor 10-15 Jahren | +5 |
| Vor mehr als 15 Jahren | 0 |
| Noch nie | 0 |

### Tab: Ausstattung

#### Mietwert-Zuschläge (€/m²)

| Feature | Zuschlag |
|---------|----------|
| Balkon | 0,50 € |
| Terrasse | 0,75 € |
| Garten | 1,00 € |
| Aufzug | 0,30 € |
| Stellplatz | 0,40 € |
| Garage | 0,60 € |
| Keller | 0,20 € |
| Einbauküche | 0,50 € |
| Fußbodenheizung | 0,40 € |
| Gast-WC | 0,25 € |
| Barrierefrei | 0,30 € |

#### Verkaufswert-Zuschläge (Absolut €)

**Außen:**
| Feature | Wert |
|---------|------|
| Balkon | 5.000 € |
| Garage | 15.000 € |
| Stellplatz | 8.000 € |
| Garten | 8.000 € |
| Terrasse | 6.000 € |
| Solaranlage | 12.000 € |

**Innen:**
| Feature | Wert |
|---------|------|
| Aufzug | 20.000 € |
| Einbauküche | 8.000 € |
| Kamin | 6.000 € |
| Parkettboden | 4.000 € |
| Keller | 10.000 € |
| Dachboden | 5.000 € |

### Tab: Lage-Faktoren

| Stufe | Name | Faktor | Beispiel |
|-------|------|--------|----------|
| 1 | Einfach | 0.85 | Industrienähe, hoher Verkehr |
| 2 | Normal | 0.95 | Durchschnittliche Wohnlage |
| 3 | Gut | 1.00 | Gute Infrastruktur |
| 4 | Sehr gut | 1.10 | Ruhig, gute Anbindung |
| 5 | Premium | 1.25 | Beste Lage, Park, See |

### Tab: Globale Parameter

| Parameter | Standard | Beschreibung |
|-----------|----------|--------------|
| Zinssatz | 3.0% | Für Kapitalanlage-Berechnung |
| Wertsteigerung | 2.0% | Jährliche Immobilienwertsteigerung |
| Mietentwicklung | 2.0% | Jährliche Mieterhöhung |

### Tab: Altersabschlag (Verkaufswert)

| Parameter | Wert |
|-----------|------|
| Abschlag pro Jahr | 1% |
| Maximaler Abschlag | 40% |
| Basis-Jahr | 2025 |

---

## WordPress Options (Technisch)

Die Einstellungen werden in folgenden WordPress Options gespeichert:

| Option | Beschreibung |
|--------|--------------|
| `irp_settings` | Haupteinstellungen |
| `irp_price_matrix` | Städte, Multiplikatoren, Features |
| `irp_email_settings` | E-Mail-Konfiguration |
| `irp_propstack_settings` | Propstack-Integration |
| `irp_sale_value_settings` | Verkaufswert-spezifische Settings |
| `irp_db_version` | Datenbank-Version |
