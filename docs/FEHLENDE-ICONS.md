# Fehlende Icons für den Verkaufswert-Rechner

**Stand:** 2026-01-17
**Anzahl:** 30 Icons
**Ablageort:** `assets/images/`

---

## 1. Immobilientyp (Step 1)

| Icon | Dateiname | Beschreibung |
|------|-----------|--------------|
| Grundstück | `grundstueck.svg` | Unbebautes Grundstück |

---

## 2. Haustypen (Step 1 - bei Auswahl "Haus")

| Icon | Dateiname | Beschreibung |
|------|-----------|--------------|
| Einfamilienhaus | `einfamilienhaus.svg` | Freistehendes EFH |
| Mehrfamilienhaus | `mehrfamilienhaus.svg` | MFH mit mehreren Wohneinheiten |
| Doppelhaushälfte | `doppelhaushaelfte.svg` | DHH |
| Mittelreihenhaus | `reihenhaus-mitte.svg` | RMH (zwei Nachbarn) |
| Endreihenhaus | `reihenhaus-ende.svg` | REH (ein Nachbar) |
| Bungalow | `bungalow.svg` | Eingeschossiges Haus |

---

## 3. Modernisierung (Step 2)

| Icon | Dateiname | Beschreibung |
|------|-----------|--------------|
| Vor 1-3 Jahren | `modernisierung-1-3.svg` | Kürzlich modernisiert (Sterne/Neu-Symbol) |
| Vor 4-9 Jahren | `modernisierung-4-9.svg` | Neuere Modernisierung |
| Vor 10-15 Jahren | `modernisierung-10-15.svg` | Ältere Modernisierung |
| Vor >15 Jahren | `modernisierung-alt.svg` | Lange her |
| Noch nie | `modernisierung-nie.svg` | Originalzustand |

---

## 4. Ausstattung (Step 3) - Ergänzungen

| Icon | Dateiname | Beschreibung |
|------|-----------|--------------|
| Solaranlage | `solar.svg` | PV-Anlage / Solarthermie |
| Kamin | `kamin.svg` | Kamin / Ofen |
| Parkettboden | `parkett.svg` | Hochwertiger Bodenbelag |
| Dachboden | `dachboden.svg` | Ausbaufähiger/nutzbarer Dachboden |

**Bereits vorhanden:** balkon.svg, terrasse.svg, garte.svg, garage.svg, stellplatz.svg, keller.svg, aufzug.svg, kueche.svg

---

## 5. Qualitätsstufen (Step 4)

| Icon | Dateiname | Beschreibung |
|------|-----------|--------------|
| Einfach | `qualitaet-einfach.svg` | Einfache Ausstattung (1 Stern) |
| Normal | `qualitaet-normal.svg` | Standard (2 Sterne) |
| Gehoben | `qualitaet-gehoben.svg` | Gehobene Ausstattung (3 Sterne) |
| Luxuriös | `qualitaet-luxus.svg` | Luxusausstattung (4 Sterne) |

---

## 6. Nutzung & Ziel (Step 6)

| Icon | Dateiname | Beschreibung |
|------|-----------|--------------|
| Selbstgenutzt | `nutzung-selbst.svg` | Eigennutzung (Person im Haus) |
| Vermietet | `nutzung-vermietet.svg` | Aktuell vermietet (Schlüssel) |
| Leerstand | `nutzung-leer.svg` | Unbewohnt (leeres Haus) |
| Verkaufen | `ziel-verkaufen.svg` | Verkaufsabsicht (Verkaufsschild) |
| Kaufen | `ziel-kaufen.svg` | Kaufinteresse (Einkaufswagen/Haus) |

---

## 7. Zeitrahmen (Step 6)

| Icon | Dateiname | Beschreibung |
|------|-----------|--------------|
| Sofort | `zeit-sofort.svg` | Sofortiger Verkauf (Blitz/Uhr) |
| 3 Monate | `zeit-3m.svg` | Kurzfristig |
| 6 Monate | `zeit-6m.svg` | Mittelfristig |
| 12 Monate | `zeit-12m.svg` | Langfristig |
| Unbestimmt | `zeit-offen.svg` | Kein fester Zeitrahmen (Fragezeichen) |

---

## Zusammenfassung

| Kategorie | Anzahl | Priorität |
|-----------|--------|-----------|
| Immobilientyp | 1 | Hoch |
| Haustypen | 6 | Hoch |
| Modernisierung | 5 | Hoch |
| Ausstattung | 4 | Mittel |
| Qualität | 4 | Hoch |
| Nutzung & Ziel | 5 | Mittel |
| Zeitrahmen | 5 | Niedrig |
| **Gesamt** | **30** | |

---

## Bereits vorhandene Icons (wiederverwendbar)

Diese Icons existieren bereits in `assets/images/`:

- `wohnung.svg` - Wohnung
- `haus.svg` - Haus
- `gewerbe.svg` - Gewerbe
- `balkon.svg` - Balkon
- `terrasse.svg` - Terrasse
- `garte.svg` - Garten (Hinweis: Tippfehler im Dateinamen)
- `garage.svg` - Garage
- `stellplatz.svg` - Stellplatz
- `keller.svg` - Keller
- `aufzug.svg` - Aufzug
- `kueche.svg` - Einbauküche
- `fussbodenheizung.svg` - Fußbodenheizung
- `barrierefrei.svg` - Barrierefrei
- `wc.svg` - Gäste-WC
- `neubau.svg` - Neubau
- `renoviert.svg` - Renoviert
- `gut.svg` - Guter Zustand
- `reparaturen.svg` - Renovierungsbedürftig

---

## Design-Hinweise

- **Stil:** Einheitlich mit bestehenden Icons (Linienstärke, Farbe)
- **Format:** SVG (Vektorgrafik)
- **Größe:** ca. 64x64px Viewbox
- **Farbe:** Monochrom, übernimmt CSS-Farbe (currentColor)
