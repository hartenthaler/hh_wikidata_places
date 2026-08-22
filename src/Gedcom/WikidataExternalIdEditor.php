<?php

declare(strict_types=1);

namespace Hartenthaler\Webtrees\Module\ExternalPlacesModule\Gedcom;

use Hartenthaler\Webtrees\Module\ExternalPlacesModule\Domain\WikidataIdentifier;

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
            if (preg_match('/^1 (?:_EXID|EXID) (.+)$/', $lines[$i], $match) !== 1) {
                $kept[] = $lines[$i];
                continue;
            }

            $block = [$lines[$i]];
            while ($i + 1 < $count && preg_match('/^1 /', $lines[$i + 1]) !== 1) {
                $block[] = $lines[++$i];
            }

            $identifier = WikidataIdentifier::tryFrom($match[1]);
            $authority  = null;
            foreach ($block as $line) {
                if (preg_match('/^2 TYPE (.+)$/', $line, $type) === 1) {
                    $authority = $type[1];
                    break;
                }
            }

            if ($identifier !== null && $authority !== null && $identifier->isDeclaredBy($authority)) {
                continue;
            }

            array_push($kept, ...$block);
        }

        if ($replacement !== null) {
            $kept[] = '1 _EXID ' . $replacement->qid();
            $kept[] = '2 TYPE ' . WikidataIdentifier::AUTHORITY_URI;
        }

        return implode("\n", $kept) . "\n";
    }
}
