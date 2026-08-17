<?php

declare(strict_types=1);

namespace Hartenthaler\Webtrees\Module\WikidataPlacesModule\Wikidata;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Hartenthaler\Webtrees\Module\WikidataPlacesModule\Domain\WikidataIdentifier;
use JsonException;

/**
 * Small, read-only client for the public Wikidata entity endpoint.
 *
 * The endpoint, query parameters, timeout and response size are fixed so a
 * GEDCOM identifier can never become an arbitrary outbound request.
 */
final class WikidataClient
{
    private const ENDPOINT = 'https://www.wikidata.org/w/api.php';
    private const MAX_RESPONSE_BYTES = 1_000_000;

    public function __construct(
        private readonly ClientInterface $httpClient = new Client(),
        private readonly WikidataEntityMapper $mapper = new WikidataEntityMapper(),
    ) {
    }

    public function fetch(WikidataIdentifier $identifier, string $language): ?WikidataEntity
    {
        $language = $this->language($language);

        try {
            $response = $this->httpClient->request('GET', self::ENDPOINT, [
                'allow_redirects' => false,
                'connect_timeout' => 3.0,
                'headers'         => [
                    'Accept'     => 'application/json',
                    'User-Agent' => 'webtrees Wikidata Places/0.1 (https://github.com/hartenthaler/hh_wikidata_places)',
                ],
                'http_errors'     => false,
                'query'           => [
                    'action'        => 'wbgetentities',
                    'format'        => 'json',
                    'formatversion' => '2',
                    'ids'           => $identifier->qid(),
                    'languages'     => $language . '|en',
                    'props'         => 'labels|descriptions|claims',
                ],
                'timeout'         => 6.0,
            ]);
        } catch (GuzzleException) {
            return null;
        }

        if ($response->getStatusCode() !== 200) {
            return null;
        }

        $body = $response->getBody()->getContents();
        if (strlen($body) > self::MAX_RESPONSE_BYTES) {
            return null;
        }

        try {
            $payload = json_decode($body, true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        return is_array($payload) ? $this->mapper->map($identifier, $payload, $language) : null;
    }

    private function language(string $language): string
    {
        $language = str_replace('_', '-', trim($language));
        if (preg_match('/^([a-z]{2,3})(?:-[A-Za-z]{2,4})?$/', $language, $matches) !== 1) {
            return 'en';
        }

        // Wikidata labels use language codes such as "de" rather than webtrees'
        // regional UI tags such as "de-DE".
        return $matches[1];
    }
}
