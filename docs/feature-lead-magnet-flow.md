# Feature: Lead-Magnet Flow

## Übersicht

Umstellung des Rechner-Flows auf einen Lead-Magnet-Ansatz, bei dem Nutzer ihre Kontaktdaten eingeben müssen, bevor sie das Berechnungsergebnis sehen.

---

## Neuer Flow

```
┌─────────────────────────────────────────────────────────────────────────────┐
│  PHASE 1: WIZARD-STEPS (unverändert)                                        │
│                                                                             │
│  Immobilienart → Details → Standort → Zustand → Lage → Ausstattung          │
│                                                                             │
│                         [Button: Berechnen]                                 │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│  PHASE 2: PARTIAL LEAD SPEICHERUNG                                          │
│                                                                             │
│  • API-Call: POST /irp/v1/leads/partial                                     │
│  • Alle Immobiliendaten werden in DB gespeichert                            │
│  • Status: "partial"                                                        │
│  • Berechnung wird durchgeführt und in calculation_data gespeichert         │
│  • Lead-ID wird zurückgegeben für spätere Vervollständigung                 │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│  PHASE 3: LOADER-ANIMATION                                                  │
│                                                                             │
│  • 2-3 Sekunden Animation                                                   │
│  • Text: "Ihre Berechnung wird erstellt..."                                 │
│  • Fortschrittsbalken oder Spinner                                          │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│  PHASE 4: KONTAKTFORMULAR-STEP                                              │
│                                                                             │
│  ┌───────────────────────────────────────────────────────────────────────┐  │
│  │                                                                       │  │
│  │   ✓ Ihre Berechnung ist fertig!                                       │  │
│  │                                                                       │  │
│  │   Bitte geben Sie uns noch ein paar Informationen,                    │  │
│  │   damit wir Ihnen das Ergebnis zusenden können.                       │  │
│  │                                                                       │  │
│  │   ┌─────────────────────────────────────────────────────────────┐     │  │
│  │   │  Name *                                                     │     │  │
│  │   └─────────────────────────────────────────────────────────────┘     │  │
│  │                                                                       │  │
│  │   ┌─────────────────────────────────────────────────────────────┐     │  │
│  │   │  E-Mail *                                                   │     │  │
│  │   └─────────────────────────────────────────────────────────────┘     │  │
│  │                                                                       │  │
│  │   ┌─────────────────────────────────────────────────────────────┐     │  │
│  │   │  Telefon                                                    │     │  │
│  │   └─────────────────────────────────────────────────────────────┘     │  │
│  │                                                                       │  │
│  │   ☑ Ich stimme der Datenschutzerklärung zu *                          │  │
│  │                                                                       │  │
│  │   ☐ Ich möchte den Newsletter erhalten                                │  │
│  │                                                                       │  │
│  │   [reCAPTCHA Badge]                                                   │  │
│  │                                                                       │  │
│  │              [Button: Ergebnis anfordern]                             │  │
│  │                                                                       │  │
│  └───────────────────────────────────────────────────────────────────────┘  │
│                                                                             │
│  Pflichtfelder: Name, E-Mail, Datenschutz                                   │
│  Optionale Felder: Telefon, Newsletter (ohne "(optional)" Label)            │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│  PHASE 5: LEAD VERVOLLSTÄNDIGUNG                                            │
│                                                                             │
│  • API-Call: POST /irp/v1/leads/complete                                    │
│  • reCAPTCHA Token wird validiert                                           │
│  • Kontaktdaten werden zum Lead hinzugefügt                                 │
│  • Status: "complete"                                                       │
│  • E-Mail an Makler wird versendet                                          │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│  PHASE 6: ERGEBNIS-ANZEIGE                                                  │
│                                                                             │
│  ┌───────────────────────────────────────────────────────────────────────┐  │
│  │                                                                       │  │
│  │   [Berechnungsergebnis wie bisher]                                    │  │
│  │                                                                       │  │
│  │   ───────────────────────────────────────────────────────────────     │  │
│  │                                                                       │  │
│  │   ℹ️ Ein Makler prüft Ihre Berechnung und sendet Ihnen                │  │
│  │      in Kürze eine detaillierte Auswertung per E-Mail.                │  │
│  │                                                                       │  │
│  └───────────────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Datenbank-Änderungen

### Neue Felder in `wp_irp_leads`

| Feld | Typ | Beschreibung |
|------|-----|--------------|
| `status` | ENUM('partial', 'complete') | Lead-Status |
| `newsletter_consent` | TINYINT(1) | Newsletter-Anmeldung |
| `recaptcha_score` | DECIMAL(3,2) | reCAPTCHA Score (0.0 - 1.0) |
| `ip_address` | VARCHAR(45) | IP-Adresse für Spam-Schutz |
| `completed_at` | DATETIME | Zeitpunkt der Vervollständigung |

### SQL Migration

```sql
ALTER TABLE wp_irp_leads
ADD COLUMN status ENUM('partial', 'complete') DEFAULT 'partial' AFTER source,
ADD COLUMN newsletter_consent TINYINT(1) DEFAULT 0 AFTER consent,
ADD COLUMN recaptcha_score DECIMAL(3,2) NULL AFTER newsletter_consent,
ADD COLUMN ip_address VARCHAR(45) NULL AFTER recaptcha_score,
ADD COLUMN completed_at DATETIME NULL AFTER created_at,
ADD INDEX idx_status (status);
```

---

## REST API Endpoints

### 1. Partial Lead erstellen

**Endpoint:** `POST /irp/v1/leads/partial`

**Request:**
```json
{
  "mode": "rental",
  "property_type": "apartment",
  "property_size": 75,
  "property_location": "München",
  "city_id": "muenchen",
  "condition": "renovated",
  "location_rating": 4,
  "features": ["balcony", "elevator"],
  "year_built": 2010,
  "calculation_data": {
    "monthly_rent": 1250,
    "price_per_sqm": 16.67,
    "factors": { ... }
  }
}
```

**Response:**
```json
{
  "success": true,
  "lead_id": 123,
  "message": "Partial lead created"
}
```

### 2. Lead vervollständigen

**Endpoint:** `POST /irp/v1/leads/complete`

**Request:**
```json
{
  "lead_id": 123,
  "name": "Max Mustermann",
  "email": "max@example.com",
  "phone": "0171 1234567",
  "consent": true,
  "newsletter_consent": false,
  "recaptcha_token": "03AGdBq24..."
}
```

**Response:**
```json
{
  "success": true,
  "message": "Lead completed",
  "calculation_data": { ... }
}
```

---

## Settings-Erweiterung

### Neue Sektion: "Sicherheit (reCAPTCHA)"

Position: Nach "Google Maps Integration"

| Setting | Typ | Beschreibung |
|---------|-----|--------------|
| `recaptcha_site_key` | Text | reCAPTCHA v3 Site Key |
| `recaptcha_secret_key` | Password | reCAPTCHA v3 Secret Key |
| `recaptcha_threshold` | Number (0.0-1.0) | Mindest-Score für Akzeptanz (Standard: 0.5) |

### Admin UI

```php
<div class="irp-settings-section">
    <h2>Sicherheit (reCAPTCHA)</h2>

    <table class="form-table">
        <tr>
            <th><label for="recaptcha_site_key">reCAPTCHA Site Key</label></th>
            <td>
                <input type="text" id="recaptcha_site_key"
                       name="irp_settings[recaptcha_site_key]"
                       value="..." class="regular-text">
            </td>
        </tr>
        <tr>
            <th><label for="recaptcha_secret_key">reCAPTCHA Secret Key</label></th>
            <td>
                <input type="password" id="recaptcha_secret_key"
                       name="irp_settings[recaptcha_secret_key]"
                       value="..." class="regular-text">
            </td>
        </tr>
        <tr>
            <th><label for="recaptcha_threshold">Mindest-Score</label></th>
            <td>
                <input type="number" id="recaptcha_threshold"
                       name="irp_settings[recaptcha_threshold]"
                       value="0.5" min="0" max="1" step="0.1" class="small-text">
                <p class="description">0.0 = Bot, 1.0 = Mensch (Standard: 0.5)</p>
            </td>
        </tr>
    </table>

    <div class="irp-info-box">
        <h4>So erhalten Sie reCAPTCHA Keys:</h4>
        <ol>
            <li>Google reCAPTCHA Admin Console öffnen</li>
            <li>"v3 Admin Console" auswählen</li>
            <li>Neue Website registrieren (Typ: reCAPTCHA v3)</li>
            <li>Domain hinzufügen</li>
            <li>Keys kopieren und hier einfügen</li>
        </ol>
        <p><a href="https://www.google.com/recaptcha/admin" target="_blank">
            google.com/recaptcha/admin
        </a></p>
    </div>
</div>
```

---

## Frontend-Komponenten

### 1. Neue Komponente: `CalculationPendingStep.js`

Zeigt Loader-Animation während Berechnung gespeichert wird.

```jsx
export default function CalculationPendingStep({ onComplete }) {
    useEffect(() => {
        // Mindestens 2 Sekunden anzeigen für UX
        const timer = setTimeout(onComplete, 2000);
        return () => clearTimeout(timer);
    }, [onComplete]);

    return (
        <div className="irp-calculation-pending">
            <div className="irp-loader-animation">
                <svg>...</svg>
            </div>
            <h3>Ihre Berechnung wird erstellt...</h3>
            <p>Bitte warten Sie einen Moment.</p>
        </div>
    );
}
```

### 2. Neue Komponente: `ContactFormStep.js`

Kontaktformular nach der Berechnung.

```jsx
export default function ContactFormStep({ leadId, onComplete }) {
    const [formData, setFormData] = useState({
        name: '',
        email: '',
        phone: '',
        consent: false,
        newsletter_consent: false
    });

    // reCAPTCHA v3 laden
    useEffect(() => {
        loadRecaptchaScript();
    }, []);

    const handleSubmit = async () => {
        const recaptchaToken = await grecaptcha.execute(siteKey, {action: 'submit'});

        const response = await apiFetch({
            path: '/irp/v1/leads/complete',
            method: 'POST',
            data: { ...formData, lead_id: leadId, recaptcha_token: recaptchaToken }
        });

        if (response.success) {
            onComplete(response.calculation_data);
        }
    };

    return (
        <div className="irp-contact-form-step">
            <div className="irp-success-header">
                <svg>✓</svg>
                <h3>Ihre Berechnung ist fertig!</h3>
                <p>Bitte geben Sie uns noch ein paar Informationen...</p>
            </div>

            <form onSubmit={handleSubmit}>
                <div className="irp-form-group">
                    <label>Name <span className="irp-required">*</span></label>
                    <input type="text" required ... />
                </div>

                <div className="irp-form-group">
                    <label>E-Mail <span className="irp-required">*</span></label>
                    <input type="email" required ... />
                </div>

                <div className="irp-form-group">
                    <label>Telefon</label>
                    <input type="tel" ... />
                </div>

                <div className="irp-checkbox-group">
                    <label>
                        <input type="checkbox" required ... />
                        Ich stimme der <a href="...">Datenschutzerklärung</a> zu *
                    </label>
                </div>

                <div className="irp-checkbox-group">
                    <label>
                        <input type="checkbox" ... />
                        Ich möchte den Newsletter erhalten
                    </label>
                </div>

                <button type="submit" className="irp-btn irp-btn-primary">
                    Ergebnis anfordern
                </button>
            </form>

            {/* reCAPTCHA Badge */}
            <div className="irp-recaptcha-notice">
                Diese Seite ist durch reCAPTCHA geschützt.
            </div>
        </div>
    );
}
```

### 3. Angepasste `ResultsDisplay.js`

Zusätzlicher Hinweis-Banner:

```jsx
<div className="irp-broker-notice">
    <div className="irp-notice-icon">ℹ️</div>
    <div className="irp-notice-content">
        <strong>Persönliche Beratung</strong>
        <p>
            Ein Makler prüft Ihre Berechnung und sendet Ihnen
            in Kürze eine detaillierte Auswertung per E-Mail.
        </p>
    </div>
</div>
```

---

## App.js Flow-Anpassung

### Neue Steps

```javascript
const STEPS = {
    MODE_SELECT: 'mode_select',
    CALCULATOR: 'calculator',
    CALCULATION_PENDING: 'calculation_pending',  // NEU
    CONTACT_FORM: 'contact_form',                // NEU
    RESULTS: 'results',
    // LEAD_FORM entfernt (ersetzt durch CONTACT_FORM)
    // THANK_YOU entfernt (in RESULTS integriert)
};
```

### Neuer Flow

```javascript
// Nach CALCULATOR step:
const handleCalculationComplete = async (data, calculationResults) => {
    setFormData(data);
    setResults(calculationResults);

    // Partial Lead erstellen
    const response = await apiFetch({
        path: '/irp/v1/leads/partial',
        method: 'POST',
        data: { ...data, calculation_data: calculationResults }
    });

    setLeadId(response.lead_id);
    setCurrentStep(STEPS.CALCULATION_PENDING);
};

// Nach CALCULATION_PENDING:
const handlePendingComplete = () => {
    setCurrentStep(STEPS.CONTACT_FORM);
};

// Nach CONTACT_FORM:
const handleContactComplete = (calculationData) => {
    setResults(calculationData);
    setCurrentStep(STEPS.RESULTS);
};
```

---

## E-Mail an Makler

### Template

```
Betreff: [Neuer Lead] Mietwert-Berechnung - München

──────────────────────────────────────────────

NEUER LEAD EINGEGANGEN

──────────────────────────────────────────────

KONTAKTDATEN:
• Name: Max Mustermann
• E-Mail: max@example.com
• Telefon: 0171 1234567
• Newsletter: Ja / Nein

──────────────────────────────────────────────

IMMOBILIENDATEN:
• Typ: Wohnung
• Größe: 75 m²
• Standort: München
• Zustand: Renoviert
• Lage: Sehr gut (4/5)
• Ausstattung: Balkon, Aufzug

──────────────────────────────────────────────

BERECHNUNGSERGEBNIS:
• Geschätzte Monatsmiete: 1.250 €
• Preis pro m²: 16,67 €

──────────────────────────────────────────────

→ Lead im Admin-Bereich ansehen:
  [Link zum Lead]

──────────────────────────────────────────────
```

---

## Implementierungs-Phasen

### Phase 1: Backend (PHP)

| # | Aufgabe | Datei |
|---|---------|-------|
| 1.1 | Datenbank-Migration (neue Felder) | `class-activator.php` |
| 1.2 | Settings: reCAPTCHA Sektion | `admin/views/settings.php` |
| 1.3 | Settings: Sanitize für reCAPTCHA | `admin/class-admin.php` |
| 1.4 | reCAPTCHA Validierung Klasse | `includes/class-recaptcha.php` (NEU) |
| 1.5 | REST API: `/leads/partial` Endpoint | `includes/class-rest-api.php` |
| 1.6 | REST API: `/leads/complete` Endpoint | `includes/class-rest-api.php` |
| 1.7 | Leads: Status-Handling | `includes/class-leads.php` |
| 1.8 | Leads: E-Mail Template anpassen | `includes/class-leads.php` |

### Phase 2: Frontend (React)

| # | Aufgabe | Datei |
|---|---------|-------|
| 2.1 | Komponente: CalculationPendingStep | `src/components/CalculationPendingStep.js` (NEU) |
| 2.2 | Komponente: ContactFormStep | `src/components/ContactFormStep.js` (NEU) |
| 2.3 | App.js: Neuer Step-Flow | `src/components/App.js` |
| 2.4 | ResultsDisplay: Makler-Hinweis | `src/components/ResultsDisplay.js` |
| 2.5 | reCAPTCHA Script Loading | `src/components/ContactFormStep.js` |
| 2.6 | SCSS: Neue Komponenten stylen | `src/styles/main.scss` |

### Phase 3: Admin & Testing

| # | Aufgabe | Datei |
|---|---------|-------|
| 3.1 | Leads-Liste: Status-Filter | `admin/views/leads-list.php` |
| 3.2 | Leads-Liste: Status-Badge | `admin/css/admin.css` |
| 3.3 | Testing & Bugfixes | - |
| 3.4 | Dokumentation (README) | `README.md` |

---

## Dateien - Übersicht

### Neue Dateien

```
includes/
└── class-recaptcha.php          # reCAPTCHA v3 Validierung

src/components/
├── CalculationPendingStep.js    # Loader nach Berechnung
└── ContactFormStep.js           # Kontaktformular
```

### Zu ändernde Dateien

```
includes/
├── class-activator.php          # DB Migration
├── class-rest-api.php           # Neue Endpoints
└── class-leads.php              # Status-Handling, E-Mail

admin/
├── class-admin.php              # Settings Sanitize
├── views/settings.php           # reCAPTCHA Settings UI
├── views/leads-list.php         # Status-Filter
└── css/admin.css                # Status-Badges

src/
├── components/App.js            # Neuer Flow
├── components/ResultsDisplay.js # Makler-Hinweis
└── styles/main.scss             # Neue Styles
```

---

## Technische Details

### reCAPTCHA v3 Integration

**Frontend (Script laden):**
```javascript
const loadRecaptcha = (siteKey) => {
    const script = document.createElement('script');
    script.src = `https://www.google.com/recaptcha/api.js?render=${siteKey}`;
    document.head.appendChild(script);
};
```

**Frontend (Token holen):**
```javascript
const getRecaptchaToken = async (siteKey, action) => {
    return new Promise((resolve) => {
        grecaptcha.ready(() => {
            grecaptcha.execute(siteKey, { action }).then(resolve);
        });
    });
};
```

**Backend (Token validieren):**
```php
class IRP_Recaptcha {
    public function verify(string $token): array {
        $secret = get_option('irp_settings')['recaptcha_secret_key'] ?? '';
        $threshold = get_option('irp_settings')['recaptcha_threshold'] ?? 0.5;

        $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
            'body' => [
                'secret' => $secret,
                'response' => $token,
                'remoteip' => $_SERVER['REMOTE_ADDR']
            ]
        ]);

        $result = json_decode(wp_remote_retrieve_body($response), true);

        return [
            'success' => $result['success'] && $result['score'] >= $threshold,
            'score' => $result['score'] ?? 0,
            'action' => $result['action'] ?? ''
        ];
    }
}
```

---

## Risiken & Fallbacks

| Risiko | Fallback |
|--------|----------|
| reCAPTCHA nicht konfiguriert | Formular funktioniert ohne reCAPTCHA |
| reCAPTCHA Service nicht erreichbar | Formular wird trotzdem akzeptiert (mit Log) |
| Partial Lead wird nie vervollständigt | Cleanup-Job nach 24h (optional) |
| E-Mail-Versand fehlgeschlagen | Fehler loggen, Lead trotzdem speichern |

---

## Abnahmekriterien

- [x] Wizard-Flow funktioniert wie bisher bis "Ausstattung"
- [x] "Berechnen" Button speichert Partial Lead
- [x] Loader-Animation wird 2-3 Sekunden angezeigt
- [x] Kontaktformular zeigt alle Felder korrekt an
- [x] Pflichtfelder sind mit * markiert
- [x] reCAPTCHA Badge ist sichtbar (wenn konfiguriert)
- [x] Formular-Validierung funktioniert
- [x] Lead wird vervollständigt nach Absenden
- [x] E-Mail an Makler wird versendet
- [x] Ergebnis-Seite zeigt Berechnung + Makler-Hinweis
- [x] Leads-Liste zeigt Status (partial/complete)
- [x] reCAPTCHA Settings in Admin funktionieren

## Implementierung abgeschlossen

**Datum:** 2026-01-09

Alle Phasen wurden erfolgreich implementiert:
- Phase 1: Backend (PHP) ✓
- Phase 2: Frontend (React) ✓
- Phase 3: Admin & Testing ✓
