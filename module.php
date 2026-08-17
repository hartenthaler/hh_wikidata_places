<?php

declare(strict_types=1);

use Hartenthaler\Webtrees\Module\WikidataPlacesModule\WikidataPlacesModule;

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
require __DIR__ . '/src/Wikidata/WikidataEntity.php';
require __DIR__ . '/src/Wikidata/WikidataEntityMapper.php';
require __DIR__ . '/src/Wikidata/WikidataClient.php';
require __DIR__ . '/src/Infrastructure/WikidataCacheSchema.php';
require __DIR__ . '/src/Infrastructure/WikidataCacheRepository.php';
require __DIR__ . '/src/WikidataPlacesModule.php';

return new WikidataPlacesModule();
