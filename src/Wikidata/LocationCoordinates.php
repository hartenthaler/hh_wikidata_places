<?php

declare(strict_types=1);

namespace Hartenthaler\Webtrees\Module\WikidataPlacesModule\Wikidata;

/** Extracts a valid GEDCOM MAP/LATI/LONG coordinate pair from a shared place. */
final class LocationCoordinates
{
    /** @return array{latitude:float,longitude:float}|null */
    public static function fromGedcom(string $gedcom): ?array
    {
        $map = null;
        if (preg_match('/\n1 MAP\\b[^\n]*(?:\n[2-9].*)*/', $gedcom, $match) === 1) {
            $map = $match[0];
        }
        $coordinateSource = $map ?? $gedcom;

        // GEDCOM 5.5.1 stores coordinates below MAP.  Some existing Vesta
        // shared-place data uses direct LATI/LONG facts; accept that harmless
        // legacy variant as well, but only when both values are valid.
        if (preg_match('/\n[1-9] LATI\\s+([^\n]+)/', $coordinateSource, $latitude) !== 1
            || preg_match('/\n[1-9] LONG\\s+([^\n]+)/', $coordinateSource, $longitude) !== 1) {
            return null;
        }

        $lat = self::coordinate($latitude[1], -90.0, 90.0);
        $lon = self::coordinate($longitude[1], -180.0, 180.0);

        if ($lat === null || $lon === null) {
            return null;
        }

        return ['latitude' => $lat, 'longitude' => $lon];
    }

    private static function coordinate(string $value, float $minimum, float $maximum): ?float
    {
        if (preg_match('/^([NSEW])?\\s*([+-]?[0-9]+(?:\\.[0-9]+)?)\\s*([NSEW])?$/i', trim($value), $matches) !== 1) {
            return null;
        }

        $prefix    = strtoupper($matches[1] ?? '');
        $suffix    = strtoupper($matches[3] ?? '');
        $direction = $prefix !== '' ? $prefix : $suffix;
        if ($prefix !== '' && $suffix !== '' && $prefix !== $suffix) {
            return null;
        }

        $coordinate = (float) $matches[2];
        if ($direction === 'N' || $direction === 'E') {
            $coordinate = abs($coordinate);
        }
        if ($direction === 'S' || $direction === 'W') {
            $coordinate = -abs($coordinate);
        }

        return $coordinate >= $minimum && $coordinate <= $maximum ? $coordinate : null;
    }
}
