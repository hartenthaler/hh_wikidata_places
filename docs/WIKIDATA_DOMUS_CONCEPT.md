# Konzept – webtrees-Modul Wikidata Places / Domus

**Arbeitstitel:** `hh_wikidata_places`  
**Stand:** 17.08.2026  
**Zielplattform:** webtrees 2.2.x, zunächst GEDCOM 5.5.1 mit Vesta Shared Places  
**Abhängigkeit:** Vesta Shared Places für `_LOC`-Datensätze

## 1. Zielbild

Das Modul verbindet Orts- und Gebäudedatensätze in webtrees mit offenen, extern gepflegten Informationen aus Wikidata und ermöglicht von diesen Datensätzen aus den Übergang zur Domus-Anwendung.

Grundprinzipien:

1. **Identität statt Datenkopie:** webtrees speichert primär die Wikidata-QID am `_LOC`-Datensatz.
2. **Anreichern statt synchronisieren:** Wikidata-Daten werden read-only abgerufen und gecacht.
3. **Nutzung statt Nachbau:** Domus bleibt spezialisierter Karten-/Recherche-/Editor-Client; webtrees baut diese Funktionalität nicht nach.

## 2. Fachliches Modell

### 2.1 `_LOC` als lokaler Identitätsträger

Beispiel unter GEDCOM 5.5.1:

```gedcom
0 @L123@ _LOC
1 NAME Hauptstraße 17
1 _EXID Q123456
2 TYPE https://www.wikidata.org/entity/
1 MAP
2 LATI N52.12345
2 LONG E13.12345
```

Semantisches Ziel für GEDCOM 7:

```gedcom
1 EXID Q123456
2 TYPE https://www.wikidata.org/entity/
```

Das Modul identifiziert Wikidata-Verknüpfungen anhand von `TYPE`, nicht allein anhand des `_EXID`-Tags. Mehrere externe Identifikatoren an einem `_LOC` müssen grundsätzlich möglich bleiben.

#### Vereinbarter Kennungsvertrag

Für GEDCOM 5.5.1 erkennt das Modul eine Wikidata-Kennung nur, wenn alle folgenden Bedingungen erfüllt sind:

1. Das Tag `_EXID` steht direkt unterhalb des `_LOC`-Datensatzes.
2. Sein Wert ist eine kanonische Wikidata-Item-ID: `Q` gefolgt von einer positiven Dezimalzahl.
3. Das direkte Kind-Tag `TYPE` hat exakt den Wert `https://www.wikidata.org/entity/`.

Beim Einlesen bestehender Daten darf auch eine vollständige Entity-URL akzeptiert werden; sie wird anschließend für Anzeige und Cache-Schlüssel auf die kanonische QID normalisiert. Das Modul leitet keine Wikidata-Zuordnung aus einer untypisierten `_EXID`, einer Beschriftung oder einer URL in einer Notiz ab. So bleiben andere externe Kennungen eindeutig geschützt.

Im MVP wird höchstens eine passend typisierte Wikidata-QID je `_LOC` verwendet. Finden sich mehrere gültige Wikidata-Kennungen, erzeugt das Modul eine eindeutige Konfigurationswarnung und wählt nicht stillschweigend eine davon aus. Andere `_EXID`-Einträge bleiben unverändert. Bei GEDCOM 7 wechseln Wert und Authority-URI unverändert zu `EXID` / `TYPE`; die Ausleselogik bleibt deshalb unabhängig vom Namen des GEDCOM-Tags.

### 2.2 Geltungsbereich der QID

Die QID bezeichnet dasselbe reale Objekt wie der `_LOC`-Datensatz. Das Modul soll nicht auf Gebäude beschränkt werden; auch Höfe, Kirchen, Friedhöfe, Straßen, Plätze, Ortsteile, Orte und andere geeignete Shared Places können mit Wikidata verknüpft werden.

Für den MVP gilt: höchstens eine Wikidata-QID pro `_LOC`.

## 3. Abgrenzung der Systeme

- **webtrees/GEDCOM:** genealogische Daten, Ereignisse, Personen, Familien, Quellen und `_LOC`.
- **Wikidata:** öffentliche Aussagen zu Orten/Gebäuden, z. B. Typ, Koordinate, Bauzeit, Bilder, Adressen und ggf. historische Beziehungen.
- **Domus:** spezialisierte Recherche-, Karten- und Bearbeitungsoberfläche für Hausgeschichte auf Basis offener Daten.

Das Modul synchronisiert nicht mit Domus und schreibt nicht nach Wikidata zurück.

## 4. Funktionale Anforderungen

### F1 – Wikidata-Verknüpfung anzeigen

Auf der `_LOC`-Seite wird eine vorhandene QID mit Label, Link und Cache-Status angezeigt. Ohne QID wird eine Aktion „Wikidata-Eintrag zuordnen“ angeboten.

### F2 – Wikidata-Suche und Zuordnung

Manuelle Suche nach Label/Name; optional Kandidatenvorschläge anhand Koordinaten. Treffer zeigen QID, Label, Typ und ggf. Entfernung. Keine automatische Zuordnung im MVP.

### F3 – Wikidata-Informationsblock

MVP:

- Label
- Beschreibung
- QID
- Objektart (`P31`)
- Koordinate (`P625`)
- Entstehungs-/Bauzeit soweit sinnvoll modelliert
- Bild (`P18`)
- Adresse soweit sinnvoll modelliert

Später: historische Adressen, administrative Einordnung, Architekt, Denkmalschutz, Eigentümer, Bewohner, Commons-Kategorie und weitere externe Identifier.

### F4 – Sprache und Provenienz

Labels/Beschreibungen folgen der webtrees-Spracheinstellung mit Fallback. Wikidata wird als Quelle der externen Daten kenntlich gemacht. Für Commons-Bilder werden deren individuelle Lizenz- und Attributionsangaben angezeigt.

### F5 – Domus-Verknüpfung

Ein Button „In Domus anzeigen“ verwendet den dokumentierten QID-Link `https://domus.genealogy.net/map/?id=Q…`. Ohne QID öffnet er die Kartenstartseite. Eine Koordinaten-URL wird erst verwendet, wenn Domus dafür einen stabilen Vertrag dokumentiert.

### F6 – Domus-Link als Adapter

Die URL-Generierung wird über einen austauschbaren `DomusLinkProvider` gekapselt, damit spätere Deep-Link-Änderungen keine UI-Anpassungen erfordern.

## 5. Benutzeroberfläche

Zwei Ebenen:

1. kompakte „Externe Daten“-Zusammenfassung auf der `_LOC`-Seite;
2. Detail-Tab „Wikidata“ für umfangreichere Informationen.

## 6. Personenbeziehungen

Bewohner-/Eigentümerangaben aus Wikidata werden im MVP nicht mit webtrees-Personen aufgelöst oder als GEDCOM-Fakten importiert. Entity Resolution zwischen Wikidata-Personen und webtrees-Personen ist ein separates späteres Vorhaben.

## 7. Datenschutz

Für Wikidata-Abfragen werden keine personenbezogenen Daten aus webtrees benötigt. Nach außen gehen höchstens QID, öffentliche Ortsbezeichnung und Koordinate sowie technisch übliche Request-Metadaten. Keine Namen lebender Personen, Familienbeziehungen, privaten Notizen oder Quelleninhalte werden aus webtrees übertragen.

## 8. Technische Architektur

```text
webtrees
   │
Vesta Shared Place (_LOC)
   │
_EXID / TYPE
   │
Q123456
   │
   ├── WikidataService ── Wikidata APIs
   │       │
   │       └── CacheRepository ── webtrees DB
   │
   └── DomusLinkProvider ── domus.genealogy.net
```

### Kernkomponenten

- `WikidataIdentifier`
- `ExternalIdService`
- `WikidataEntityService`
- `WikidataMapper`
- `WikidataCacheRepository`
- `DomusLinkProvider`

### Wikidata-Zugriff

Bekannte QID: Entity/API-Zugriff, nicht primär SPARQL.  
Discovery: Suchendpunkte und/oder SPARQL für räumliche und kombinierte Abfragen.

Konzeptionell:

```text
WikidataClient
 ├── EntityClient
 ├── SearchClient
 └── QueryServiceClient
```

## 9. Cache

Eigene Cache-Tabelle, z. B. mit:

- `cache_key`
- `qid`
- `language`
- `payload`
- `fetched_at`
- `expires_at`
- optional `etag`

Vorschlag TTL:

- Entity-Metadaten: 7–30 Tage
- Suchergebnisse: 1–7 Tage
- negative/Fehler-Caches: kurz, z. B. 1 Stunde

## 10. Fehlerbehandlung

Die normale `_LOC`-Ansicht darf nie durch Wikidata-Ausfälle blockiert werden. Cache-Daten werden mit Standdatum angezeigt. Redirects/zusammengeführte QIDs sollen erkannt und eine Aktualisierung des `_EXID` angeboten werden.

## 11. Berechtigungen

- Lesen: wie `_LOC` sichtbar ist.
- QID zuordnen/ändern/löschen: Benutzer mit Änderungsrecht für den Baum.
- Cache manuell aktualisieren: konfigurierbar, mindestens für berechtigte Benutzer.

## 12. Konfiguration

Pro Baum:

- Wikidata-Funktion aktiv
- bevorzugte/fallback Sprache
- Nearby-Suche aktiv
- Suchradius
- Personeninformationen anzeigen (später)
- Cache-TTLs
- Domus-Link aktiv
- Domus-Basis-URL
- Deep-Link-Modus: automatisch / QID / Koordinate / Startseite

## 13. MVP-Phasen

### 0.1 – Foundation / Read-only

- `_EXID` lesen
- QID validieren
- Wikidata-Link
- Label/Beschreibung/P31/P18 laden
- Cache
- Fehlerbehandlung

### 0.2 – Assignment

- Wikidata-Suche
- Treffer auswählen
- `_EXID` schreiben/ändern/löschen

### 0.3 – Nearby Discovery

- Koordinatensuche
- Kandidaten-Ranking
- Distanzanzeige

### 0.4 – Domus Integration

- stabiler Domus-Link
- Deep-Link-Adapter
- kein iframe im ersten Schritt

### 0.5 – Extended Metadata

- historische Adressen
- Eigentümer/Bewohner
- weitere Wikidata-Eigenschaften

## 14. Offene Fragen

### Blocker vor MVP

1. Verbindliche Semantik: `_EXID Q123456` + `TYPE https://www.wikidata.org/entity/`?
2. Abstimmung mit Vesta und möglichst Gramps/Gramps Web zur Interoperabilität.
3. Welche `_LOC`-Objekttypen sollen unterstützt werden? Empfehlung: grundsätzlich alle geeigneten Shared Places.
4. Kardinalität im MVP: Empfehlung genau eine Wikidata-QID pro `_LOC`.
5. Welche Extension Points stellt Vesta für eigene Facts/Views an `_LOC` stabil bereit?
6. Wie erfolgt die spätere Migration von `_EXID` zu echtem `EXID` bei GEDCOM 7?

### Wikidata-Modellierung

Für jede Information ist das tatsächliche Datenmodell zu prüfen; insbesondere Bauzeit, aktuelle/historische Adresse, Eigentümer und Bewohner dürfen nicht vorschnell auf eine einzelne Property reduziert werden.

### Domus

1. Gibt es bereits undokumentierte Deep Links?
2. Soll ein offizieller `?qid=Q...`-Parameter eingeführt werden?
3. Gibt es einen Koordinaten-Deep-Link?
4. Soll URL-Semantik als stabiler externer Vertrag dokumentiert werden?
5. iframe/Embed erst später prüfen; für MVP ausdrücklich kein iframe.

### Gramps Web / David Straub

Zu klären:

1. Plant Gramps Web eine Wikidata-Verknüpfung für Orte/Gebäude?
2. Welches lokale Datenobjekt wird verknüpft?
3. Wird GEDCOM `EXID` verwendet?
4. Welcher `TYPE`-URI soll Wikidata kennzeichnen?
5. Wird QID oder vollständige URI gespeichert?
6. Plant Gramps Web Domus-Links?
7. Plant Domus einen stabilen QID-Deep-Link?
8. Können webtrees und Gramps Web dasselbe Verknüpfungsmodell verwenden?

## 15. Empfohlene Modulstruktur

```text
hh_wikidata_places/
├── module.php
├── src/
│   ├── Domain/
│   ├── Gedcom/
│   ├── Wikidata/
│   ├── Cache/
│   ├── Domus/
│   ├── Http/
│   └── WikidataPlacesModule.php
├── resources/
│   ├── views/
│   └── lang/
├── docs/
└── tests/
```

## 16. Akzeptanzkriterien für den ersten echten MVP

- Ein Vesta-`_LOC` kann eine Wikidata-QID in `_EXID` speichern.
- Die QID bleibt im GEDCOM-Export erhalten und wird nach Import erkannt.
- Wikidata wird ausschließlich anhand des definierten `TYPE` erkannt.
- Suche und manuelle Zuordnung funktionieren.
- Label, Beschreibung, Typ und Bild werden angezeigt.
- Sprache folgt webtrees.
- Wikidata-Ausfälle beeinträchtigen die Ortsansicht nicht.
- Abfragen werden gecacht.
- Keine genealogischen Personendaten werden an Wikidata übertragen.
- Zuordnung kann geändert/entfernt werden.
- `DomusLinkProvider` ist als austauschbare Schicht vorhanden.
- Wikidata-Funktion funktioniert ohne Domus.
