<?php

declare(strict_types=1);

use Hartenthaler\Webtrees\Module\WikidataPlacesModule\WikidataPlacesModule;
use Hartenthaler\Webtrees\Module\WikidataPlacesModule\VestaWikidataPlacesModule;

// webtrees loads custom modules independently.  Register Vesta's classes
// before loading our class declaration, which implements Vesta's hook API.
$vestaAutoload = dirname(__DIR__) . '/vesta_common/autoload.php';
if (is_file($vestaAutoload)) {
    require_once $vestaAutoload;
}

require __DIR__ . '/src/MoreI18N.php';
require __DIR__ . '/src/Domain/WikidataIdentifier.php';
require __DIR__ . '/src/Gedcom/WikidataIdentifierLookup.php';
require __DIR__ . '/src/Gedcom/ExternalIdService.php';
require __DIR__ . '/src/Gedcom/WikidataExternalIdEditor.php';
require __DIR__ . '/src/Gedcom/WikidataLocationAssignmentService.php';
require __DIR__ . '/src/Http/WikidataLocationAssignmentAction.php';
require __DIR__ . '/src/Http/WikidataLocationAssignmentPage.php';
require __DIR__ . '/src/Wikidata/WikidataEntity.php';
require __DIR__ . '/src/Wikidata/WikidataEntityMapper.php';
require __DIR__ . '/src/Wikidata/WikidataSearchResult.php';
require __DIR__ . '/src/Wikidata/WikidataClient.php';
require __DIR__ . '/src/Infrastructure/WikidataCacheSchema.php';
require __DIR__ . '/src/Infrastructure/WikidataCacheRepository.php';
require __DIR__ . '/src/WikidataPlacesModule.php';

if (interface_exists(\Vesta\Hook\HookInterfaces\PrintFunctionsPlaceInterface::class)) {
    require __DIR__ . '/src/VestaWikidataPlacesModule.php';

    return new VestaWikidataPlacesModule();
}

return new WikidataPlacesModule();
