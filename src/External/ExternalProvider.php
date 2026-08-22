<?php

declare(strict_types=1);

namespace Hartenthaler\Webtrees\Module\ExternalPlacesModule\External;

use Hartenthaler\Webtrees\Module\ExternalPlacesModule\Domain\ExternalIdentifier;

interface ExternalProvider
{
    public function key(): string;

    public function label(): string;

    public function authorityUri(): string;

    public function identifier(string $value): ?ExternalIdentifier;

    public function fetch(ExternalIdentifier $identifier, string $language): ?ExternalInformation;
}
