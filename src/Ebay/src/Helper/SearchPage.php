<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\Ebay\Helper;

use GuzzleHttp\Cookie\SetCookie;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;

class SearchPage
{
    protected $redirectedUri;

    protected $requestFactory;

    protected $client;

    public function __construct(ClientInterface $client, ServerRequestFactoryInterface $requestFactory, $redirectedUri)
    {
        $this->client = $client;
        $this->redirectedUri = $redirectedUri;
        $this->requestFactory = $requestFactory;
    }

    public function getTrueUri(string $uri)
    {
        return $this->redirectedUri . urlencode($uri);
    }

    public function getCookie(string $uri): array
    {
        $request = $this->requestFactory->createServerRequest('GET', $uri);
        $response = $this->client->sendRequest($request);
        $cookies = [];

        foreach ($response->getHeader('Set-Cookie') as $cookie) {
            $cookie = SetCookie::fromString($cookie);
            $cookies[$cookie->getName()] = $cookie->getValue();
        }

        return $cookies;
    }
}
