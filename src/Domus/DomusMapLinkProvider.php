<?php

declare(strict_types=1);

namespace Hartenthaler\Webtrees\Module\ExternalPlacesModule\Domus;

use Hartenthaler\Webtrees\Module\ExternalPlacesModule\Domain\WikidataIdentifier;

/** Current documented Domus map deep link with a safe start-page fallback. */
final class DomusMapLinkProvider implements DomusLinkProvider
{
    private const MAP_URL = 'https://domus.genealogy.net/map/';

    public function url(?WikidataIdentifier $identifier, ?array $coordinates): string
    {
        if ($identifier !== null) {
            return self::MAP_URL . '?id=' . rawurlencode($identifier->qid());
        }

        // Domus does not currently document a stable coordinate URL contract.
        // Do not invent one: preserve the useful map-start fallback instead.
        return self::MAP_URL;
    }
}
