<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Loader;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Parser\ProxyManager;
use Parser\UserAgentGenerator;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use rollun\datastore\DataStore\DataStoreException;

class Loader implements LoaderInterface
{
    protected $userAgentGenerator;

    protected $clientFactory;

    protected $proxyManager;

    protected $maxAttempts;

    protected $logger;

    /**
     * Loader constructor.
     * @param UserAgentGenerator $userAgentGenerator
     * @param ProxyManager $proxyManager
     * @param callable $clientFactory
     * @param LoggerInterface $logger
     * @param int $maxAttempt
     */
    public function __construct(
        UserAgentGenerator $userAgentGenerator,
        ProxyManager $proxyManager,
        callable $clientFactory,
        LoggerInterface $logger,
        $maxAttempt = 10
    ) {
        $this->userAgentGenerator = $userAgentGenerator;
        $this->proxyManager = $proxyManager;
        $this->clientFactory = $clientFactory;
        $this->logger = $logger;
        $this->maxAttempts = $maxAttempt;
    }

    /**
     * @param string $uri
     * @return string
     * @throws GuzzleException
     * @throws DataStoreException
     * @throws Exception
     */
    public function load(string $uri): string
    {
        $attempt = 0;
        $proxy = $this->proxyManager->getUnusedProxy();
        $response = $this->createClient($proxy)->request('GET', $uri);

        while ($this->validate($response) && $attempt < $this->maxAttempts) {
            $proxy = $this->proxyManager->getUnusedProxy();
            $this->proxyManager->setUsedProxy($proxy);
            $response = $this->createClient($proxy)->request('GET', $uri);
            $attempt++;
        }

        if (!$this->validate($response)) {
            $this->logger->warning("Can't load html from '$uri'", [
                'status' => $response->getStatusCode(),
                'reasonPhrase' => $response->getReasonPhrase(),
            ]);
        }

        return $response->getBody()->getContents();
    }

    /**
     * @param $proxy
     * @return Client
     * @throws Exception
     */
    protected function createClient($proxy): Client
    {
        return ($this->clientFactory)($proxy, ['User-Agent' => $this->userAgentGenerator->generate()]);
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
    public function validate(ResponseInterface $response)
    {
        return $response->getStatusCode() !== 200;
    }
}
