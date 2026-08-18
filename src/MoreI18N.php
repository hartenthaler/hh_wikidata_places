<?php

declare(strict_types=1);

namespace Hartenthaler\Webtrees\Module\WikidataPlacesModule;

use Fisharebest\Webtrees\I18N;

final class MoreI18N
{
    public static function xlate(string $message, string ...$args): string
    {
        // A stale compiled/template cache must not turn a translated string
        // with placeholders into a fatal sprintf() error.  Current callers
        // always provide their values; empty fallbacks keep older views safe.
        if ($args === []) {
            $args = array_fill(0, substr_count($message, '%s'), '');
        }

        return I18N::translate($message, ...$args);
    }
}
