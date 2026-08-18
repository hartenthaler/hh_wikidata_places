<?php

declare(strict_types=1);

namespace Hartenthaler\Webtrees\Module\WikidataPlacesModule\Wikidata;

use Fisharebest\Webtrees\Tree;

/** Per-tree, bounded settings for the editor-only nearby discovery. */
final class NearbyDiscoverySettings
{
    public const PREFERENCE = 'HH_WIKIDATA_PLACES_NEARBY_RADIUS_KM';
    public const DEFAULT_RADIUS_KM = 5.0;

    public static function radius(Tree $tree): float
    {
        return self::normalise($tree->getPreference(self::PREFERENCE, (string) self::DEFAULT_RADIUS_KM));
    }

    public static function normalise(string $radius): float
    {
        return is_numeric($radius) ? max(0.1, min(100.0, (float) $radius)) : self::DEFAULT_RADIUS_KM;
    }
}
