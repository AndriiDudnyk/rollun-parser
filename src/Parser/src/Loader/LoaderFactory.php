<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Loader;

use GuzzleHttp\Client;
use InvalidArgumentException;
use Parser\ProxyManager;
use Parser\UserAgentGenerator;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class LoaderFactory
{
    const KEY = 'proxyManagerConfig';

    const KEY_MAX_ATTEMPTS = 'maxAttempts';

    const KEY_PROXIES = 'proxies';

    public function __construct(ContainerInterface $container)
    {
        $userAgentGenerator = $container->get(UserAgentGenerator::class);
        $proxyManager = $container->get(ProxyManager::class);
        $logger = $container->get(LoggerInterface::class);

        $clientFactory = function ($proxy, $headers) {
            return new Client([
                'proxy' => $proxy,
                'headers' => $headers
            ]);
        };

        $serviceConfig = $container->get('config')[self::KEY] ?? [];

        if (!isset($serviceConfig[self::KEY_PROXIES])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_PROXIES . "'");
        }

        $proxies = $serviceConfig[self::KEY_PROXIES];
        $maxAttempts = $serviceConfig[self::KEY_PROXIES];

        return new Loader(
            $userAgentGenerator,
            $proxyManager,
            $clientFactory,
            $logger,
            $maxAttempts
        );
    }
}
