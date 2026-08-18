<?php

declare(strict_types=1);

namespace Hartenthaler\Webtrees\Module\WikidataPlacesModule\Wikidata;

/** One read-only Wikidata address statement, including optional validity dates. */
final class HistoricAddress
{
    public function __construct(
        public readonly ?string $streetQid,
        public readonly ?string $streetText,
        public readonly ?string $houseNumber,
        public readonly ?string $postalCode,
        public readonly ?string $localityQid,
        public readonly ?string $from,
        public readonly ?string $until,
    ) {
    }
}
