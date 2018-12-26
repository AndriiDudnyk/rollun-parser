<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Loader;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\RequestException;
use Parser\DataStore\Proxy;
use Parser\UserAgentGenerator;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Log\LoggerInterface;
use ReflectionException;
use rollun\dic\InsideConstruct;
use RuntimeException;

class Loader implements LoaderInterface
{
    const DEF_MAX_ATTEMPTS = 1;
    const DEF_USER_AGENT_OS = 'linux';
    const DEF_CREATE_TASK_IF_NO_PROXIES = false;

    // Options
    const MAX_ATTEMPTS_OPTION = 'maxAttempts'; // int
    const USE_PROXY_OPTION = 'useProxy'; // bool
    const FAKE_USER_AGENT_OPTION = 'fakeUserAgent'; // bool
    const FAKE_USER_AGENT_OS_OPTION = 'fakeUserAgentOS'; // 'linux' or 'windows' or 'mac'
    const COOKIES_OPTION = 'cookies'; // array
    const COOKIE_DOMAIN_OPTION = 'cookieDomain'; // string
    const ALLOW_REDIRECT_OPTION = 'allowRedirect'; // bool
    const CONNECTION_TIMEOUT_OPTION = 'connectionTimeout'; // int
    const CREATE_TASK_IF_NO_PROXIES_OPTION = 'createTaskIfNoProxy';

    protected $userAgentGenerator;

    protected $proxyDataStore;

    protected $options;

    protected $requestFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Loader constructor.
     * @param UserAgentGenerator $userAgentGenerator
     * @param Proxy $proxyDataStore
     * @param ServerRequestFactoryInterface $requestFactory
     * @param array $options
     * @param LoggerInterface|null $logger
     * @throws ReflectionException
     */
    public function __construct(
        UserAgentGenerator $userAgentGenerator,
        Proxy $proxyDataStore,
        ServerRequestFactoryInterface $requestFactory,
        array $options = [],
        LoggerInterface $logger = null
    ) {
        InsideConstruct::setConstructParams(['logger' => LoggerInterface::class]);

        $this->userAgentGenerator = $userAgentGenerator;
        $this->proxyDataStore = $proxyDataStore;
        $this->setOptions($options);
        $this->requestFactory = $requestFactory;
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
     * @throws ClientException
     */
    public function load(string $uri): string
    {
        $request = $this->requestFactory->createServerRequest('GET', $uri);
        $response = $this->sendRequest($request);

        if ($response->getStatusCode() != 200) {
            throw new RuntimeException("Can't load html from '$uri'. Reason: {$response->getReasonPhrase()}");
        }

        return $response->getBody()->getContents();
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws ClientException
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $uri = $request->getUri()->__toString();
        $method = $request->getMethod();

        $maxAttempts = $this->options[self::MAX_ATTEMPTS_OPTION] ?? self::DEF_MAX_ATTEMPTS;
        $attempt = 0;

        do {
            $attempt++;
            $proxy = $this->changeProxy($proxy ?? null);

            try {
                $response = $this->createClient($proxy)->request($method, $uri);
            } catch (RequestException $e) {
                $this->logger->debug('Failed to fetch request', [
                    'exception' => $e
                ]);
                $response = $e->getResponse();
            }
        } while (!$response && $attempt < $maxAttempts);

        if (!$response) {
            throw new ClientException("Can't fetch response using {$attempt} attempts", $request);
        }

        return $response;
    }

    /**
     * @param null $oldProxyUri
     * @return string|null
     */
    protected function changeProxy($oldProxyUri = null)
    {
        $useProxy = $this->options[self::USE_PROXY_OPTION] ?? false;

        if (!$useProxy) {
            return null;
        }

        if ($oldProxyUri) {
            $this->proxyDataStore->setUsedProxy($oldProxyUri);
        }

        $createTaskIfNoExist = (bool)$this->options[self::CREATE_TASK_IF_NO_PROXIES_OPTION] ??
            self::DEF_CREATE_TASK_IF_NO_PROXIES;

        if (!$newProxyUri = $this->proxyDataStore->getUnusedProxy($createTaskIfNoExist)) {
            $addMessage = $createTaskIfNoExist ? ', and add new task for proxy parser' : '';
            throw new RuntimeException("Unused proxies run out" . $addMessage);
        }

        return $newProxyUri;
    }

    /**
     * @param $proxy
     * @return Client
     */
    protected function createClient($proxy = null): Client
    {
        $options = [];

        if (!empty($this->options[self::FAKE_USER_AGENT_OPTION])) {
            $userAgent = $this->userAgentGenerator->generate(
                $this->options[self::FAKE_USER_AGENT_OS_OPTION] ?? self::DEF_USER_AGENT_OS
            );
            $options['headers']['User-Agent'] = $userAgent;
        }

        $cookies = $this->options[self::COOKIES_OPTION] ?? [];
        if ($cookies) {
            $domain = $this->options[self::COOKIE_DOMAIN_OPTION] ?? null;
            $options['cookies'] = CookieJar::fromArray($cookies, $domain);
        }

        if ($proxy) {
            $options['proxy'] = $proxy;
        }

        if (isset($this->options[self::ALLOW_REDIRECT_OPTION])) {
            $options['allow_redirects'] = true;
        }

        if (isset($this->options[self::CONNECTION_TIMEOUT_OPTION])) {
            $options['connect_timeout'] = $this->options[self::CONNECTION_TIMEOUT_OPTION];
        }

        return new Client($options);
    }

    public function __sleep()
    {
        return ['proxyDataStore', 'options', 'userAgentGenerator', 'requestFactory'];
    }

    public function __wakeup()
    {
        InsideConstruct::initWakeup(['logger' => LoggerInterface::class]);
    }
}
