# Roadmap

## 0.1 – Foundation / Read-only
Goal: establish the external identifier model and robust read-only Wikidata enrichment.

## 0.2 – Assignment ✅
Completed: editors can search Wikidata from a Vesta `_LOC` record, review candidates, assign, replace, or remove a typed Wikidata identifier. The workflow works with and without Pretty URLs, preserves unrelated external identifiers, and updates the shared place’s change record.

## 0.3 – Nearby Discovery ✅
Completed: editors can search up to 20 Wikidata items around a shared place with GEDCOM coordinates. Results are ranked by name similarity and distance, never linked automatically, and use a safe radius configured independently for each family tree.

## 0.4 – Domus Integration ✅
Completed: shared places offer a read-only Domus link through a replaceable provider. Linked QIDs use Domus' documented map deep link; the safe fallback is the map start page. The module does not embed or synchronize Domus.

## 0.5 – Extended Metadata
Goal: expose richer house-history data such as historical addresses and selected owner/resident metadata without importing it into GEDCOM.
