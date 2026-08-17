<?php

declare(strict_types=1);

namespace Hartenthaler\Webtrees\Module\WikidataPlacesModule;

use Fisharebest\Webtrees\I18N;

final class MoreI18N
{
    public static function translate(string $message): string
    {
        return I18N::translate($message);
    }
}
