<?php

declare(strict_types=1);

namespace Hartenthaler\Webtrees\Module\WikidataPlacesModule;

use Fisharebest\Localization\Translation;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;
use Fisharebest\Webtrees\Module\ModuleCustomTrait;
use Hartenthaler\Webtrees\Module\WikidataPlacesModule\Infrastructure\WikidataCacheSchema;
use Hartenthaler\Webtrees\Module\WikidataPlacesModule\Infrastructure\WikidataCacheRepository;
use Hartenthaler\Webtrees\Module\WikidataPlacesModule\Gedcom\ExternalIdService;
use Hartenthaler\Webtrees\Module\WikidataPlacesModule\Wikidata\WikidataClient;
use Vesta\Hook\HookInterfaces\EmptyPrintFunctionsPlace;
use Vesta\Hook\HookInterfaces\PrintFunctionsPlaceInterface;
use Vesta\Model\GenericViewElement;
use Vesta\Model\PlaceStructure;

use function file_exists;

class WikidataPlacesModule extends AbstractModule implements ModuleCustomInterface, PrintFunctionsPlaceInterface
{
    use ModuleCustomTrait;
    use EmptyPrintFunctionsPlace;

    private const MODULE_NAME = 'hh_wikidata_places';
    private const GITHUB_USER = 'hartenthaler';
    private const CACHE_SCHEMA_VERSION_PREFERENCE = 'wikidata_cache_schema_version';

    public function boot(): void
    {
        $currentVersion = (int) $this->getPreference(self::CACHE_SCHEMA_VERSION_PREFERENCE, '0');
        $targetVersion  = (new WikidataCacheSchema())->ensureSchema($currentVersion);

        if ($targetVersion !== $currentVersion) {
            $this->setPreference(self::CACHE_SCHEMA_VERSION_PREFERENCE, (string) $targetVersion);
        }
    }

    public function plac2html(PlaceStructure $place): ?GenericViewElement
    {
        $location = $place->getLocation();
        if ($location === null) {
            return null;
        }

        $lookup = (new ExternalIdService())->wikidataIdentifiers($location->gedcom());
        if ($lookup->isAmbiguous()) {
            return GenericViewElement::create('<div class="alert alert-warning">' . e(I18N::translate('Several Wikidata identifiers are configured for this shared place.')) . '</div>');
        }

        $identifier = $lookup->identifier();
        if ($identifier === null) {
            return null;
        }

        $language = explode('-', str_replace('_', '-', I18N::languageTag()))[0] ?: 'en';
        $cache    = new WikidataCacheRepository();
        $entity   = $cache->find($identifier, $language);
        if ($entity === null) {
            $entity = (new WikidataClient())->fetch($identifier, $language);
            if ($entity !== null) {
                $cache->store($entity, $language);
            }
        }

        $label = $entity?->label ?? $identifier->qid();
        $html  = '<section class="wt-wikidata-places mt-3">';
        $html .= '<h3>' . e(I18N::translate('Wikidata')) . '</h3>';
        $html .= '<p><a href="' . e($identifier->entityUrl()) . '" rel="noopener noreferrer" target="_blank">' . e($label) . '</a> (' . e($identifier->qid()) . ')</p>';
        if ($entity?->description !== null) {
            $html .= '<p>' . e($entity->description) . '</p>';
        }
        if ($entity?->instanceOfQids !== []) {
            $html .= '<p><strong>' . e(I18N::translate('Type')) . ':</strong> ' . e(implode(', ', $entity->instanceOfQids)) . '</p>';
        }
        if ($entity?->commonsFileName !== null) {
            $fileUrl = 'https://commons.wikimedia.org/wiki/Special:FilePath/' . rawurlencode($entity->commonsFileName);
            $html .= '<p><a href="' . e($fileUrl) . '" rel="noopener noreferrer" target="_blank">' . e(I18N::translate('Image on Wikimedia Commons')) . '</a></p>';
            $html .= '<p class="small text-muted">' . e(I18N::translate('Image source and licence: Wikimedia Commons')) . '</p>';
        }
        $html .= '<p class="small text-muted">' . e(I18N::translate('Source: Wikidata')) . '</p></section>';

        return GenericViewElement::create($html);
    }

    public function title(): string
    {
        return I18N::translate('Wikidata Places');
    }

    public function description(): string
    {
        return I18N::translate('Links shared places to Wikidata and exposes read-only Wikidata data and Domus links.');
    }

    public function customModuleAuthorName(): string
    {
        return 'Hermann Hartenthaler';
    }

    public function customModuleVersion(): string
    {
        return trim((string) file_get_contents(__DIR__ . '/../version.txt'));
    }

    public function customModuleLatestVersionUrl(): string
    {
        return 'https://raw.githubusercontent.com/' . self::GITHUB_USER . '/' . self::MODULE_NAME . '/main/version.txt';
    }

    public function customModuleSupportUrl(): string
    {
        return 'https://github.com/' . self::GITHUB_USER . '/' . self::MODULE_NAME;
    }

    public function resourcesFolder(): string
    {
        return __DIR__ . '/../resources/';
    }

    public function customTranslations(string $language): array
    {
        $file = $this->resourcesFolder() . 'lang/' . $language . '.mo';
        return file_exists($file) ? (new Translation($file))->asArray() : [];
    }
}
