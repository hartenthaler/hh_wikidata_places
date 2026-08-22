<?php

declare(strict_types=1);

namespace Hartenthaler\Webtrees\Module\ExternalPlacesModule\Domain;

/** A canonical FactGrid item identifier and its declared authority. */
final class FactGridIdentifier
{
    public const AUTHORITY_URI = 'https://database.factgrid.de/entity/';

    private function __construct(private readonly string $qid)
    {
    }

    public static function tryFrom(string $value): ?self
    {
        $value = trim($value);
        if (str_starts_with($value, self::AUTHORITY_URI)) {
            $value = substr($value, strlen(self::AUTHORITY_URI));
        }

        return preg_match('/^Q[1-9][0-9]*$/', $value) === 1 ? new self($value) : null;
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
}
