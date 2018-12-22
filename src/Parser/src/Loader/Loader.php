<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Loader;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Parser\UserAgentGenerator;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use rollun\datastore\DataStore\Interfaces\DataStoresInterface;
use rollun\datastore\Rql\RqlQuery;
use RuntimeException;

class Loader implements LoaderInterface
{
    const DEF_MAX_ATTEMPTS = 10;

    protected $userAgentGenerator;

    protected $proxies;

    protected $options;

    /**
     * Loader constructor.
     * @param UserAgentGenerator $userAgentGenerator
     * @param DataStoresInterface $proxyDataStore
     * @param array $options
     */
    public function __construct(
        UserAgentGenerator $userAgentGenerator,
        DataStoresInterface $proxyDataStore,
        array $options = []
    ) {
        $this->userAgentGenerator = $userAgentGenerator;
        $this->proxies = $proxyDataStore;
        $this->setOptions($options);
    }

    public function setOptions($options, $override = true)
    {
        foreach ($options as $key => $option) {
            if ($override || !isset($this->options[$key])) {
                $this->options[$key] = $option;
            }
        }
    }

    /**
     * @param string $uri
     * @return string
     * @throws GuzzleException
     */
    public function load(string $uri): string
    {
        $maxAttempts = $this->options['maxProxy'] ?? self::DEF_MAX_ATTEMPTS;
        $attempt = 0;

        $response = $this->createClient()->request('GET', $uri);

        while (!$this->validate($response) && $attempt < $maxAttempts) {
            $response = $this->createClient()->request('GET', $uri);
            $attempt++;
        }

        if (!$this->validate($response)) {
            throw new RuntimeException("Can't load html from '$uri'. Reason: {$response->getReasonPhrase()}");
        }

        return $response->getBody()->getContents();
    }

    protected function generateProxy()
    {
        $proxy = $this->getUnusedProxy();
        $this->setUsedProxy($proxy);

        return $proxy;
    }

    /**
     * @return Client
     */
    protected function createClient(): Client
    {
        $proxy = empty($this->options['usedProxy']) ? null : $this->generateProxy();
        $userAgent = empty($this->options['fakeUserAgent']) ? null : $this->userAgentGenerator->generate();

        return new Client([
            'proxy' => $proxy,
            'headers' => [
                'User-Agent' => $userAgent
            ],
        ]);
    }

    /**
     * @param RequestInterface $request
     * @return string
     */
    protected function createUrl(RequestInterface $request): string
    {
        $uri = $request->getUri();
        $path = ltrim($uri->getPath(), '/');

        return "{$uri->getScheme()}://{$uri->getHost()}:{$uri->getPort()}/{$path}";
    }

    /**
     * @param ResponseInterface $response
     * @return bool
     */
    public function validate(ResponseInterface $response): bool
    {
        return $response->getStatusCode() === 200;
    }

    public function getUnusedProxy()
    {
        $proxies = $this->proxies->query(new RqlQuery('eqf(is_used)&limit(1)'));

        if (!count($proxies)) {
            throw new RuntimeException('Unused proxies run out');
        }

        $proxy = current($proxies);

        return $proxy['uri'];
    }

    public function setUsedProxy(string $uri)
    {
        $proxies = $this->proxies->query(new RqlQuery("eq(uri,{$uri})&limit(1)"));
        $proxy = current($proxies);
        $this->proxies->update([
            'id' => $proxy['id'],
            'is_used' => 1,
        ]);
    }
}
