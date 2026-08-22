<?php

declare(strict_types=1);

namespace Hartenthaler\Webtrees\Module\ExternalPlacesModule\Wikidata;

/** A nearby Wikidata place candidate, ranked only as a search aid. */
final class WikidataNearbyCandidate
{
    public function __construct(
        public readonly string $qid,
        public readonly string $label,
        public readonly ?string $description,
        public readonly float $distanceKm,
        public readonly int $score,
    ) {
    }

    public function entityUrl(): string
    {
        return 'https://www.wikidata.org/entity/' . $this->qid;
    }
}
