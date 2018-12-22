<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Loader;

use InvalidArgumentException;
use Parser\UserAgentGenerator;
use Psr\Container\ContainerInterface;

class LoaderFactory
{
    const KEY_PROXY_DATASTORE = 'proxyDataStore';

    public function __invoke(ContainerInterface $container)
    {
        $serviceConfig = $container->get('config')[self::class] ?? [];

        if (!isset($serviceConfig[self::KEY_PROXY_DATASTORE])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_PROXY_DATASTORE . "'");
        }

        $userAgentGenerator = $container->get(UserAgentGenerator::class);
        $proxyDataStore = $container->get($serviceConfig[self::KEY_PROXY_DATASTORE]);

        return new Loader($userAgentGenerator, $proxyDataStore);
    }
}
