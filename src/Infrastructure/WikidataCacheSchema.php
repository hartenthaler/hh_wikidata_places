<?php

declare(strict_types=1);

namespace Hartenthaler\Webtrees\Module\ExternalPlacesModule\Infrastructure;

use Illuminate\Database\Capsule\Manager as DB;

/** Versioned schema for the module-owned, read-only Wikidata cache. */
final class WikidataCacheSchema
{
    public const CURRENT_VERSION = 1;
    public const TABLE = 'hh_external_places_cache';

    public function ensureSchema(int $currentVersion): int
    {
        if ($currentVersion < 1 && !DB::schema()->hasTable(self::TABLE)) {
            DB::schema()->create(self::TABLE, static function ($table): void {
                $table->increments('id');
                $table->string('qid', 32);
                $table->string('language', 16);
                $table->text('payload');
                $table->unsignedInteger('fetched_at');
                $table->unsignedInteger('expires_at');

                $table->unique(['qid', 'language'], 'hh_external_places_cache_qid_language');
                $table->index('expires_at', 'hh_external_places_cache_expiry');
            });
        }

        return self::CURRENT_VERSION;
    }
}
