# Feature: Propstack Integration

## Übersicht

Integration des Immobilien-Rechner Pro Plugins mit Propstack CRM. Leads werden automatisch an Propstack übertragen und dem zuständigen Makler zugewiesen.

## Ziele

1. **Automatische Lead-Übertragung**: Jeder neue Lead wird in Propstack angelegt
2. **Makler-Zuweisung nach Stadt**: Jede Stadt kann einem Makler zugewiesen werden
3. **Newsletter Double-Opt-In**: Bei Newsletter-Consent wird DOI-Mail über Propstack versendet
4. **Fehlerbehandlung**: Bei Fehlern kann die Übertragung manuell wiederholt werden

---

## API-Endpunkte

### Verwendete Propstack API-Endpunkte

| Endpunkt | Methode | Beschreibung |
|----------|---------|--------------|
| `/v1/brokers` | GET | Liste aller Makler abrufen |
| `/v1/contacts` | POST | Kontakt erstellen/aktualisieren |
| `/v1/contact_sources` | GET | Kontaktquellen abrufen |
| `/v1/messages` | POST | E-Mail versenden (für Newsletter DOI) |

### Benötigte API-Key Berechtigungen

- Kontakte lesen
- Kontakte schreiben
- Nutzer/Brokers lesen
- Nachrichten schreiben
- Kontaktquellen lesen

---

## Admin-Bereich: Integrationen

### Neuer Menüpunkt

```
Immo Rechner Pro
├── Dashboard
├── Leads
├── Preismatrix
├── Shortcode Generator
├── Einstellungen
└── Integrationen (NEU)
    └── Propstack
```

### Propstack-Einstellungen

#### Tab 1: Verbindung

| Feld | Typ | Beschreibung |
|------|-----|--------------|
| Integration aktiv | Checkbox | Aktiviert/Deaktiviert die Propstack-Integration |
| API-Key | Password-Input | Propstack API-Schlüssel |
| Verbindung testen | Button | Testet die API-Verbindung |
| Status | Anzeige | Zeigt Verbindungsstatus (Verbunden/Fehler) |

#### Tab 2: Makler-Zuweisung

**UI-Konzept:**

```
┌─────────────────────────────────────────────────────────────┐
│ Makler-Zuweisung nach Stadt                                 │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Stadt          │ Zuständiger Makler           │ Aktion    │
│  ───────────────┼──────────────────────────────┼───────────│
│  Berlin         │ [Dropdown: Max Müller    ▼]  │ [Zuweisen]│
│  München        │ [Dropdown: Anna Schmidt  ▼]  │ [Zuweisen]│
│  Hamburg        │ [Dropdown: -- Auswählen --▼] │ [Zuweisen]│
│  Frankfurt      │ [Dropdown: Tom Weber     ▼]  │ [Zuweisen]│
│                                                             │
│  ──────────────────────────────────────────────────────────│
│  Standard-Makler (Fallback):                                │
│  [Dropdown: Max Müller ▼]                                   │
│                                                             │
│  [Alle Zuweisungen speichern]                               │
└─────────────────────────────────────────────────────────────┘
```

**Funktionsweise:**
- Die Tabelle zeigt alle angelegten Städte aus der Preismatrix
- Dropdown enthält alle Makler aus Propstack (via API abgerufen)
- "Zuweisen" Button speichert einzelne Zuweisung
- "Alle Zuweisungen speichern" speichert alles auf einmal
- Standard-Makler wird verwendet wenn Stadt keinen zugewiesenen Makler hat

**Datenstruktur (WordPress Option):**

```php
// Option: irp_propstack_settings
[
    'enabled' => true,
    'api_key' => 'xxx',
    'default_broker_id' => 123,
    'city_broker_mapping' => [
        'berlin' => 123,      // city_id => broker_id
        'muenchen' => 456,
        'hamburg' => 789,
    ],
    'contact_source_id' => 42,
    'newsletter_snippet_id' => 1234,
    'newsletter_broker_id' => 123,
]
```

#### Tab 3: Newsletter-Einstellungen

| Feld | Typ | Beschreibung |
|------|-----|--------------|
| Newsletter DOI aktiv | Checkbox | Aktiviert Double-Opt-In über Propstack |
| Textbaustein-ID | Number | ID des Textbausteins für DOI-Mail |
| Absender | Dropdown | Makler der als Absender fungiert |

**Hinweis-Box:**
> Der Textbaustein muss in Propstack angelegt werden und die Variable `{{ kontakt_link }}` enthalten.
> Beim Klick auf diesen Link werden die Newsletter-Flags beim Kontakt aktiviert.

---

## Lead-Synchronisation

### Ablauf bei neuem Lead

```
┌─────────────────────────────────────────────────────────────┐
│                    LEAD EINGEGANGEN                         │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│  1. Lead in WordPress speichern                             │
│     Status: "partial" oder "complete"                       │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│  2. Propstack-Integration aktiv?                            │
│     Nein → Fertig                                           │
│     Ja   → Weiter                                           │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│  3. Broker-ID ermitteln                                     │
│     - Stadt des Leads prüfen                                │
│     - Zugewiesenen Makler aus Mapping holen                 │
│     - Fallback: Standard-Makler                             │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│  4. POST /v1/contacts                                       │
│     - Kontaktdaten senden                                   │
│     - broker_id setzen                                      │
│     - client_source_id setzen                               │
│     - Berechnungsergebnis in description                    │
└─────────────────────┬───────────────────────────────────────┘
                      │
              ┌───────┴───────┐
              │               │
          Erfolg           Fehler
              │               │
              ▼               ▼
┌─────────────────┐   ┌─────────────────────────────┐
│ Lead-Status:    │   │ Lead-Status:                │
│ propstack_id    │   │ propstack_error = "..."     │
│ speichern       │   │ propstack_synced = false    │
└────────┬────────┘   └─────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────────────────────────┐
│  5. Newsletter-Consent vorhanden?                           │
│     Nein → Fertig                                           │
│     Ja   → POST /v1/messages (DOI-Mail)                     │
└─────────────────────────────────────────────────────────────┘
```

### Daten die an Propstack gesendet werden

```json
{
  "client": {
    "salutation": "mr",
    "first_name": "Max",
    "last_name": "Mustermann",
    "email": "max@beispiel.de",
    "phone": "+49 123 456789",
    "broker_id": 123,
    "client_source_id": 42,
    "description": "Anfrage über Immobilien-Rechner Pro\n\n--- Berechnungsergebnis ---\nModus: Mietwertberechnung\nObjekttyp: Wohnung\nGröße: 85 m²\nStadt: Berlin\nZustand: Gut\n\nGeschätzte Miete: 1.250 €/Monat\nPreis pro m²: 14,70 €"
  }
}
```

---

## Fehlerbehandlung

### Neue Datenbank-Felder für Leads

```sql
ALTER TABLE wp_irp_leads ADD COLUMN propstack_id BIGINT DEFAULT NULL;
ALTER TABLE wp_irp_leads ADD COLUMN propstack_synced TINYINT(1) DEFAULT 0;
ALTER TABLE wp_irp_leads ADD COLUMN propstack_error TEXT DEFAULT NULL;
ALTER TABLE wp_irp_leads ADD COLUMN propstack_synced_at DATETIME DEFAULT NULL;
```

### Lead-Liste Anpassungen

**Neue Spalte: "Propstack Status"**

| Status | Anzeige | Aktion |
|--------|---------|--------|
| Synchronisiert | ✅ Grüner Haken + Propstack-ID | Link zu Propstack |
| Ausstehend | ⏳ Gelb | - |
| Fehler | ❌ Rot + Fehlermeldung | [Erneut versuchen] Button |
| Deaktiviert | ⚫ Grau "Nicht aktiv" | - |

**Fehleranzeige in Lead-Detail:**

```
┌─────────────────────────────────────────────────────────────┐
│ ⚠️ Propstack-Synchronisation fehlgeschlagen                │
│                                                             │
│ Fehler: API-Key besitzt nicht genügend Rechte              │
│ Letzter Versuch: 10.01.2026 14:23                          │
│                                                             │
│ [Erneut versuchen]                                          │
└─────────────────────────────────────────────────────────────┘
```

### Manueller Sync

- Button "Erneut versuchen" bei fehlgeschlagenen Leads
- Bulk-Action: "An Propstack senden" für mehrere Leads
- Cron-Job (optional): Automatischer Retry alle X Stunden

---

## Suchprofil (Erklärung)

### Was ist ein Suchprofil?

Ein Suchprofil in Propstack speichert die Suchkriterien eines Interessenten:

- **Objekttyp**: Wohnung, Haus, Gewerbe
- **Größe**: Min/Max Quadratmeter
- **Preis**: Min/Max Miete oder Kaufpreis
- **Lage**: Stadt, Region, PLZ-Bereich
- **Ausstattung**: Balkon, Garten, Aufzug, etc.

### Nutzen für den Makler

Wenn ein passende Immobilie ins System kommt, kann Propstack automatisch:
1. Matching durchführen (Objekt ↔ Suchprofile)
2. Interessenten benachrichtigen
3. Exposé versenden

### Umsetzung im Plugin

**Optional für Phase 2:**

Da wir bereits Immobiliendaten erfassen (Größe, Typ, Stadt), könnten wir diese als Suchprofil anlegen:

```json
{
  "query": {
    "marketing_type": "RENT",
    "rs_types": ["APARTMENT"],
    "living_space": 80,
    "living_space_to": 100,
    "city": "Berlin"
  }
}
```

→ Der Interessent bekommt dann automatisch passende Angebote.

**Frage an Kunden:** Soll diese Funktion implementiert werden?

---

## Implementierungsplan

### Phase 1: Basis-Integration

1. [ ] Neue Klasse `IRP_Propstack` erstellen
2. [ ] Admin-Bereich "Integrationen" anlegen
3. [ ] API-Verbindung implementieren (Test-Button)
4. [ ] Makler-Liste von API abrufen
5. [ ] Stadt-Makler-Mapping UI bauen
6. [ ] Lead-Sync bei Erstellung implementieren
7. [ ] Datenbank-Felder für Sync-Status hinzufügen
8. [ ] Fehleranzeige in Lead-Liste
9. [ ] "Erneut versuchen" Button

### Phase 2: Newsletter & Erweitert

1. [ ] Newsletter DOI-Mail Integration
2. [ ] Suchprofil-Erstellung (optional)
3. [ ] Bulk-Sync für bestehende Leads
4. [ ] Automatischer Retry via Cron

---

## Offene Fragen

1. ~~Stadt-Mapping: Soll das über PLZ oder Städtenamen funktionieren?~~
   → Antwort: Über die bereits angelegten Städte in der Preismatrix

2. Suchprofil: Soll diese Funktion implementiert werden?
   → Entscheidung ausstehend

3. Automatischer Retry: Soll ein Cron-Job fehlgeschlagene Syncs wiederholen?
   → Entscheidung ausstehend

4. Propstack-Link: Soll in der Lead-Liste ein direkter Link zum Kontakt in Propstack angezeigt werden?
   → Vermutlich ja, wenn propstack_id vorhanden

---

## Technische Details

### Neue Dateien

```
includes/
└── class-propstack.php          # API-Wrapper Klasse

admin/
├── views/
│   └── integrations.php         # Integrationen-Seite
└── js/
    └── integrations.js          # JS für Makler-Dropdown etc.
```

### Hooks

```php
// Nach Lead-Erstellung
add_action('irp_lead_created', ['IRP_Propstack', 'sync_lead'], 10, 2);

// Nach Lead-Vervollständigung
add_action('irp_lead_completed', ['IRP_Propstack', 'sync_lead'], 10, 2);
```

### API-Wrapper Beispiel

```php
class IRP_Propstack {

    private string $api_key;
    private string $base_url = 'https://api.propstack.de/v1';

    public function get_brokers(): array { }

    public function create_contact(array $data): int|WP_Error { }

    public function send_message(int $broker_id, string $email, int $snippet_id): bool { }

    public function test_connection(): bool { }
}
```
