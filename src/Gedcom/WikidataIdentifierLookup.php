<?php

declare(strict_types=1);

namespace Hartenthaler\Webtrees\Module\WikidataPlacesModule\Gedcom;

use Hartenthaler\Webtrees\Module\WikidataPlacesModule\Domain\WikidataIdentifier;

/**
 * Result of looking for typed Wikidata identifiers in one shared-place record.
 */
final class WikidataIdentifierLookup
{
    /**
     * @param list<WikidataIdentifier> $identifiers
     */
    public function __construct(private readonly array $identifiers)
    {
    }

    public function identifier(): ?WikidataIdentifier
    {
        return count($this->identifiers) === 1 ? $this->identifiers[0] : null;
    }

    public function isAmbiguous(): bool
    {
        return count($this->identifiers) > 1;
    }

    /** @return list<WikidataIdentifier> */
    public function identifiers(): array
    {
        return $this->identifiers;
    }
}
