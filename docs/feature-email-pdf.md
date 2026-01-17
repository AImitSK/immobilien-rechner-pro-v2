# Feature: E-Mail mit PDF-Auswertung

## Ziel

Nach Abschluss der Berechnung erhält der Lead automatisch eine E-Mail mit:
1. Personalisierter Nachricht
2. PDF-Anhang mit vollständiger Auswertung im Briefpapier-Stil

**Wichtig:** Keine Formeln oder Berechnungslogik im PDF preisgeben.

---

## Ablauf

```
┌─────────────────────────────────────────────────────────────┐
│  Berechnung abgeschlossen + Kontaktdaten erfasst            │
└─────────────────────┬───────────────────────────────────────┘
                      │
          ┌───────────┴───────────┐
          │                       │
          ▼                       ▼
┌─────────────────────┐   ┌─────────────────────────────────┐
│  Visuelle Anzeige   │   │  Nach Response:                 │
│  im Browser         │   │  1. PDF generieren              │
│  (sofort)           │   │  2. E-Mail senden               │
└─────────────────────┘   └─────────────────────────────────┘
```

E-Mail wird via `register_shutdown_function` nach der Response gesendet → User wartet nicht, E-Mail geht trotzdem sofort raus.

---

## Admin-Bereich Struktur

### Einstellungen aufgeteilt in Tabs

```
Einstellungen
├── Tab: Allgemein (Farben, Defaults, Max-Width)
├── Tab: Branding & Kontakt (NEU)
├── Tab: E-Mail (NEU)
├── Tab: reCAPTCHA
└── Tab: Google Maps
```

---

## Tab: Branding & Kontakt

```
┌─────────────────────────────────────────────────────────────┐
│ Branding & Kontakt                                          │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Logo:                                                      │
│  [Bild auswählen]  [Vorschau]  [Entfernen]                  │
│                                                             │
│  Logo-Breite im PDF:                                        │
│  [150        ] px  (max. 300px)                             │
│                                                             │
│  ─────────────────────────────────────────────────────────  │
│                                                             │
│  Firmenname:                                                │
│  [Maklerkontor Brand & Co.                      ]           │
│                                                             │
│  Firmenname Zeile 2 (optional):                             │
│  [Immobilienmakler GmbH & Co. KG                ]           │
│                                                             │
│  Firmenname Zeile 3 (optional):                             │
│  [Brand & Co. Bauträgergesellschaft mbH         ]           │
│                                                             │
│  ─────────────────────────────────────────────────────────  │
│                                                             │
│  Straße:                                                    │
│  [Morsbachallee 8-10                            ]           │
│                                                             │
│  PLZ:                  Ort:                                 │
│  [32545       ]        [Bad Oeynhausen           ]          │
│                                                             │
│  ─────────────────────────────────────────────────────────  │
│                                                             │
│  Telefon:                                                   │
│  [+49 5731 177550                               ]           │
│                                                             │
│  E-Mail:                                                    │
│  [immobilien@brand-partner.de                   ]           │
│                                                             │
│  [Änderungen speichern]                                     │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

## Tab: E-Mail

```
┌─────────────────────────────────────────────────────────────┐
│ E-Mail Einstellungen                                        │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ☑ E-Mail mit Auswertung automatisch versenden              │
│                                                             │
│  ─────────────────────────────────────────────────────────  │
│                                                             │
│  Absender-Name (optional):                                  │
│  [                                              ]           │
│  Wenn leer, wird der Firmenname verwendet.                  │
│                                                             │
│  Absender-E-Mail (optional):                                │
│  [                                              ]           │
│  Wenn leer, wird die Firmen-E-Mail verwendet.               │
│                                                             │
│  ─────────────────────────────────────────────────────────  │
│                                                             │
│  Betreff:                                                   │
│  [Ihre Immobilienbewertung - {property_type} in {city}    ] │
│                                                             │
│  Verfügbare Variablen: {name}, {city}, {property_type},     │
│  {size}, {result_value}                                     │
│                                                             │
│  ─────────────────────────────────────────────────────────  │
│                                                             │
│  E-Mail Text:                                               │
│  ┌─────────────────────────────────────────────────────┐    │
│  │ Guten Tag {name},                                   │    │
│  │                                                     │    │
│  │ vielen Dank für Ihr Interesse an unserer           │    │
│  │ Immobilienbewertung.                               │    │
│  │                                                     │    │
│  │ Anbei erhalten Sie Ihre persönliche Auswertung     │    │
│  │ als PDF-Dokument.                                  │    │
│  │                                                     │    │
│  │ Für Fragen stehen wir Ihnen gerne zur Verfügung.   │    │
│  │                                                     │    │
│  │ Mit freundlichen Grüßen                            │    │
│  └─────────────────────────────────────────────────────┘    │
│  (WYSIWYG-Editor)                                           │
│                                                             │
│  Hinweis: Die Signatur wird automatisch aus den             │
│  Branding-Einstellungen generiert.                          │
│                                                             │
│  ─────────────────────────────────────────────────────────  │
│                                                             │
│  [Test-E-Mail senden an: [admin@example.de    ] [Senden]]   │
│                                                             │
│  [Änderungen speichern]                                     │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

## E-Mail Template

### HTML-Struktur

```html
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">

    <!-- Header mit Logo -->
    <div style="text-align: center; padding: 20px 0; border-bottom: 2px solid #2563eb;">
        <img src="{logo_url}" alt="{company_name}" style="max-height: 60px;">
    </div>

    <!-- Hauptinhalt -->
    <div style="padding: 30px 20px; line-height: 1.6;">
        {email_content}
    </div>

    <!-- Signatur -->
    <div style="padding: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 14px;">
        <p style="margin: 0 0 5px;"><strong>{company_name}</strong></p>
        <p style="margin: 0 0 5px;">{company_name_2}</p>
        <p style="margin: 0 0 10px;">{company_name_3}</p>
        <p style="margin: 0 0 5px;">{company_street}</p>
        <p style="margin: 0 0 10px;">{company_zip} {company_city}</p>
        <p style="margin: 0;">Tel.: {company_phone}<br>
        E-Mail: {company_email}</p>
    </div>

</body>
</html>
```

### Verfügbare Variablen im Betreff/Text

| Variable | Beschreibung | Beispiel |
|----------|--------------|----------|
| `{name}` | Name des Leads | Max Mustermann |
| `{city}` | Stadt der Immobilie | Berlin |
| `{property_type}` | Objekttyp | Wohnung |
| `{size}` | Größe in m² | 85 |
| `{result_value}` | Geschätzte Miete | 1.250 €/Monat |

---

## PDF-Dokument (Briefpapier-Stil)

### PDF-Bibliothek: DOMPDF (gebundelt)

DOMPDF wird direkt im Plugin gebundelt (kein Composer nötig):
- HTML/CSS basiert → einfaches Styling
- Unterstützt eingebettete Bilder (Base64)
- DejaVu Sans Font für deutsche Umlaute

### Logo-Einbettung

Logo wird als Base64 direkt eingebettet (keine Remote-Requests):
```php
$logo_path = get_attached_file($logo_id);
$logo_data = base64_encode(file_get_contents($logo_path));
$logo_src = "data:image/png;base64,{$logo_data}";
```

### PDF-Layout

```
┌─────────────────────────────────────────────────────────────┐
│                                                             │
│                        [LOGO]                               │
│                    (zentriert, einstellbare Breite)         │
│                                                             │
│                                        Datum: 10.01.2026    │
│                                                             │
│  ═══════════════════════════════════════════════════════   │
│                                                             │
│              IHRE IMMOBILIENBEWERTUNG                       │
│                                                             │
│  ═══════════════════════════════════════════════════════   │
│                                                             │
│                                                             │
│  OBJEKTDATEN                                                │
│  ───────────────────────────────────────────────────────   │
│                                                             │
│  Objekttyp              Wohnung                             │
│  Wohnfläche             85 m²                               │
│  Standort               Berlin                              │
│  Zustand                Gut                                 │
│  Lagekategorie          ★★★★☆ Sehr gute Lage               │
│                                                             │
│                                                             │
│  AUSSTATTUNGSMERKMALE                                       │
│  ───────────────────────────────────────────────────────   │
│                                                             │
│  ✓ Balkon          ✓ Einbauküche       ✓ Aufzug            │
│  ✓ Keller          ✓ Gäste-WC                              │
│                                                             │
│                                                             │
│  ═══════════════════════════════════════════════════════   │
│                                                             │
│  BEWERTUNGSERGEBNIS                                         │
│                                                             │
│  ┌─────────────────────────────────────────────────────┐   │
│  │                                                     │   │
│  │   Geschätzte Monatsmiete                            │   │
│  │                                                     │   │
│  │   ┌─────────────────────────────────────────────┐   │   │
│  │   │                                             │   │   │
│  │   │         1.180 € – 1.320 €                   │   │   │
│  │   │                                             │   │   │
│  │   │      Empfohlener Mietpreis: 1.250 €         │   │   │
│  │   │                                             │   │   │
│  │   └─────────────────────────────────────────────┘   │   │
│  │                                                     │   │
│  │   Mietpreis pro m²: 14,70 €                         │   │
│  │                                                     │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                             │
│                                                             │
│  MARKTEINORDNUNG                                            │
│  ───────────────────────────────────────────────────────   │
│                                                             │
│  ▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓░░░░░░░                                  │
│  |         |         |         |                            │
│  günstig   Markt   gehoben   Premium                        │
│                       ▲                                     │
│                  Ihr Objekt                                 │
│                                                             │
│  ┌─────────────────────────────────────────────────────┐   │
│  │ Diese Einschätzung beruht auf Ihren Angaben und     │   │
│  │ sollte gemeinsam mit einem Immobilienexperten von   │   │
│  │ {company_name} überprüft werden.                    │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                             │
│                                                             │
│  ───────────────────────────────────────────────────────   │
│                                                             │
│  HINWEIS                                                    │
│                                                             │
│  Diese Bewertung basiert auf aktuellen Marktdaten und      │
│  dient als erste Orientierung. Für eine verbindliche       │
│  Bewertung kontaktieren Sie uns gerne persönlich.          │
│                                                             │
│                                                             │
│  ═══════════════════════════════════════════════════════   │
│                                                             │
│         Maklerkontor Brand & Co. · Immobilienmakler         │
│    Morsbachallee 8-10, 32545 Bad Oeynhausen                 │
│    Tel.: +49 5731 177550 · immobilien@brand-partner.de      │
│                                                             │
│                      (zentriert, klein)                     │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### Markteinordnung

Die Position basiert auf der Lage-Bewertung (1-5):
- Lage 1-2 → günstig
- Lage 3 → Markt
- Lage 4 → gehoben
- Lage 5 → Premium

**Disclaimer im PDF:**
> Diese Einschätzung beruht auf Ihren Angaben und sollte gemeinsam mit einem Immobilienexperten von {company_name} überprüft werden.

`{company_name}` wird durch den Firmennamen aus den Branding-Einstellungen ersetzt.

### Was im PDF erscheint

**Ja:**
- Logo (Header, zentriert, einstellbare Breite, Base64 eingebettet)
- Datum der Bewertung
- Objektdaten (Typ, Größe, Stadt, Zustand, Lage)
- Ausstattungsmerkmale (Checkmarks)
- Mietpreisspanne (Min – Max)
- Empfohlener Mietpreis
- Preis pro m²
- Markteinordnung (visuell) + Disclaimer mit {company_name}
- Hinweistext
- Kontaktdaten (Footer, zentriert, 2-3 Zeilen, kleine Schrift)

**Nein:**
- ❌ Berechnungsformeln
- ❌ Multiplikatoren
- ❌ Basispreise
- ❌ Ausstattungszuschläge in Euro
- ❌ Interne Parameter

---

## Datenbank-Erweiterung

```sql
ALTER TABLE wp_irp_leads
ADD COLUMN email_sent TINYINT(1) DEFAULT 0,
ADD COLUMN email_sent_at DATETIME DEFAULT NULL;
```

### Lead-Liste Anpassung

Neue Spalte "E-Mail":

| Status | Anzeige |
|--------|---------|
| Gesendet | ✅ + Zeitpunkt |
| Nicht gesendet | — |
| Deaktiviert | — |

---

## Technische Umsetzung

### Neue Dateien

```
includes/
├── class-email.php              # E-Mail Versand
├── class-pdf-generator.php      # PDF Erstellung
└── templates/
    ├── email.php                # E-Mail HTML Template
    └── pdf.php                  # PDF HTML Template

vendor/
└── dompdf/                      # DOMPDF gebundelt (ohne Composer)
```

### Klasse: IRP_Email

```php
<?php
class IRP_Email {

    public static function is_enabled(): bool {
        $settings = get_option('irp_email_settings', []);
        return !empty($settings['enabled']);
    }

    /**
     * Plant E-Mail-Versand nach Response
     */
    public static function schedule_after_response(int $lead_id): void {
        if (!self::is_enabled()) {
            return;
        }

        // Nach Response ausführen (User wartet nicht)
        register_shutdown_function([self::class, 'send_result_email'], $lead_id);
    }

    /**
     * Sendet E-Mail mit PDF-Anhang
     */
    public static function send_result_email(int $lead_id): bool {
        $leads = new IRP_Leads();
        $lead = $leads->get($lead_id);

        if (!$lead) {
            return false;
        }

        $lead_data = [
            'name' => $lead->name,
            'email' => $lead->email,
            'calculation_data' => json_decode($lead->calculation_data, true),
        ];

        // PDF generieren
        $pdf_path = IRP_PDF_Generator::create($lead_data);
        if (!$pdf_path) {
            return false;
        }

        // E-Mail zusammenbauen
        $to = $lead_data['email'];
        $subject = self::parse_template(self::get_subject(), $lead_data);
        $body = self::build_email_body($lead_data);
        $headers = self::get_headers();
        $attachments = [$pdf_path];

        // Senden
        $sent = wp_mail($to, $subject, $body, $headers, $attachments);

        // PDF aufräumen
        @unlink($pdf_path);

        // Status in DB speichern
        if ($sent) {
            global $wpdb;
            $wpdb->update(
                $wpdb->prefix . 'irp_leads',
                [
                    'email_sent' => 1,
                    'email_sent_at' => current_time('mysql'),
                ],
                ['id' => $lead_id]
            );
        }

        return $sent;
    }

    private static function get_headers(): array {
        $settings = get_option('irp_email_settings', []);
        $branding = get_option('irp_settings', []);

        $from_name = $settings['sender_name'] ?: ($branding['company_name'] ?? '');
        $from_email = $settings['sender_email'] ?: ($branding['company_email'] ?? '');

        return [
            'Content-Type: text/html; charset=UTF-8',
            "From: {$from_name} <{$from_email}>",
        ];
    }

    private static function get_subject(): string {
        $settings = get_option('irp_email_settings', []);
        return $settings['subject'] ?? 'Ihre Immobilienbewertung - {property_type} in {city}';
    }

    private static function build_email_body(array $data): string {
        $settings = get_option('irp_email_settings', []);
        $branding = get_option('irp_settings', []);

        $content = $settings['email_content'] ?? self::get_default_content();
        $content = self::parse_template($content, $data);
        $content = nl2br($content);

        ob_start();
        include IRP_PLUGIN_DIR . 'includes/templates/email.php';
        return ob_get_clean();
    }

    private static function parse_template(string $template, array $data): string {
        $calc = $data['calculation_data'] ?? [];
        $result = $calc['result'] ?? [];

        $replacements = [
            '{name}' => $data['name'] ?? '',
            '{city}' => $calc['city_name'] ?? '',
            '{property_type}' => self::translate_type($calc['property_type'] ?? ''),
            '{size}' => $calc['size'] ?? '',
            '{result_value}' => self::format_rent($result),
        ];

        return strtr($template, $replacements);
    }

    private static function translate_type(string $type): string {
        $types = [
            'apartment' => 'Wohnung',
            'house' => 'Haus',
            'commercial' => 'Gewerbe',
        ];
        return $types[$type] ?? $type;
    }

    private static function format_rent(array $result): string {
        $rent = $result['monthly_rent']['estimate'] ?? 0;
        return number_format($rent, 0, ',', '.') . ' €/Monat';
    }

    private static function get_default_content(): string {
        return "Guten Tag {name},\n\n" .
               "vielen Dank für Ihr Interesse an unserer Immobilienbewertung.\n\n" .
               "Anbei erhalten Sie Ihre persönliche Auswertung als PDF-Dokument.\n\n" .
               "Für Fragen stehen wir Ihnen gerne zur Verfügung.\n\n" .
               "Mit freundlichen Grüßen";
    }
}
```

### Klasse: IRP_PDF_Generator

```php
<?php
class IRP_PDF_Generator {

    public static function create(array $lead_data): string|false {
        require_once IRP_PLUGIN_DIR . 'vendor/dompdf/autoload.inc.php';

        $html = self::build_html($lead_data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', false);  // Keine Remote-Bilder
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Temporäre Datei
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/irp-temp/';
        wp_mkdir_p($temp_dir);

        $filename = 'immobilienbewertung-' . uniqid() . '.pdf';
        $filepath = $temp_dir . $filename;

        file_put_contents($filepath, $dompdf->output());

        return $filepath;
    }

    private static function build_html(array $data): string {
        $branding = get_option('irp_settings', []);
        $calc = $data['calculation_data'] ?? [];
        $result = $calc['result'] ?? [];

        // Logo als Base64
        $logo_base64 = '';
        if (!empty($branding['company_logo'])) {
            $logo_id = attachment_url_to_postid($branding['company_logo']);
            if ($logo_id) {
                $logo_path = get_attached_file($logo_id);
                if ($logo_path && file_exists($logo_path)) {
                    $logo_data = base64_encode(file_get_contents($logo_path));
                    $mime = mime_content_type($logo_path);
                    $logo_base64 = "data:{$mime};base64,{$logo_data}";
                }
            }
        }

        ob_start();
        include IRP_PLUGIN_DIR . 'includes/templates/pdf.php';
        return ob_get_clean();
    }

    /**
     * Übersetzt Lage-Rating in Marktposition
     */
    public static function get_market_position(int $rating): array {
        $positions = [
            1 => ['position' => 15, 'label' => 'günstig'],
            2 => ['position' => 30, 'label' => 'günstig'],
            3 => ['position' => 50, 'label' => 'Markt'],
            4 => ['position' => 70, 'label' => 'gehoben'],
            5 => ['position' => 85, 'label' => 'Premium'],
        ];
        return $positions[$rating] ?? $positions[3];
    }
}
```

---

## Hook Integration

```php
// In class-rest-api.php bei Lead-Completion

add_action('irp_lead_completed', function($lead_id, $lead_data) {
    // E-Mail nach Response senden
    IRP_Email::schedule_after_response($lead_id);
}, 10, 2);
```

---

## Einstellungen

### irp_email_settings
```php
[
    'enabled' => true,
    'sender_name' => '',           // Fallback: company_name
    'sender_email' => '',          // Fallback: company_email
    'subject' => 'Ihre Immobilienbewertung - {property_type} in {city}',
    'email_content' => '...',      // WYSIWYG Content
]
```

### irp_settings (erweitert)
```php
[
    // Bestehende Felder...
    'company_logo' => 'https://...',
    'company_logo_width' => 150,              // Logo-Breite in px (max 300)
    'company_name' => 'Maklerkontor Brand & Co.',
    'company_name_2' => 'Immobilienmakler GmbH & Co. KG',
    'company_name_3' => 'Brand & Co. Bauträgergesellschaft mbH',
    'company_street' => 'Morsbachallee 8-10',
    'company_zip' => '32545',
    'company_city' => 'Bad Oeynhausen',
    'company_phone' => '+49 5731 177550',
    'company_email' => 'immobilien@brand-partner.de',
]
```

---

## Checkliste

### Phase 1: Grundlagen
- [ ] Branding-Einstellungen erweitern (Adresse, Telefon, mehrzeilige Firma, Logo-Breite)
- [ ] Settings-Seite in Tabs aufteilen
- [ ] Neuer Tab "Branding & Kontakt"
- [ ] Neuer Tab "E-Mail"

### Phase 2: E-Mail
- [ ] `class-email.php` erstellen
- [ ] E-Mail HTML Template (`templates/email.php`)
- [ ] WYSIWYG-Editor für E-Mail-Text einbinden
- [ ] Variablen-Ersetzung implementieren
- [ ] Test-E-Mail Funktion

### Phase 3: PDF
- [ ] DOMPDF in `/vendor/` bundeln
- [ ] `class-pdf-generator.php` erstellen
- [ ] PDF HTML Template (`templates/pdf.php`)
- [ ] Logo als Base64 einbetten
- [ ] Markteinordnung mit Disclaimer

### Phase 4: Integration
- [ ] DB-Migration (email_sent, email_sent_at)
- [ ] Hook bei Lead-Completion (`register_shutdown_function`)
- [ ] E-Mail-Status in Lead-Liste anzeigen
- [ ] Testen mit echten Daten
