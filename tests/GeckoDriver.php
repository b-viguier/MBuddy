<?php

declare(strict_types=1);

namespace Bveing\MBuddy\Tests;

use Amp\Http\Client\HttpClient;
use Amp\Http\Client\HttpClientBuilder;
use Amp\Promise;
use Amp\Success;
use Amp\Http\Client\Request;

use function Amp\call;

class GeckoDriver
{
    private HttpClient $httpClient;
    private ?string $sessionId = null;

    public function __construct(
        private string $endpoint,
    ) {
        $this->httpClient = HttpClientBuilder::buildDefault();
    }

    /**
     * @return Promise<null>
     */
    public function start(): Promise
    {
        if ($this->sessionId !== null) {
            return new Success();
        }

        return call(function() {

            $response = yield $this->httpClient->request(
                new Request(
                    $this->endpoint.'/session',
                    'POST',
                    json_encode(
                        [
                            'capabilities' => [
                                'firstMatch' => [
                                    [
                                        'browserName' => 'firefox',
                                        'moz:firefoxOptions' => [
                                            'prefs' => [
                                                'reader.parse-on-load.enabled' => false,
                                                'devtools.jsonview.enabled' => false,
                                            ],
                                            'args' => [
                                                '-headless',
                                                '-window-size=1200,1100',
                                                '-devtools',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        JSON_THROW_ON_ERROR,
                    ),
                ),
            );

            $body = yield $response->getBody()->buffer();
            /** @var array{'value':null|array{'sessionId':string}} $jsonResponse */
            $jsonResponse = json_decode($body, associative: true, flags: JSON_THROW_ON_ERROR);
            $this->sessionId = $jsonResponse['value']['sessionId'] ?? throw new \Exception("Cannot retrieve sessionId");

            return null;
        });
    }

    /**
     * @return Promise<null>
     */
    public function stop(): Promise
    {
        if ($this->sessionId === null) {
            return new Success();
        }

        return call(function() {
            yield $this->httpClient->request(
                new Request(
                    $this->endpoint.'/session/'.$this->sessionId,
                    'DELETE',
                ),
            );
        });
    }

    /**
     * @return Promise<null>
     */
    public function navigateTo(string $url): Promise
    {
        return call(function() use ($url) {
            yield $this->httpClient->request(
                new Request(
                    $this->endpoint.'/session/'.$this->sessionId.'/url',
                    'POST',
                    json_encode(
                        [
                            'url' => $url,
                        ],
                        JSON_THROW_ON_ERROR,
                    ),
                ),
            );
        });
    }

    /**
     * @return Promise<null>
     */
    public function refresh(): Promise
    {
        return call(function() {
            yield $this->httpClient->request(
                new Request(
                    $this->endpoint.'/session/'.$this->sessionId.'/refresh',
                    'POST',
                ),
            );
        });
    }

    /**
     * @return Promise<string>
     */
    public function getTitle(): Promise
    {
        return call(function() {
            $response = yield $this->httpClient->request(
                new Request(
                    $this->endpoint.'/session/'.$this->sessionId.'/title',
                    'GET',
                ),
            );

            $body = yield $response->getBody()->buffer();
            /** @var array{'value':string|null} $jsonResponse */
            $jsonResponse = json_decode($body, associative: true, flags: JSON_THROW_ON_ERROR);

            return $jsonResponse['value'] ?? throw new \Exception("Cannot retrieve title");
        });
    }

    /**
     * @return Promise<string>
     */
    public function takeScreenshot(): Promise
    {
        return call(function() {
            $response = yield $this->httpClient->request(
                new Request(
                    $this->endpoint.'/session/'.$this->sessionId.'/screenshot',
                    'GET',
                ),
            );

            $body = yield $response->getBody()->buffer();
            /** @var array{'value':string|null} $jsonResponse */
            $jsonResponse = json_decode($body, associative: true, flags: JSON_THROW_ON_ERROR);

            return base64_decode($jsonResponse['value'] ?? throw new \Exception("Cannot retrieve screenshot"));
        });
    }

    /**
     * @return Promise<string>
     */
    public function findElement(string $selector): Promise
    {
        return call(function() use ($selector) {
            $response = yield $this->httpClient->request(
                new Request(
                    $this->endpoint.'/session/'.$this->sessionId.'/element',
                    'POST',
                    json_encode(
                        [
                            'using' => 'css selector',
                            'value' => $selector,
                        ],
                        JSON_THROW_ON_ERROR,
                    ),
                ),
            );

            $body = yield $response->getBody()->buffer();
            /** @var array{'value':null|array{'ELEMENT':string}} $jsonResponse */
            $jsonResponse = json_decode($body, associative: true, flags: JSON_THROW_ON_ERROR);

            return $jsonResponse['value']['ELEMENT'] ?? throw new \Exception("Cannot retrieve element content");
        });
    }
}
