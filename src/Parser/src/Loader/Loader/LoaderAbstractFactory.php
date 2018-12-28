<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\Loader\Loader;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use rollun\parser\UserAgentGenerator;
use Zend\Diactoros\ServerRequestFactory;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

class LoaderAbstractFactory implements AbstractFactoryInterface
{
    const KEY_PROXY_DATASTORE = 'proxyDataStore';
    const KEY_OPTIONS = 'options';

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $serviceConfig = $container->get('config')[self::class][$requestedName];

        if (!isset($serviceConfig[self::KEY_PROXY_DATASTORE])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_PROXY_DATASTORE . "'");
        }

        $requestFactory = new ServerRequestFactory();
        $options = $serviceConfig[self::KEY_OPTIONS] ?? [];
        $userAgentGenerator = $container->get(UserAgentGenerator::class);
        $proxyDataStore = $container->get($serviceConfig[self::KEY_PROXY_DATASTORE]);

        return new Base($userAgentGenerator, $proxyDataStore, $requestFactory, $options);
    }

    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return $container->get('config')[self::class][$requestedName] ?? null;
    }
}
