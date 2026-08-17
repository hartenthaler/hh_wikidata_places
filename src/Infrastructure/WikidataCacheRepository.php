<?php

declare(strict_types=1);

namespace Hartenthaler\Webtrees\Module\WikidataPlacesModule\Infrastructure;

use Hartenthaler\Webtrees\Module\WikidataPlacesModule\Domain\WikidataIdentifier;
use Hartenthaler\Webtrees\Module\WikidataPlacesModule\Wikidata\WikidataEntity;
use Illuminate\Database\Capsule\Manager as DB;
use JsonException;

/** Persists a deliberately small, language-specific and expiring entity cache. */
final class WikidataCacheRepository
{
    public const DEFAULT_TTL = 604800; // Seven days.

    public function find(WikidataIdentifier $identifier, string $language): ?WikidataEntity
    {
        $row = DB::table(WikidataCacheSchema::TABLE)
            ->where('qid', '=', $identifier->qid())
            ->where('language', '=', $language)
            ->where('expires_at', '>', time())
            ->first();

        if ($row === null || !is_string($row->payload)) {
            return null;
        }

        try {
            $payload = json_decode($row->payload, true, 16, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        if (!is_array($payload)) {
            return null;
        }

        $qid = $payload['qid'] ?? null;
        if (!is_string($qid) || $qid !== $identifier->qid()) {
            return null;
        }

        return new WikidataEntity(
            $qid,
            is_string($payload['label'] ?? null) ? $payload['label'] : null,
            is_string($payload['description'] ?? null) ? $payload['description'] : null,
            array_values(array_filter($payload['instance_of'] ?? [], 'is_string')),
            is_string($payload['commons_file_name'] ?? null) ? $payload['commons_file_name'] : null,
        );
    }

    public function store(WikidataEntity $entity, string $language, int $ttl = self::DEFAULT_TTL): void
    {
        $now     = time();
        $payload = json_encode([
            'qid'               => $entity->qid,
            'label'             => $entity->label,
            'description'       => $entity->description,
            'instance_of'       => $entity->instanceOfQids,
            'commons_file_name' => $entity->commonsFileName,
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        DB::table(WikidataCacheSchema::TABLE)->updateOrInsert(
            ['qid' => $entity->qid, 'language' => $language],
            [
                'payload'    => $payload,
                'fetched_at' => $now,
                'expires_at' => $now + max(60, $ttl),
            ],
        );
    }
}
