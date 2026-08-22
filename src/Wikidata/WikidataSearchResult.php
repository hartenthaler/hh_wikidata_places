<?php

declare(strict_types=1);

namespace Hartenthaler\Webtrees\Module\ExternalPlacesModule\Wikidata;

/** One bounded candidate returned by Wikidata's entity search endpoint. */
final class WikidataSearchResult
{
    public function __construct(
        public readonly string $qid,
        public readonly string $label,
        public readonly ?string $description,
    ) {
    }

    public function entityUrl(): string
    {
        return 'https://www.wikidata.org/entity/' . $this->qid;
    }
}
