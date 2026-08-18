<?php

declare(strict_types=1);

namespace Hartenthaler\Webtrees\Module\WikidataPlacesModule\Http;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\Http\Exceptions\HttpBadRequestException;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Validator;
use Hartenthaler\Webtrees\Module\WikidataPlacesModule\Domain\WikidataIdentifier;
use Hartenthaler\Webtrees\Module\WikidataPlacesModule\Gedcom\WikidataLocationAssignmentService;
use Hartenthaler\Webtrees\Module\WikidataPlacesModule\MoreI18N;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function redirect;

/** Handles a CSRF-protected Wikidata assignment submitted for one shared place. */
final class WikidataLocationAssignmentAction implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $tree = Validator::attributes($request)->tree();
        $xref = Validator::attributes($request)->isXref()->string('xref');

        // This both checks the tree-bound record and acquires webtrees' edit lock.
        $location = Auth::checkLocationAccess(Registry::locationFactory()->make($xref, $tree), true);
        $operation = Validator::parsedBody($request)->string('operation', 'assign');
        $service   = new WikidataLocationAssignmentService();

        if ($operation === 'remove') {
            $service->remove($location);
            FlashMessages::addMessage(MoreI18N::translate('The Wikidata item has been removed.'), 'success');
        } elseif ($operation === 'assign') {
            $qid        = Validator::parsedBody($request)->string('qid', '');
            $identifier = WikidataIdentifier::tryFrom($qid);

            if ($identifier === null) {
                throw new HttpBadRequestException(MoreI18N::translate('Invalid Wikidata identifier.'));
            }

            $service->assign($location, $identifier);
            FlashMessages::addMessage(MoreI18N::translate('The Wikidata item has been assigned.'), 'success');
        } else {
            throw new HttpBadRequestException(MoreI18N::translate('Invalid Wikidata assignment operation.'));
        }

        return redirect($location->url());
    }
}
