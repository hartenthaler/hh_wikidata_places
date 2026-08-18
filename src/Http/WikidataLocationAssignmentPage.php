<?php

declare(strict_types=1);

namespace Hartenthaler\Webtrees\Module\WikidataPlacesModule\Http;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Validator;
use Hartenthaler\Webtrees\Module\WikidataPlacesModule\Gedcom\ExternalIdService;
use Hartenthaler\Webtrees\Module\WikidataPlacesModule\MoreI18N;
use Hartenthaler\Webtrees\Module\WikidataPlacesModule\Wikidata\WikidataClient;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function route;

/** Search and review Wikidata items before explicitly assigning one to a shared place. */
final class WikidataLocationAssignmentPage implements RequestHandlerInterface
{
    use ViewResponseTrait;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $tree     = Validator::attributes($request)->tree();
        $xref     = Validator::attributes($request)->isXref()->string('xref');
        $location = Auth::checkLocationAccess(Registry::locationFactory()->make($xref, $tree), true);
        $language = explode('-', str_replace('_', '-', I18N::languageTag()))[0] ?: 'en';
        $search   = trim(Validator::queryParams($request)->string('search', ''));
        $client   = new WikidataClient();

        $current = (new ExternalIdService())->wikidataIdentifiers($location->gedcom())->identifier();
        $entity  = $current === null ? null : $client->fetch($current, $language);

        return $this->viewResponse('hh_wikidata_places::assignment', [
            'assignment_url' => route('hh-wikidata-places.assignment', ['tree' => $tree->name(), 'xref' => $location->xref()]),
            'candidates'     => $search === '' ? [] : $client->search($search, $language),
            'current'        => $current,
            'entity'         => $entity,
            'location'       => $location,
            'search'         => $search,
            'search_url'     => route('hh-wikidata-places.assignment-page', ['tree' => $tree->name(), 'xref' => $location->xref()]),
            'title'          => MoreI18N::translate('Assign Wikidata item'),
            'tree'           => $tree,
        ]);
    }
}
