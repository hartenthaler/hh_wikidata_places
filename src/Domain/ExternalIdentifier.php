<?php

declare(strict_types=1);

namespace Hartenthaler\Webtrees\Module\ExternalPlacesModule\Domain;

/** A validated identifier belonging to one of the supported public providers. */
final class ExternalIdentifier
{
    public function __construct(
        public readonly string $provider,
        public readonly string $value,
        public readonly string $authorityUri,
        public readonly string $url,
    ) {
    }
}
