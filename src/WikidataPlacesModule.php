<?php

declare(strict_types=1);

namespace Hartenthaler\Webtrees\Module\WikidataPlacesModule;

use Fisharebest\Localization\Translation;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\View;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;
use Fisharebest\Webtrees\Module\ModuleCustomTrait;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;
use Fisharebest\Webtrees\Module\ModuleConfigTrait;
use Fisharebest\Webtrees\Services\TreeService;
use Fisharebest\Webtrees\Validator;
use Hartenthaler\Webtrees\Module\WikidataPlacesModule\Infrastructure\WikidataCacheSchema;
use Hartenthaler\Webtrees\Module\WikidataPlacesModule\Infrastructure\WikidataCacheRepository;
use Hartenthaler\Webtrees\Module\WikidataPlacesModule\Gedcom\ExternalIdService;
use Hartenthaler\Webtrees\Module\WikidataPlacesModule\Domus\DomusMapLinkProvider;
use Hartenthaler\Webtrees\Module\WikidataPlacesModule\Http\WikidataLocationAssignmentPage;
use Hartenthaler\Webtrees\Module\WikidataPlacesModule\Wikidata\WikidataClient;
use Hartenthaler\Webtrees\Module\WikidataPlacesModule\Wikidata\NearbyDiscoverySettings;
use Hartenthaler\Webtrees\Module\WikidataPlacesModule\Wikidata\LocationCoordinates;
use Vesta\Model\GenericViewElement;
use Vesta\Model\GovReference;
use Vesta\Model\LocReference;
use Vesta\Model\MapCoordinates;
use Vesta\Model\PlaceStructure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function file_exists;
use function route;

class WikidataPlacesModule extends AbstractModule implements ModuleConfigInterface, ModuleCustomInterface
{
    use ModuleCustomTrait;
    use ModuleConfigTrait;

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

        View::registerNamespace(self::MODULE_NAME, $this->resourcesFolder() . 'views/');
        $router = Registry::routeFactory()->routeMap();
        $router->get(
            'hh-wikidata-places.assignment-page',
            '/tree/{tree}/wikidata-place/{xref}/assignment',
            WikidataLocationAssignmentPage::class,
        )->allows(['GET', 'POST']);
    }

    public function plac2html(PlaceStructure $place): ?GenericViewElement
    {
        $location = $place->getLocation();
        if ($location === null) {
            return null;
        }

        $lookup = (new ExternalIdService())->wikidataIdentifiers($location->gedcom());
        if ($lookup->isAmbiguous()) {
            return GenericViewElement::create('<div class="alert alert-warning">' . e(MoreI18N::translate('Several Wikidata identifiers are configured for this shared place.')) . '</div>');
        }

        $identifier = $lookup->identifier();
        $coordinates = LocationCoordinates::fromGedcom($location->gedcom());
        $domusUrl = (new DomusMapLinkProvider())->url($identifier, $coordinates);
        if ($identifier === null) {
            if (!$location->canEdit()) {
                return null;
            }

            return GenericViewElement::create(
                '<br><br><strong>' . e(MoreI18N::translate('Wikidata')) . ':</strong><br>'
                . '<a class="btn btn-primary btn-sm mt-2" href="' . e(route('hh-wikidata-places.assignment-page', ['tree' => $location->tree()->name(), 'xref' => $location->xref()])) . '">' . e(MoreI18N::translate('Assign Wikidata item')) . '</a>'
                . '<a class="btn btn-primary btn-sm mt-2 ms-2" href="' . e($domusUrl) . '" rel="noopener noreferrer" target="_blank">' . e(MoreI18N::translate('Show in Domus')) . '</a>'
            );
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
        $html  = '<br><br><strong>' . e(MoreI18N::translate('Wikidata')) . ':</strong> ';
        $html .= '<a href="' . e($identifier->entityUrl()) . '" rel="noopener noreferrer" target="_blank">' . e($label) . '</a> (' . e($identifier->qid()) . ')';
        if ($entity?->description !== null) {
            $html .= ' — ' . e($entity->description);
        }
        if ($entity !== null && $entity->instanceOfQids !== []) {
            $typeLabels = (new WikidataClient())->labels($entity->instanceOfQids, $language);
            $types      = array_map(static fn (string $qid): string => $typeLabels[$qid] ?? $qid, $entity->instanceOfQids);
            $html .= '<br>' . e(I18N::translate('Type')) . ': ' . e(implode(', ', $types));
        }
        if ($entity?->commonsFileName !== null) {
            $fileUrl = 'https://commons.wikimedia.org/wiki/Special:FilePath/' . rawurlencode($entity->commonsFileName);
            $html .= '<br><a href="' . e($fileUrl) . '" rel="noopener noreferrer" target="_blank">' . e(MoreI18N::translate('Image on Wikimedia Commons')) . '</a>';
        }
        if ($entity === null) {
            $html .= ' — <small>' . e(MoreI18N::translate('Wikidata details are currently unavailable.')) . '</small>';
        }
        $html .= '<br><small>' . e(MoreI18N::translate('Source')) . ': Wikidata</small>';
        $html .= '<br><a class="btn btn-primary btn-sm mt-2" href="' . e($domusUrl) . '" rel="noopener noreferrer" target="_blank">' . e(MoreI18N::translate('Show in Domus')) . '</a>';

        if ($location->canEdit()) {
            $html .= '<br><a class="btn btn-primary btn-sm mt-2" href="' . e(route('hh-wikidata-places.assignment-page', ['tree' => $location->tree()->name(), 'xref' => $location->xref()])) . '">' . e(MoreI18N::translate('Assign Wikidata item')) . '</a>';
        }

        return GenericViewElement::create($html);
    }

    public function gov2html(GovReference $gov, Tree $tree): ?GenericViewElement
    {
        return null;
    }

    public function map2html(MapCoordinates $map): ?GenericViewElement
    {
        return null;
    }

    public function loc2linkIcon(LocReference $loc): ?string
    {
        return null;
    }

    public function title(): string
    {
        return MoreI18N::translate('Wikidata Places');
    }

    public function description(): string
    {
        return MoreI18N::translate('Links shared places to Wikidata and exposes read-only Wikidata data and Domus links.');
    }

    public function getAdminAction(): ResponseInterface
    {
        $this->layout = 'layouts/administration';

        return $this->viewResponse(self::MODULE_NAME . '::configuration', [
            'all_trees' => app(TreeService::class)->all(),
            'default_radius_km' => NearbyDiscoverySettings::DEFAULT_RADIUS_KM,
            'preference' => NearbyDiscoverySettings::PREFERENCE,
            'title' => $this->title(),
        ]);
    }

    public function postAdminAction(ServerRequestInterface $request): ResponseInterface
    {
        foreach (app(TreeService::class)->all() as $tree) {
            $radius = Validator::parsedBody($request)->string('nearby-radius-' . $tree->id(), (string) NearbyDiscoverySettings::DEFAULT_RADIUS_KM);
            $tree->setPreference(NearbyDiscoverySettings::PREFERENCE, (string) NearbyDiscoverySettings::normalise($radius));
        }

        FlashMessages::addMessage(MoreI18N::translate('Nearby search settings have been updated.'), 'success');

        return redirect($this->getConfigLink());
    }

    /**
     * Privacy information consumed opportunistically by hh_legal_notice.
     *
     * There is intentionally no interface dependency: Wikidata Places must
     * also load on installations that do not use the Legal Notice module.
     *
     * @return array{
     *     third_party_services:list<array{
     *         service_id:string,
     *         name:string,
     *         url:string,
     *         country:string,
     *         privacy_url:string,
     *         description:string,
     *         data:list<string>
     *     }>,
     *     security_measures:list<string>
     * }
     */
    public function privacyNotices(): array
    {
        return [
            'third_party_services' => [[
                'service_id'  => 'wikimedia-foundation',
                'name'        => 'Wikimedia Foundation (Wikidata)',
                'url'         => 'https://www.wikidata.org/',
                'country'     => 'United States',
                'privacy_url' => 'https://foundation.wikimedia.org/wiki/Policy:Privacy_policy',
                'description' => MoreI18N::translate('The module retrieves public place information from Wikidata. Editors can also search Wikidata to assign an item to a shared place.'),
                'data'        => [
                    MoreI18N::translate('Wikidata item identifiers and the requested display language.'),
                    MoreI18N::translate('Search text entered by an editor.'),
                    MoreI18N::translate('Coordinates and the configured radius for an editor-requested nearby search.'),
                    MoreI18N::translate('The server IP address and technical request metadata.'),
                ],
            ]],
            'security_measures' => [
                MoreI18N::translate('Wikidata responses are cached locally to reduce external requests.'),
            ],
        ];
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
