<?php

declare(strict_types=1);

namespace Hartenthaler\Webtrees\Module\WikidataPlacesModule\Gedcom;

use Hartenthaler\Webtrees\Module\WikidataPlacesModule\Domain\WikidataIdentifier;

/** Safely replaces or removes only typed Wikidata external identifiers. */
final class WikidataExternalIdEditor
{
    public function replace(string $gedcom, WikidataIdentifier $identifier): string
    {
        return $this->edit($gedcom, $identifier);
    }

    public function remove(string $gedcom): string
    {
        return $this->edit($gedcom, null);
    }

    private function edit(string $gedcom, ?WikidataIdentifier $replacement): string
    {
        $lines = preg_split('/\R/u', rtrim($gedcom)) ?: [];
        $kept  = [];
        for ($i = 0, $count = count($lines); $i < $count; ++$i) {
            if (preg_match('/^1 (?:_EXID|EXID) (.+)$/', $lines[$i], $match) === 1 && WikidataIdentifier::tryFrom($match[1]) !== null && isset($lines[$i + 1]) && preg_match('/^2 TYPE https:\/\/www\.wikidata\.org\/entity\/$/', $lines[$i + 1]) === 1) {
                ++$i;
                continue;
            }
            $kept[] = $lines[$i];
        }

        if ($replacement !== null) {
            $kept[] = '1 _EXID ' . $replacement->qid();
            $kept[] = '2 TYPE ' . WikidataIdentifier::AUTHORITY_URI;
        }

        return implode("\n", $kept) . "\n";
    }
}
