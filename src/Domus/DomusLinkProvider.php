<?php

declare(strict_types=1);

namespace Hartenthaler\Webtrees\Module\ExternalPlacesModule\Domus;

use Hartenthaler\Webtrees\Module\ExternalPlacesModule\Domain\WikidataIdentifier;

/**
 * Isolates Domus URL semantics from webtrees views and Vesta hook output.
 *
 * Domus can evolve its deep-link contract without requiring UI changes in
 * this module.  Coordinates are part of the contract for a later documented
 * coordinate deep link, even though the current Domus link uses a QID only.
 */
interface DomusLinkProvider
{
    /** @param array{latitude:float,longitude:float}|null $coordinates */
    public function url(?WikidataIdentifier $identifier, ?array $coordinates): string;
}
