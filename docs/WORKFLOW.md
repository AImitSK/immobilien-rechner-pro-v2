# Workflow: Refactoring-Phasen Abarbeitung

**Version:** 1.0
**Erstellt:** 2026-01-18
**Bezieht sich auf:** [REFACTORING-PLAN.md](./REFACTORING-PLAN.md)

---

## Übersicht

Dieses Dokument definiert den strukturierten Workflow für die Abarbeitung des Refactoring-Plans. Jede Phase wird in einem kontrollierten Prozess durchgeführt, bei dem der Benutzer die volle Kontrolle behält.

### Verfügbare Spezial-Agenten

| Agent | Zweck | Aufruf |
|-------|-------|--------|
| 🧪 **test-runner** | Tests ausführen, Build prüfen | "Führe Tests aus" |
| 🌐 **i18n-specialist** | Übersetzungen, POT generieren | "Prüfe die Übersetzungen" |
| 🔢 **calculator-validator** | Parameter-Vollständigkeit prüfen | "Validiere die Berechnungen" |
| 🎨 **icon-manager** | Icon-Struktur, Migration | "Prüfe die Icons" |
| 🔒 **security-checker** | Sicherheitslücken finden | "Security-Check durchführen" |
| 📋 **review-specialist** | Vollständiger Code-Review | "Reviewe den Code" |

```
┌─────────────────────────────────────────────────────────────────┐
│                     WORKFLOW PRO PHASE                          │
├─────────────────────────────────────────────────────────────────┤
│  1. VORBEREITUNG    →  Plan lesen & verstehen                   │
│  2. PLANUNG         →  Aufgaben identifizieren & priorisieren   │
│  3. UMSETZUNG       →  Code schreiben / ändern                  │
│  4. TESTS           →  Funktionalität prüfen                    │
│  5. REVIEW          →  Code-Qualität sicherstellen              │
│  6. DOKUMENTATION   →  Änderungen dokumentieren                 │
│  7. ABSCHLUSS       →  Zusammenfassung & Freigabe               │
│                                                                 │
│  ══════════════════════════════════════════════════════════════ │
│  🔒 WARTEN AUF BENUTZER-FREIGABE FÜR NÄCHSTE PHASE             │
└─────────────────────────────────────────────────────────────────┘
```

---

## Phasen-Workflow im Detail

### Schritt 1: VORBEREITUNG

**Ziel:** Phase verstehen und Kontext aufbauen

**Aktionen:**
- [ ] REFACTORING-PLAN.md lesen (relevante Phase)
- [ ] Betroffene Dateien identifizieren
- [ ] Abhängigkeiten zu anderen Phasen prüfen
- [ ] Offene Fragen an Benutzer klären

**Ausgabe an Benutzer:**
```
📋 PHASE [X]: [Name der Phase]
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Ziel: [Beschreibung]

Betroffene Dateien:
- datei1.php
- datei2.js
- ...

Geplante Änderungen:
1. [Änderung 1]
2. [Änderung 2]
...

Geschätzte Aufgaben: [X]

❓ Offene Fragen: [Falls vorhanden]

Bereit zum Start? (ja/nein)
```

**Checkpoint:** ✋ Warten auf Benutzer-Bestätigung

---

### Schritt 2: PLANUNG

**Ziel:** Detaillierte Aufgabenliste erstellen

**Aktionen:**
- [ ] Aufgaben aus dem Plan in Todo-Liste übertragen
- [ ] Reihenfolge festlegen (Abhängigkeiten beachten)
- [ ] Komplexe Aufgaben in Teilschritte zerlegen

**Ausgabe an Benutzer:**
```
📝 AUFGABENLISTE PHASE [X]
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

[ ] 1. Aufgabe 1 (Datei: xxx.php)
[ ] 2. Aufgabe 2 (Datei: xxx.js)
[ ] 3. Aufgabe 3 (Dateien: mehrere)
...

Soll ich mit Aufgabe 1 beginnen?
```

**Checkpoint:** ✋ Warten auf Benutzer-Bestätigung

---

### Schritt 3: UMSETZUNG

**Ziel:** Code-Änderungen durchführen

**Aktionen (pro Aufgabe):**
- [ ] Aktuelle Datei lesen
- [ ] Änderungen implementieren
- [ ] Änderungen dem Benutzer zeigen

**Ausgabe an Benutzer (nach jeder Aufgabe):**
```
✏️ AUFGABE [X/Y] UMGESETZT
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Datei: [Pfad zur Datei]

Änderung:
[Kurze Beschreibung was geändert wurde]

Geänderte Zeilen: [von-bis]

Status: ✅ Erfolgreich / ⚠️ Mit Hinweisen

Weiter mit nächster Aufgabe? (ja/nein/zeigen)
```

**Hinweis:** Bei komplexen Änderungen wird der Code-Diff gezeigt.

---

### Schritt 4: TESTS

**Ziel:** Sicherstellen, dass Änderungen funktionieren

**Aktionen:**
- [ ] Relevante Tests identifizieren
- [ ] Tests ausführen (falls vorhanden)
- [ ] Manuelle Prüfpunkte durchgehen
- [ ] Fehler beheben (falls nötig)

**Test-Typen:**

| Test-Typ | Wann | Wie |
|----------|------|-----|
| **Syntax-Check** | Nach jeder Datei | `php -l datei.php` |
| **Build** | Nach JS-Änderungen | `npm run build` |
| **Unit-Tests** | Nach Logik-Änderungen | `phpunit` / `npm test` |
| **Manuell** | Nach UI-Änderungen | Browser-Test |

**Ausgabe an Benutzer:**
```
🧪 TEST-ERGEBNISSE
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Syntax-Check:     ✅ Bestanden
Build:            ✅ Bestanden (oder ⏭️ Nicht relevant)
Unit-Tests:       ✅ X bestanden, 0 fehlgeschlagen
Manuelle Tests:   📋 Bitte prüfen: [Checkliste]

Gesamtstatus: ✅ ALLE TESTS BESTANDEN

Weiter zum Review? (ja/nein)
```

**Checkpoint:** ✋ Warten auf Benutzer-Bestätigung bei Fehlern

---

### Schritt 5: REVIEW

**Ziel:** Code-Qualität sicherstellen

**Aktionen:**
- [ ] Coding-Standards prüfen
- [ ] Sicherheitsaspekte prüfen
- [ ] Performance-Auswirkungen bewerten
- [ ] Rückwärtskompatibilität prüfen

**Review-Checkliste:**
```
☑️ REVIEW-CHECKLISTE
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

[x] WordPress Coding Standards eingehalten
[x] Keine Sicherheitslücken eingeführt
[x] Fehlerbehandlung vorhanden
[x] i18n-Funktionen korrekt verwendet
[x] Keine Debug-Code in Production
[x] Performance nicht negativ beeinflusst
[ ] Dokumentation aktualisiert (falls nötig)
```

---

### Schritt 6: DOKUMENTATION

**Ziel:** Änderungen nachvollziehbar dokumentieren

**Aktionen:**
- [ ] CHANGELOG-Eintrag vorbereiten
- [ ] Code-Kommentare prüfen
- [ ] Betroffene Docs aktualisieren
- [ ] Versionsnummer aktualisieren (falls nötig)

**Ausgabe an Benutzer:**
```
📄 DOKUMENTATIONS-UPDATE
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

CHANGELOG-Eintrag:
------------------
### [Version] - [Datum]
#### Phase X: [Name]
- Geändert: [Beschreibung]
- Hinzugefügt: [Beschreibung]
- Behoben: [Beschreibung]

Aktualisierte Dokumentation:
- docs/DATEI.md (Zeile X-Y)

Soll ich diese Änderungen übernehmen? (ja/nein)
```

---

### Schritt 7: ABSCHLUSS

**Ziel:** Phase offiziell abschließen

**Aktionen:**
- [ ] Alle Aufgaben als erledigt markieren
- [ ] Zusammenfassung erstellen
- [ ] Offene Punkte für nächste Phase notieren
- [ ] Auf Freigabe warten

**Ausgabe an Benutzer:**
```
═══════════════════════════════════════════════════════════════════
✅ PHASE [X] ABGESCHLOSSEN: [Name der Phase]
═══════════════════════════════════════════════════════════════════

📊 ZUSAMMENFASSUNG
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Erledigte Aufgaben:    [X/X] (100%)
Geänderte Dateien:     [Anzahl]
Neue Dateien:          [Anzahl]
Gelöschte Dateien:     [Anzahl]

Wichtigste Änderungen:
• [Änderung 1]
• [Änderung 2]
• [Änderung 3]

Tests:                 ✅ Alle bestanden
Review:                ✅ Abgeschlossen
Dokumentation:         ✅ Aktualisiert

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

🔜 NÄCHSTE PHASE: [X+1] - [Name]
   Geplante Aufgaben: [Anzahl]
   Betroffene Dateien: [Anzahl]

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

🔒 Warte auf Ihre Freigabe für Phase [X+1].
   Antworten Sie mit "Phase [X+1] starten" um fortzufahren.

═══════════════════════════════════════════════════════════════════
```

**Checkpoint:** 🔒 WARTEN AUF BENUTZER-FREIGABE

---

## Befehle für die Zusammenarbeit

Der Benutzer kann folgende Befehle verwenden:

| Befehl | Beschreibung |
|--------|--------------|
| `Phase X starten` | Startet die angegebene Phase |
| `weiter` | Fährt mit nächstem Schritt/Aufgabe fort |
| `stopp` | Pausiert die aktuelle Arbeit |
| `status` | Zeigt aktuellen Fortschritt |
| `zeigen` | Zeigt die letzte Änderung im Detail |
| `rückgängig` | Macht letzte Änderung rückgängig |
| `überspringen` | Überspringt aktuelle Aufgabe |
| `zusammenfassung` | Zeigt Zusammenfassung der Phase |
| `hilfe` | Zeigt verfügbare Befehle |

---

## Phasen-Übersicht

| Phase | Name | Aufgaben | Agenten | Status |
|-------|------|----------|---------|--------|
| 1 | Internationalisierung (i18n) | 5 | 🌐 i18n-specialist, 🧪 test-runner | ⏳ Ausstehend |
| 2 | Sicherheits-Refactoring | 5 | 🔒 security-checker, 🧪 test-runner | ⏳ Ausstehend |
| 3 | Einheitliche Fehlerbehandlung | 5 | 🧪 test-runner | ⏳ Ausstehend |
| 4 | Performance & Qualität | 5 | 🔢 calculator-validator, 🧪 test-runner | ⏳ Ausstehend |
| 5 | Icon-Management-System | 11 | 🎨 icon-manager, 🧪 test-runner | ⏳ Ausstehend |
| 6 | Dokumentation | 8 | 📋 review-specialist | ⏳ Ausstehend |

**Legende:** ⏳ Ausstehend | 🔄 In Bearbeitung | ✅ Abgeschlossen | ⏸️ Pausiert

---

## Agenten-Einsatz pro Phase

### Phase 1: Internationalisierung

```
┌─────────────────────────────────────────────────────────────────┐
│  SCHRITT 1: Analyse                                             │
│  → Agent: 🌐 i18n-specialist                                    │
│  → Aktion: "Analysiere die i18n-Situation"                      │
│  → Output: Liste aller Strings, fehlende Text-Domains           │
├─────────────────────────────────────────────────────────────────┤
│  SCHRITT 2: POT generieren                                      │
│  → Agent: 🌐 i18n-specialist                                    │
│  → Aktion: "Generiere die POT-Datei"                            │
├─────────────────────────────────────────────────────────────────┤
│  SCHRITT 3: Übersetzungen erstellen                             │
│  → Manuell oder mit Tool                                        │
├─────────────────────────────────────────────────────────────────┤
│  SCHRITT 4: Validierung                                         │
│  → Agent: 🌐 i18n-specialist                                    │
│  → Aktion: "Prüfe die Übersetzungen"                            │
├─────────────────────────────────────────────────────────────────┤
│  SCHRITT 5: Tests                                               │
│  → Agent: 🧪 test-runner                                        │
│  → Aktion: "Führe Tests aus"                                    │
└─────────────────────────────────────────────────────────────────┘
```

### Phase 2: Sicherheits-Refactoring

```
┌─────────────────────────────────────────────────────────────────┐
│  SCHRITT 1: Security-Scan                                       │
│  → Agent: 🔒 security-checker                                   │
│  → Aktion: "Security-Check durchführen"                         │
│  → Output: Liste aller Sicherheitsprobleme                      │
├─────────────────────────────────────────────────────────────────┤
│  SCHRITT 2: Fixes implementieren                                │
│  → Pro Problem: Code ändern                                     │
├─────────────────────────────────────────────────────────────────┤
│  SCHRITT 3: Verifizierung                                       │
│  → Agent: 🔒 security-checker                                   │
│  → Aktion: "Prüfe die Fixes"                                    │
├─────────────────────────────────────────────────────────────────┤
│  SCHRITT 4: Tests                                               │
│  → Agent: 🧪 test-runner                                        │
│  → Aktion: "Führe Tests aus"                                    │
└─────────────────────────────────────────────────────────────────┘
```

### Phase 4: Performance & Qualität (mit Berechnungstests)

```
┌─────────────────────────────────────────────────────────────────┐
│  SCHRITT 1: Parameter-Validierung                               │
│  → Agent: 🔢 calculator-validator                               │
│  → Aktion: "Validiere die Berechnungen"                         │
│  → Output: Matrix aller Parameter und deren Verwendung          │
├─────────────────────────────────────────────────────────────────┤
│  SCHRITT 2: Entscheidungen treffen                              │
│  → Benutzer entscheidet für jeden nicht verwendeten Parameter   │
├─────────────────────────────────────────────────────────────────┤
│  SCHRITT 3: Implementierung                                     │
│  → Code-Änderungen basierend auf Entscheidungen                 │
├─────────────────────────────────────────────────────────────────┤
│  SCHRITT 4: Re-Validierung                                      │
│  → Agent: 🔢 calculator-validator                               │
│  → Aktion: "Prüfe ob alle Parameter jetzt verwendet werden"     │
├─────────────────────────────────────────────────────────────────┤
│  SCHRITT 5: Tests                                               │
│  → Agent: 🧪 test-runner                                        │
│  → Aktion: "Führe Berechnungstests aus"                         │
└─────────────────────────────────────────────────────────────────┘
```

### Phase 5: Icon-Management

```
┌─────────────────────────────────────────────────────────────────┐
│  SCHRITT 1: Icon-Analyse                                        │
│  → Agent: 🎨 icon-manager                                       │
│  → Aktion: "Analysiere die Icon-Struktur"                       │
│  → Output: Aktuelle Struktur, fehlende Icons, Duplikate         │
├─────────────────────────────────────────────────────────────────┤
│  SCHRITT 2: Neue Struktur erstellen                             │
│  → Verzeichnisse anlegen                                        │
├─────────────────────────────────────────────────────────────────┤
│  SCHRITT 3: Migration                                           │
│  → Agent: 🎨 icon-manager                                       │
│  → Aktion: "Migriere die Icons"                                 │
├─────────────────────────────────────────────────────────────────┤
│  SCHRITT 4: Code-Updates                                        │
│  → Import-Pfade in JS aktualisieren                             │
├─────────────────────────────────────────────────────────────────┤
│  SCHRITT 5: Validierung                                         │
│  → Agent: 🎨 icon-manager                                       │
│  → Aktion: "Prüfe ob alle Icons korrekt verlinkt sind"          │
├─────────────────────────────────────────────────────────────────┤
│  SCHRITT 6: Tests                                               │
│  → Agent: 🧪 test-runner                                        │
│  → Aktion: "Führe Build aus"                                    │
└─────────────────────────────────────────────────────────────────┘
```

### Phase 6: Dokumentation (Abschluss)

```
┌─────────────────────────────────────────────────────────────────┐
│  SCHRITT 1: Finaler Review                                      │
│  → Agent: 📋 review-specialist                                  │
│  → Aktion: "Reviewe den gesamten Code"                          │
│  → Output: Abschlussbericht                                     │
├─────────────────────────────────────────────────────────────────┤
│  SCHRITT 2: Dokumentation aktualisieren                         │
│  → Alle docs/*.md Dateien prüfen und aktualisieren              │
├─────────────────────────────────────────────────────────────────┤
│  SCHRITT 3: CHANGELOG                                           │
│  → Vollständigen CHANGELOG-Eintrag erstellen                    │
└─────────────────────────────────────────────────────────────────┘
```

---

## Regeln

1. **Keine Phase ohne Freigabe starten**
   - Jede Phase beginnt erst nach expliziter Benutzer-Bestätigung

2. **Transparenz bei Änderungen**
   - Alle Code-Änderungen werden vor dem Speichern gezeigt
   - Bei Unsicherheiten wird nachgefragt

3. **Tests vor Abschluss**
   - Keine Phase gilt als abgeschlossen ohne bestandene Tests

4. **Dokumentation ist Pflicht**
   - Jede Phase endet mit Dokumentations-Update

5. **Fehler werden sofort gemeldet**
   - Bei Fehlern wird gestoppt und der Benutzer informiert

6. **Backup-Empfehlung**
   - Vor kritischen Phasen wird ein Git-Commit empfohlen

---

## Autonomer Modus

Die Agenten sind so konfiguriert, dass sie **ohne Nachfragen** arbeiten können.

### Erlaubte Befehle (settings.local.json)

```
✅ git (commit, push, etc.)
✅ npm / npx (build, test, scripts)
✅ php (syntax check, etc.)
✅ composer
✅ wp-cli
✅ Dateioperationen (mkdir, cp, mv, rm)
✅ PHPUnit, PHPCS
✅ i18n Tools (msgfmt, msgcmp)
```

### So starten Sie eine autonome Phase

```
"Phase 1 autonom durchführen"
```

Der Agent wird dann:
1. Alle Aufgaben der Phase abarbeiten
2. Tests automatisch ausführen
3. Bei Fehlern versuchen, diese zu beheben
4. Am Ende eine vollständige Zusammenfassung geben

### Wann wird trotzdem nachgefragt?

- **Entscheidungen:** Wenn mehrere Optionen möglich sind
- **Kritische Löschungen:** Wenn wichtige Dateien gelöscht werden sollen
- **Unklare Anforderungen:** Wenn der Plan nicht eindeutig ist
- **Fehler:** Wenn ein Fehler nicht automatisch behoben werden kann

### Empfehlung vor autonomer Ausführung

```bash
# Git-Commit erstellen als Backup
git add -A && git commit -m "Backup vor Phase X"
```

So können Sie jederzeit zurückkehren, falls etwas schiefgeht.

---

## Schnellstart

### Manueller Modus (mit Bestätigungen)

```
"Phase 1 starten"
```

### Autonomer Modus (eine Phase)

```
"Phase 1 autonom durchführen"
```

### Auto-Pilot (ALLE Phasen ohne Nachfragen)

```
Auto-Pilot: Lies .claude/AUTOSTART.md und führe aus.
```

Oder kopieren Sie die vollständige Einweisung aus `.claude/AUTOSTART.md`.

**Hinweis:** Im Auto-Pilot-Modus arbeitet der Agent ALLE 6 Phasen durch, ohne nachzufragen. Ideal wenn Sie den PC verlassen möchten.

---

## Beispiel-Dialog

**Benutzer:** `Phase 1 starten`

**Claude:**
```
📋 PHASE 1: Internationalisierung (i18n)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Ziel: Vollständige i18n mit Deutsch als Standard und Englisch als Alternative

Betroffene Dateien:
- languages/ (neue Dateien)
- includes/class-error-handler.php (neu)
- includes/class-assets.php (anpassen)

Geplante Aufgaben:
1. Error-Handler Klasse erstellen
2. POT-Datei generieren
3. Deutsche Übersetzung erstellen
4. Englische Übersetzung erstellen
5. JS-Übersetzungen konfigurieren

Bereit zum Start? (ja/nein)
```

**Benutzer:** `ja`

**Claude:** [Beginnt mit Aufgabe 1...]

---

## Changelog

| Version | Datum | Änderungen |
|---------|-------|------------|
| 1.0 | 2026-01-18 | Initiale Workflow-Definition |
