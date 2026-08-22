# Module and repository migration

The module is no longer limited to Wikidata. It now provides a common,
read-only integration for Wikidata, FactGrid and GOV, including identifier
consistency checks, provider-specific search and nearby discovery.

## Resulting public name

**External Places**

This name describes the user-facing purpose without tying the module to one
database. It also leaves room for additional public place providers such as
GOV-related services or other Wikibase installations.

## Resulting GitHub repository

`hartenthaler/hh_external_places`

The repository was renamed from `hartenthaler/hh_wikidata_places` after the
module gained FactGrid and GOV support. GitHub's former repository URL is
expected to redirect to this repository.

## Completed migration

1. The public display name is **External Places**.
2. The GitHub repository and local directory are `hh_external_places`.
3. The webtrees module ID, namespace, routes, view namespace and cache path use
   `hh_external_places` / `ExternalPlacesModule`.
4. Existing installations must remove the old module directory before placing
   the renamed module in `modules_v4/hh_external_places`. Preferences and
   external identifiers remain in webtrees/GEDCOM and are not changed by the
   provider migration.
