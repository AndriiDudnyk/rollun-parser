<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Loader;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Parser\UserAgentGenerator;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use rollun\datastore\DataStore\Interfaces\DataStoresInterface;
use rollun\datastore\Rql\RqlQuery;
use rollun\dic\InsideConstruct;
use RuntimeException;
use Xiag\Rql\Parser\Node\LimitNode;
use Xiag\Rql\Parser\Node\Query\ScalarOperator\EqNode;

class Loader implements LoaderInterface
{
    const DEF_MAX_ATTEMPTS = 10;

    protected $userAgentGenerator;

    protected $proxies;

    protected $options;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Loader constructor.
     * @param UserAgentGenerator $userAgentGenerator
     * @param DataStoresInterface $proxyDataStore
     * @param array $options
     * @param LoggerInterface|null $logger
     * @throws \ReflectionException
     */
    public function __construct(
        UserAgentGenerator $userAgentGenerator,
        DataStoresInterface $proxyDataStore,
        array $options = [],
        LoggerInterface $logger = null
    ) {
        InsideConstruct::setConstructParams(['logger' => LoggerInterface::class]);
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

        if (!empty($this->options['useProxy'])) {
            $proxy = $this->getUnusedProxy();
        } else {
            $proxy = null;
        }

        try {
            $response = $this->createClient($proxy)->request('GET', $uri);
        } catch (RequestException $e) {
            $this->logger->debug('Failed to fetch request', [
                'exception' => $e
            ]);
            $response = $e->getResponse();
        }

        while (!$this->validate($response) && $attempt < $maxAttempts) {
            if (!empty($this->options['useProxy'])) {
                $this->setUsedProxy($proxy);
                $proxy = $this->getUnusedProxy();
            } else {
                $proxy = null;
            }

            try {
                $response = $this->createClient($proxy)->request('GET', $uri);
            } catch (RequestException $e) {
                $this->logger->debug('Failed to fetch request', [
                    'exception' => $e
                ]);
                $response = $e->getResponse();
            }

            $attempt++;
        }

        if (!$this->validate($response)) {
            throw new RuntimeException("Can't load html from '$uri'. Reason: {$response->getReasonPhrase()}");
        }

        return $response->getBody()->getContents();
    }

    /**
     * @param $proxy
     * @return Client
     */
    protected function createClient($proxy = null): Client
    {
        if (!empty($this->options['fakeUserAgent'])) {
            $userAgent = $this->userAgentGenerator->generate($this->options['fakeUserOS'] ?? null);
        } else {
            $userAgent = null;
        }

        if ($userAgent) {
            $options['headers']['User-Agent'] = $userAgent;
        }

        if (!empty($this->options['cookies'])) {
            $domain = $this->options['cookie_domain'] ?? null;
            $options['cookies'] = CookieJar::fromArray($this->options['cookies'], $domain);
        }

        if ($proxy) {
            $options['proxy'] = $proxy;
        }

        $options['allow_redirects'] = true;

        return new Client($options);
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
    public function validate(ResponseInterface $response = null): bool
    {
        return isset($response) && $response->getStatusCode() === 200;
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
        $query = new RqlQuery();
        $query->setQuery(new EqNode('uri', $uri));
        $query->setLimit(new LimitNode(1));

        $proxies = $this->proxies->query($query);
        $proxy = current($proxies);
        $this->proxies->update([
            'id' => $proxy['id'],
            'is_used' => 1,
        ]);
    }

    public function __sleep()
    {
        return ['proxies', 'options', 'userAgentGenerator'];
    }

    public function __wakeup()
    {
        InsideConstruct::initWakeup(['logger' => LoggerInterface::class]);
    }
}
