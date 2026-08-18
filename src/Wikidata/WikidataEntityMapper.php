<?php

declare(strict_types=1);

namespace Hartenthaler\Webtrees\Module\WikidataPlacesModule\Wikidata;

use Hartenthaler\Webtrees\Module\WikidataPlacesModule\Domain\WikidataIdentifier;

/** @internal Maps the fixed wbgetentities response subset to a value object. */
final class WikidataEntityMapper
{
    /** @param array<string, mixed> $payload */
    public function map(WikidataIdentifier $identifier, array $payload, string $language): ?WikidataEntity
    {
        $entityQid = $identifier->qid();
        $entity    = $payload['entities'][$entityQid] ?? null;
        if (!is_array($entity) || array_key_exists('missing', $entity)) {
            foreach ($payload['redirects'] ?? [] as $redirect) {
                $from = $redirect['from'] ?? null;
                $to   = $redirect['to'] ?? null;
                if ($from === $identifier->qid() && is_string($to) && preg_match('/^Q[1-9][0-9]*$/', $to) === 1) {
                    $candidate = $payload['entities'][$to] ?? null;
                    if (is_array($candidate) && !array_key_exists('missing', $candidate)) {
                        $entityQid = $to;
                        $entity    = $candidate;
                    }
                    break;
                }
            }
        }
        if (!is_array($entity) || array_key_exists('missing', $entity)) {
            return null;
        }

        $label       = $this->languageValue($entity['labels'] ?? [], $language);
        $description = $this->languageValue($entity['descriptions'] ?? [], $language);
        $claims      = is_array($entity['claims'] ?? null) ? $entity['claims'] : [];

        return new WikidataEntity(
            $entityQid,
            $label,
            $description,
            $this->entityIds($claims['P31'] ?? []),
            $this->commonsFileName($claims['P18'] ?? []),
        );
    }

    /** @param mixed $values */
    private function languageValue(mixed $values, string $language): ?string
    {
        if (!is_array($values)) {
            return null;
        }

        foreach (array_unique([$language, 'en']) as $candidate) {
            $value = $values[$candidate]['value'] ?? null;
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    /** @param mixed $statements @return list<string> */
    private function entityIds(mixed $statements): array
    {
        if (!is_array($statements)) {
            return [];
        }

        $ids = [];
        foreach ($statements as $statement) {
            $id = $statement['mainsnak']['datavalue']['value']['id'] ?? null;
            if (is_string($id) && preg_match('/^Q[1-9][0-9]*$/', $id) === 1) {
                $ids[$id] = $id;
            }
        }

        return array_values($ids);
    }

    /** @param mixed $statements */
    private function commonsFileName(mixed $statements): ?string
    {
        if (!is_array($statements)) {
            return null;
        }

        foreach ($statements as $statement) {
            $value = $statement['mainsnak']['datavalue']['value'] ?? null;
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return null;
    }
}
