# Historical addresses from Wikidata

This is a Wikidata-specific enrichment within the provider-neutral **External
Places** module. FactGrid and GOV use their own provider mappings and are not
assumed to expose the same address properties.

External Places displays historical addresses as read-only external data. It
never writes address data to GEDCOM and never creates an address from the type
of a shared place.

## When the table is shown

The **Addresses** table is shown only if Wikidata contains an address statement:

1. `P669` (located on street) is preferred; or
2. `P6375` (street address) is used only when no structured `P669` statement exists.

Consequently, a grave, settlement, administrative area or other item without an address statement has no empty or speculative address section.

## Columns and qualifiers

For each `P669` statement, the module reads only qualifiers that belong to that same statement:

| Display column | Wikidata value |
| --- | --- |
| House number | `P670` |
| Street | main value of `P669` |
| Postal code | `P281` |
| Place | `P131` qualifier |
| From | `P580`, or `P585` when no start time exists |
| To | `P582` |

Undated rows represent a current or otherwise undated address and intentionally leave **From** and **To** empty. A global `P131` statement on the building is not copied into every row; it would not prove that the locality applied to a particular historical address.

`P6375` is free text. It is displayed as the street value, and the structured component columns remain empty.

## Language and provenance

Street and place items are resolved using the webtrees display language with the existing English fallback. Textual addresses retain the language stored in Wikidata. Wikidata remains the source for every displayed row.
