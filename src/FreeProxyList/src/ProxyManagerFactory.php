<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\FreeProxyList;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;

class ProxyManagerFactory
{
    const KEY_LOADER_TASK_DATASTORE = 'loaderTaskDataStore';
    const KEY_PROXY_LIST_URI = 'freeProxyListUri';
    const KEY_PROXY_DATASTORE = 'proxyDataStore';
    const KEY_LOADER_TASK_OPTIONS = 'loaderTaskOptions';

    public function __invoke(ContainerInterface $container)
    {
        $serviceConfig = $container->get('config')[self::class] ?? [];

        if (!isset($serviceConfig[self::KEY_LOADER_TASK_DATASTORE])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_LOADER_TASK_DATASTORE . "'");
        }

        if (!isset($serviceConfig[self::KEY_PROXY_DATASTORE])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_PROXY_DATASTORE . "'");
        }

        if (!isset($serviceConfig[self::KEY_PROXY_LIST_URI])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_PROXY_LIST_URI . "'");
        }

        $freeProxyListUri = $serviceConfig[self::KEY_PROXY_LIST_URI];
        $loaderTaskOptions = $serviceConfig[self::KEY_LOADER_TASK_OPTIONS] ?? [];
        $loaderTaskDataStore = $container->get($serviceConfig[self::KEY_LOADER_TASK_DATASTORE]);
        $proxyDataStore = $container->get($serviceConfig[self::KEY_PROXY_DATASTORE]);

        return new ProxyManager($proxyDataStore, $loaderTaskDataStore, $freeProxyListUri, $loaderTaskOptions);
    }
}
