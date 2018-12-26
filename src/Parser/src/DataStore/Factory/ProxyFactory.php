<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\DataStore\Factory;

use InvalidArgumentException;
use Parser\DataStore\Proxy;
use Psr\Container\ContainerInterface;

class ProxyFactory
{
    const KEY_TASK_DATASTORE = 'taskDataStore';
    const KEY_PROXY_LIST_URI = 'proxyListUri';
    const KEY_ASPECT_DATASTORE = 'aspectDataStore';
    const KEY_TASK_OPTIONS = 'taskOptions';

    public function __invoke(ContainerInterface $container)
    {
        $serviceConfig = $container->get('config')[self::class] ?? [];

        if (!isset($serviceConfig[self::KEY_TASK_DATASTORE])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_TASK_DATASTORE . "'");
        }

        if (!isset($serviceConfig[self::KEY_ASPECT_DATASTORE])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_ASPECT_DATASTORE . "'");
        }

        if (!isset($serviceConfig[self::KEY_PROXY_LIST_URI])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_PROXY_LIST_URI . "'");
        }

        $proxyListUri = $serviceConfig[self::KEY_PROXY_LIST_URI];
        $taskOptions = $serviceConfig[self::KEY_TASK_OPTIONS] ?? [];
        $taskDataStore = $container->get($serviceConfig[self::KEY_TASK_DATASTORE]);
        $aspectDataStore = $container->get($serviceConfig[self::KEY_ASPECT_DATASTORE]);

        return new Proxy($aspectDataStore, $taskDataStore, $proxyListUri, $taskOptions);
    }
}
