# Planned GitHub milestones and issues

## Milestone 0.1 – Foundation / Read-only

1. **Define Wikidata EXID semantics for `_LOC`**  
   Decide and document QID payload, authority URI and GEDCOM 5.5.1/7 migration behavior.

2. **Add `WikidataIdentifier` value object**  
   Validate QIDs and centralize identifier handling.

3. **Implement `ExternalIdService` for Vesta `_LOC` records**  
   Read Wikidata `_EXID`, distinguish it by `TYPE`, and keep support for other authorities.

4. **Implement Wikidata entity client and mapper**  
   Fetch known QIDs without depending on SPARQL and map external JSON into an internal model.

5. **Add Wikidata cache repository and schema**  
   Persist language-aware snapshots with TTL and negative-cache behavior.

6. **Add read-only Wikidata panel to Shared Place view**  
   Display QID, label, description, P31 and image with graceful degradation.

7. **Add language fallback and Commons attribution handling**  
   Follow webtrees language and render image metadata/licensing correctly.

## Milestone 0.2 – Assignment

8. **Implement Wikidata label search**  
   Search candidate entities based on `_LOC` names and relevant context.

9. **Build assignment dialog for Wikidata candidates**  
   Show QID, label, type and external links before assignment.

10. **Write, replace and remove Wikidata `_EXID`**  
    Respect webtrees tree edit permissions and preserve GEDCOM portability.

11. **Handle Wikidata redirects and merged items**  
    Detect obsolete QIDs and offer controlled replacement.

## Milestone 0.3 – Nearby Discovery

12. **Implement coordinate-based nearby discovery**  
    Find Wikidata candidates around `_LOC` coordinates using an appropriate query endpoint.

13. **Add candidate ranking and distance display**  
    Combine name/type/distance signals without automatic linking.

14. **Make nearby-search radius configurable per tree**  
    Provide safe defaults and allow future type-specific radii.

## Milestone 0.4 – Domus Integration

15. **Define `DomusLinkProvider` abstraction**  
    Keep Domus URL generation isolated from UI templates.

16. **Implement Domus outbound link**  
    Support documented QID/coordinate deep links when available, otherwise degrade to map start page.

## Milestone 0.5 – Extended Metadata

17. **Model and display historical address data**  
    Align property/qualifier handling with the model actually used by Domus/Wikidata.

18. **Evaluate owner/resident metadata presentation**  
    Define privacy, provenance and UI rules; do not perform automatic person entity resolution or GEDCOM import.
