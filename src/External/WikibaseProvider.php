<?php

declare(strict_types=1);

namespace Hartenthaler\Webtrees\Module\ExternalPlacesModule\External;

use Hartenthaler\Webtrees\Module\ExternalPlacesModule\Domain\ExternalIdentifier;
use Hartenthaler\Webtrees\Module\ExternalPlacesModule\Wikibase\ReadOnlyWikibaseClient;

/** Provider adapter for Wikidata-like, read-only Wikibase installations. */
final class WikibaseProvider implements ExternalProvider
{
    /** @param array{authority:string, label:string, type:string, image:string, factgrid:?string, wikidata:?string, gov:?string} $definition */
    public function __construct(private readonly string $key, private readonly array $definition, private readonly ReadOnlyWikibaseClient $client = new ReadOnlyWikibaseClient(), private readonly ExternalProviderCache $cache = new ExternalProviderCache())
    {
    }

    public function key(): string
    {
        return $this->key;
    }

    public function label(): string
    {
        return $this->definition['label'];
    }

    public function authorityUri(): string
    {
        return $this->definition['authority'];
    }

    public function identifier(string $value): ?ExternalIdentifier
    {
        $value = trim($value);
        if (str_starts_with($value, $this->authorityUri())) {
            $value = substr($value, strlen($this->authorityUri()));
        }
        if (preg_match('/^Q[1-9][0-9]*$/', $value) !== 1) {
            return null;
        }

        return new ExternalIdentifier($this->key(), $value, $this->authorityUri(), $this->entityUrl($value));
    }

    public function fetch(ExternalIdentifier $identifier, string $language): ?ExternalInformation
    {
        $cacheKey = $identifier->value . '|' . $language;
        $payload = $this->cache->read($this->key(), $cacheKey);
        if ($payload === null) {
            $payload = $this->client->entity($this->key(), $identifier->value, $language);
            if ($payload !== null) { $this->cache->write($this->key(), $cacheKey, $payload); }
        }
        $entity = $payload['entities'][$identifier->value] ?? null;
        if (!is_array($entity) || array_key_exists('missing', $entity)) {
            return null;
        }

        $claims = is_array($entity['claims'] ?? null) ? $entity['claims'] : [];
        $references = [];
        foreach (['factgrid', 'wikidata', 'gov'] as $provider) {
            $property = $this->definition[$provider];
            if ($property !== null) {
                $values = $this->claimStrings($claims[$property] ?? [], $provider);
                if ($values !== []) {
                    $references[$provider] = $values;
                }
            }
        }

        return new ExternalInformation(
            $this->key(),
            $identifier->value,
            $identifier->url,
            $this->languageValue($entity['labels'] ?? [], $language),
            $this->languageValue($entity['descriptions'] ?? [], $language),
            $this->imageUrl($claims[$this->definition['image']] ?? []),
            $this->claimEntityIds($claims[$this->definition['type']] ?? []),
            $references,
        );
    }

    private function entityUrl(string $value): string
    {
        return $this->authorityUri() . $value;
    }

    private function languageValue(mixed $values, string $language): ?string
    {
        if (!is_array($values)) { return null; }
        $language = strtolower(str_replace('_', '-', trim($language)));
        $language = explode('-', $language)[0] ?: 'en';
        foreach (array_unique([$language, 'en']) as $candidate) {
            $value = $values[$candidate]['value'] ?? null;
            if (is_string($value) && trim($value) !== '') { return trim($value); }
        }
        return null;
    }

    /** @return list<string> */
    private function claimStrings(mixed $statements, string $provider): array
    {
        $values = [];
        foreach (is_array($statements) ? $statements : [] as $statement) {
            $value = $statement['mainsnak']['datavalue']['value'] ?? null;
            if (!is_string($value)) { continue; }
            $valid = $provider === 'gov' ? preg_match('/^[A-Z][A-Z0-9_]{2,63}$/', $value) === 1 : preg_match('/^Q[1-9][0-9]*$/', $value) === 1;
            if ($valid) { $values[] = $value; }
        }
        return array_values(array_unique($values));
    }

    /** @return list<string> */
    private function claimEntityIds(mixed $statements): array
    {
        $values = [];
        foreach (is_array($statements) ? $statements : [] as $statement) {
            $value = $statement['mainsnak']['datavalue']['value']['id'] ?? null;
            if (is_string($value) && preg_match('/^Q[1-9][0-9]*$/', $value) === 1) { $values[] = $value; }
        }
        return array_values(array_unique($values));
    }

    private function imageUrl(mixed $statements): ?string
    {
        foreach (is_array($statements) ? $statements : [] as $statement) {
            $value = $statement['mainsnak']['datavalue']['value'] ?? null;
            if (is_string($value) && $value !== '') {
                return 'https://commons.wikimedia.org/wiki/Special:FilePath/' . rawurlencode($value);
            }
        }
        return null;
    }
}
