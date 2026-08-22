<?php

declare(strict_types=1);

namespace Hartenthaler\Webtrees\Module\ExternalPlacesModule\Gedcom;

use Hartenthaler\Webtrees\Module\ExternalPlacesModule\Domain\WikidataIdentifier;

/**
 * Reads typed external identifiers from a single GEDCOM shared-place record.
 *
 * It intentionally does not search nested records or infer the identifier
 * authority from free text.  Writing identifiers belongs to the assignment
 * milestone and is not part of this read-only service.
 */
final class ExternalIdService
{
    public function wikidataIdentifiers(string $gedcom): WikidataIdentifierLookup
    {
        $identifiers = [];
        $lines       = preg_split('/\R/u', $gedcom) ?: [];
        $lineCount   = count($lines);

        for ($index = 0; $index < $lineCount; ++$index) {
            if (preg_match('/^1 (?:_EXID|EXID) (.+)$/', $lines[$index], $matches) !== 1) {
                continue;
            }

            $identifier = WikidataIdentifier::tryFrom($matches[1]);
            if ($identifier === null) {
                continue;
            }

            $authority = null;
            for (++$index; $index < $lineCount; ++$index) {
                if (preg_match('/^1 /', $lines[$index]) === 1) {
                    --$index;
                    break;
                }

                if (preg_match('/^2 TYPE (.+)$/', $lines[$index], $type) === 1) {
                    $authority = $type[1];
                }
            }

            if ($authority !== null && $identifier->isDeclaredBy($authority)) {
                $identifiers[$identifier->qid()] = $identifier;
            }
        }

        return new WikidataIdentifierLookup(array_values($identifiers));
    }
}
