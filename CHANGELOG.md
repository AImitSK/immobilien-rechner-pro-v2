# Changelog

Alle wichtigen Änderungen am Immobilien Rechner Pro werden hier dokumentiert.

Das Format basiert auf [Keep a Changelog](https://keepachangelog.com/de/1.0.0/).

---

## [2.1.1] - 2026-01-17

### Behoben
- API-Parameter-Handling für `sale_value` Modus korrigiert

---

## [2.1.0] - 2026-01-17

### Neu
- **Verkaufswert-Rechner** (`mode="sale_value"`) mit drei Bewertungsverfahren:
  - Vergleichswertverfahren für Wohnungen
  - Sachwertverfahren für Häuser (Bodenwert + Gebäudewert)
  - Bodenwertverfahren für Grundstücke
- Fiktives Baujahr nach ImmoWertV (Modernisierung verschiebt Alter)
- Marktanpassungsfaktor pro Stadt konfigurierbar
- 6 neue React-Komponenten für Sale-Value-Flow
- Neue PHP-Klasse `class-sale-calculator.php`

### Geändert
- Städte-Matrix um Verkaufswert-Felder erweitert (Bodenrichtwert, Gebäudepreis/m², Wohnungspreis/m²)
- Shortcode unterstützt jetzt `mode="sale_value"`
- Lead-Tabelle um Sale-Value-Felder erweitert

---

## [1.5.8] - 2025-XX-XX

### Behoben
- Telefonnummer wird jetzt auch bei bestehenden Kontakten aktualisiert
- Automatisches DB-Upgrade bei Plugin-Updates (nicht nur bei Aktivierung)
- Fehlende Propstack-Spalten werden automatisch hinzugefügt

### Entfernt
- Debug-Box aus Lead-Detail-Ansicht

---

## [1.5.7] - 2025-XX-XX

### Behoben
- Telefonnummer wird jetzt korrekt an Propstack übertragen (verwendet `home_cell` statt `phone`)
- Propstack-Status liest jetzt direkt aus DB statt aus gecachtem Lead-Objekt

### Hinzugefügt
- Temporäre Debug-Box in Lead-Detail-Ansicht für Propstack-Werte (später entfernt)

---

## [1.5.6] - 2025-XX-XX

### Behoben
- Vollständige Kontaktdaten werden jetzt abgerufen (inkl. Beschreibung) bevor Update
- DB-Update verwendet jetzt direktes SQL für mehr Zuverlässigkeit

---

## [1.5.5] - 2025-XX-XX

### Neu
- Bei existierenden Kontakten wird die neue Anfrage zur Beschreibung hinzugefügt (keine Duplikate)

### Behoben
- Propstack Sync speichert jetzt `propstack_id` korrekt in der Datenbank
- NULL-Wert bei `propstack_error` durch leeren String ersetzt

### Verbessert
- Besseres Logging bei DB-Update Fehlern

---

## [1.5.4] - 2025-XX-XX

### Behoben
- AJAX-Handler für manuellen Propstack-Sync korrigiert (behandelt jetzt WP_Error korrekt)

---

## [1.5.3] - 2025-XX-XX

### Neu
- "Jetzt synchronisieren" Button für ausstehende Leads in der Detail-Ansicht

### Behoben
- Propstack-Sync kann jetzt manuell ausgelöst werden (nicht nur bei Fehlern)

---

## [1.5.2] - 2025-XX-XX

### Neu
- Google Maps Autocomplete auf gewählte Stadt eingeschränkt
- Eingegebene Adresse wird im PDF angezeigt (Standort zeigt jetzt Stadt + Straße)

### Verbessert
- Placeholder im Adressfeld zeigt "Straße und Hausnummer in [Stadt]..."
- Karte wird automatisch auf die gewählte Stadt zentriert

---

## [1.5.1] - 2025-XX-XX

### Behoben
- Propstack API-Authentifizierung korrigiert (X-API-KEY Header statt Bearer Token)
- Verbindungstest funktioniert jetzt auch vor dem Speichern des API-Keys

---

## [1.5.0] - 2025-XX-XX

### Neu
- Konfigurierbare Baualters-Multiplikatoren im Admin unter "Matrix & Daten → Multiplikatoren"
- 7 branchenübliche Baualtersklassen (Altbau bis 1945 bis Neubau ab 2015)

### Geändert
- Zustandsoption "Neubau / Erstbezug" zu "Neubau / Kernsaniert" umbenannt
- Währungsbeträge werden jetzt mit 2 Dezimalstellen angezeigt

### Entfernt
- Multiplikator-Anzeige bei der Lage-Bewertung im Frontend ausgeblendet

---

## [1.4.2] - 2025-XX-XX

### Behoben
- Lead-Löschen-Button in Detail-Ansicht funktioniert jetzt

### Neu
- Mehrfachauswahl und Bulk-Delete in Lead-Tabelle

### Geändert
- Objekt-Spalte aus Lead-Tabelle entfernt (Platzersparnis)

---

## [1.4.1] - 2025-XX-XX

### Behoben
- Complete Lead Tracking wird jetzt auf der Results-Seite gefeuert
- Timing-Probleme mit GTM Event-Verarbeitung behoben

---

## [1.4.0] - 2025-XX-XX

### Neu
- **Google Ads Conversion Tracking**
- Neuer Settings-Tab "Tracking"
- Zwei Conversion-Events: Anfrage gestartet / Anfrage abgeschlossen
- DataLayer Events für Google Tag Manager (`irp_partial_lead`, `irp_complete_lead`)

---

## [1.3.0] - 2025-XX-XX

### Neu
- **Propstack CRM Integration**
- Neue Admin-Seite "Integrationen" mit 3 Tabs (Verbindung, Makler-Zuweisung, Newsletter)
- Automatische Lead-Synchronisation bei Vervollständigung
- Makler-Zuweisung nach Stadt
- Propstack-Status in Lead-Liste und Detail-Ansicht
- Retry-Button für fehlgeschlagene Syncs
- 4 neue Datenbank-Spalten für Sync-Tracking

---

## [1.2.0] - 2025-XX-XX

### Neu
- **E-Mail mit PDF-Anhang** an Leads
- DOMPDF für PDF-Generierung gebündelt
- Erweiterte Branding-Einstellungen (mehrzeilige Firma, Adresse, Logo-Breite)
- E-Mail-Status in Lead-Liste

### Geändert
- Settings-Seite in Tabs reorganisiert
- PHP 7.4 Kompatibilität

---

## [1.1.0] - 2025-XX-XX

### Neu
- **Lead Magnet Flow** mit Partial Leads
- **reCAPTCHA v3** Integration
- **GitHub Auto-Updater**
- Debug-Logging

---

## [1.0.0] - 2025-XX-XX

### Neu
- Initiales Release
- Mietwert-Rechner
- Verkaufen vs. Vermieten Vergleich
- Städte-System mit individueller Konfiguration
- 5-stufige Lage-Bewertung
- Lead-Generierung mit E-Mail-Benachrichtigung
- White-Label Branding
- Responsives Design
