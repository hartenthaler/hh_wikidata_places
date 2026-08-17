<?php

declare(strict_types=1);

namespace Hartenthaler\Webtrees\Module\WikidataPlacesModule\Domain;

/**
 * A canonical Wikidata item identifier and its declared authority.
 *
 * This small value object deliberately accepts only item identifiers.  It
 * keeps identifier validation independent from GEDCOM parsing and prevents
 * callers from constructing request URLs from arbitrary external IDs.
 */
final class WikidataIdentifier
{
    public const AUTHORITY_URI = 'https://www.wikidata.org/entity/';

    private function __construct(private readonly string $qid)
    {
    }

    /**
     * Create an identifier from a QID or from a complete Wikidata entity URL.
     *
     * @return self|null Null if the value is not a canonical Wikidata item ID.
     */
    public static function tryFrom(string $value): ?self
    {
        $value = trim($value);

        if (str_starts_with($value, self::AUTHORITY_URI)) {
            $value = substr($value, strlen(self::AUTHORITY_URI));
        }

        if (preg_match('/^Q[1-9][0-9]*$/', $value) !== 1) {
            return null;
        }

        return new self($value);
    }

    public function qid(): string
    {
        return $this->qid;
    }

    public function entityUrl(): string
    {
        return self::AUTHORITY_URI . $this->qid;
    }

    public function isDeclaredBy(string $authorityUri): bool
    {
        return trim($authorityUri) === self::AUTHORITY_URI;
    }

    public function __toString(): string
    {
        return $this->qid;
    }
}
