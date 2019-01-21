<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\Loader\Loader;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Log\LoggerInterface;
use ReflectionException;
use rollun\dic\InsideConstruct;
use rollun\service\Parser\FreeProxyList\ProxyManager;
use Zend\Validator\ValidatorInterface;

class Base implements LoaderInterface
{
    protected $proxySystem;

    protected $options;

    protected $requestFactory;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Base constructor.
     * @param ProxyManager $proxySystem
     * @param ServerRequestFactoryInterface $requestFactory
     * @param ValidatorInterface $validator
     * @param array $options
     * @param LoggerInterface|null $logger
     * @throws ReflectionException
     */
    public function __construct(
        ProxyManager $proxySystem,
        ServerRequestFactoryInterface $requestFactory,
        ValidatorInterface $validator,
        array $options = [],
        LoggerInterface $logger = null
    ) {
        InsideConstruct::setConstructParams(['logger' => LoggerInterface::class,]);

        $this->validator = $validator;
        $this->proxySystem = $proxySystem;
        $this->requestFactory = $requestFactory;
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
     * @throws ClientException
     */
    public function load(string $uri): string
    {
        $this->logger->debug("Try to load page '{$uri}'");
        $request = $this->requestFactory->createServerRequest('GET', $uri);
        $response = $this->sendRequest($request);

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
        $attempt = 1;

        do {
            $attempt++;
            $startTime = microtime(true);
            $proxy = $this->getProxy();
            $client = $this->createClient($proxy);

            try {
                $this->logger->debug('Sent http request using Guzzlehttp', [
                    'uri' => $uri,
                    'proxy' => $proxy,
                    'start_time' => date('d.m H:i:s', intval($startTime)),
                ]);
                $response = $client->request($method, $uri);
                $endTime = microtime(true);
                $this->logger->debug('Fetching http response using Guzzlehttp', [
                    'uri' => $uri,
                    'proxy' => $proxy,
                    'end_time' => date('d.m H:i:s', intval($endTime)),
                ]);
                $this->proxySystem->changeLevel($proxy, $endTime - $startTime);

            } catch (RequestException $e) {
                $this->logger->debug('Failed to fetch http response using Guzzlehttp', [
                    'exception' => $e,
                    'uri' => $uri,
                    'proxy' => $proxy,
                ]);
                $response = $e->getResponse();
                $this->proxySystem->failed($proxy);
            }
        } while ((!$this->validator->isValid($response) && $attempt < $maxAttempts));

        if (!$this->validator->isValid($response)) {
            throw new ClientException("Can't fetch response using {$attempt} attempts", $request);
        }

        return $response;
    }

    protected function getProxy()
    {
        $useProxy = $this->options[self::USE_PROXY_OPTION] ?? false;

        if (!$useProxy) {
            return null;
        }

        $createTaskIfNoExist = (bool)$this->options[self::CREATE_TASK_IF_NO_PROXIES_OPTION] ??
            self::DEF_CREATE_TASK_IF_NO_PROXIES;

        if (!$proxy = $this->proxySystem->get($createTaskIfNoExist)) {
            throw LoaderException::createProxyRunOutException($createTaskIfNoExist);
        }

        return $proxy;
    }

    /**
     * Return true if valid
     *
     * @param ResponseInterface $response
     * @return bool
     */
    protected function validateResponse(ResponseInterface $response = null)
    {
        return isset($response)
            && $response->getStatusCode() < 400;
    }

    /**
     * @param $proxy
     * @return Client
     */
    protected function createClient($proxy = null): Client
    {
        $options = [];

        if (!empty($this->options[self::FAKE_USER_AGENT_OPTION])) {
            $userAgent = (\Faker\Factory::create())->chrome;
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
            $options['allow_redirects'] = boolval($this->options[self::ALLOW_REDIRECT_OPTION]);
        }

        if (isset($this->options[self::CONNECTION_TIMEOUT_OPTION])) {
            $options['connect_timeout'] = $this->options[self::CONNECTION_TIMEOUT_OPTION];
        }

        $this->logger->debug("Create client", [
            'options' => $options,
        ]);

        $this->logger->debug('Create Guzzlehttp client with options', [
            'options' => $options,
        ]);

        return new Client($options);
    }

    public function __sleep()
    {
        return ['proxySystem', 'options', 'requestFactory', 'validator'];
    }

    public function __wakeup()
    {
        InsideConstruct::initWakeup(['logger' => LoggerInterface::class]);
    }
}
