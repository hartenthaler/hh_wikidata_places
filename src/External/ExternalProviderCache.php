<?php

declare(strict_types=1);

namespace Hartenthaler\Webtrees\Module\ExternalPlacesModule\External;

use Fisharebest\Webtrees\Webtrees;

/** Small bounded JSON cache shared by the non-Wikidata provider adapters. */
final class ExternalProviderCache
{
    public const TTL = 86400;

    /** @return array<string,mixed>|null */
    public function read(string $provider, string $identifier): ?array
    {
        $file = $this->file($provider, $identifier);
        if (!is_file($file) || filemtime($file) === false || time() - (int) filemtime($file) >= self::TTL) {
            return null;
        }
        $data = json_decode((string) file_get_contents($file), true);
        return is_array($data) ? $data : null;
    }

    /** @param array<string,mixed> $data */
    public function write(string $provider, string $identifier, array $data): void
    {
        $file = $this->file($provider, $identifier);
        $directory = dirname($file);
        if (!is_dir($directory)) { @mkdir($directory, 0775, true); }
        $encoded = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($encoded !== false) { @file_put_contents($file, $encoded, LOCK_EX); }
    }

    private function file(string $provider, string $identifier): string
    {
        $safe = preg_replace('/[^a-z0-9_-]+/i', '-', $provider) ?: 'external';
        return Webtrees::DATA_DIR . 'cache/hh_external_places/' . $safe . '-' . md5($identifier) . '.json';
    }
}
