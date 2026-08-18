<?php

declare(strict_types=1);

namespace Hartenthaler\Webtrees\Module\WikidataPlacesModule\Gedcom;

use Fisharebest\Webtrees\Location;
use Hartenthaler\Webtrees\Module\WikidataPlacesModule\Domain\WikidataIdentifier;

/**
 * Applies an explicit Wikidata assignment to a shared-place record.
 *
 * This is deliberately the only write boundary for the module.  A future
 * request handler can use the boolean result to return an appropriate 403
 * response or flash message, while this service never changes a record the
 * current webtrees user may not edit.
 */
final class WikidataLocationAssignmentService
{
    public function __construct(private readonly WikidataExternalIdEditor $editor = new WikidataExternalIdEditor())
    {
    }

    /**
     * Replace the typed Wikidata external identifier on a shared place.
     * Other external identifiers are preserved verbatim.
     */
    public function assign(Location $location, WikidataIdentifier $identifier): bool
    {
        if (!$location->canEdit()) {
            return false;
        }

        $location->updateRecord($this->editor->replace($location->gedcom(), $identifier), true);

        return true;
    }

    /** Remove only typed Wikidata external identifiers from a shared place. */
    public function remove(Location $location): bool
    {
        if (!$location->canEdit()) {
            return false;
        }

        $location->updateRecord($this->editor->remove($location->gedcom()), true);

        return true;
    }
}
