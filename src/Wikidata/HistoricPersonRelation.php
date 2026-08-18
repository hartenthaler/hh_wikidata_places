<?php

declare(strict_types=1);

namespace Hartenthaler\Webtrees\Module\WikidataPlacesModule\Wikidata;

/** A public Wikidata relationship between a place and a person or organisation. */
final class HistoricPersonRelation
{
    public function __construct(
        public readonly string $qid,
        public readonly ?string $from,
        public readonly ?string $until,
    ) {
    }
}
