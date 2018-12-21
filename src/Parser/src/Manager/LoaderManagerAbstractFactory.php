<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Manager;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

class LoaderManagerAbstractFactory implements AbstractFactoryInterface
{
    const KEY_LOADER = 'loader';

    const KEY_HTML_DATASTORE = 'htmlDataStore';

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $serviceConfig = $container->get('config')[self::class][$requestedName];

        if (!isset($serviceConfig[self::KEY_HTML_DATASTORE])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_HTML_DATASTORE . "'");
        }

        if (!isset($serviceConfig[self::KEY_LOADER])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_LOADER . "'");
        }

        $loader = $container->get($serviceConfig[self::KEY_LOADER]);
        $htmlDataStore = $container->get($serviceConfig[self::KEY_HTML_DATASTORE]);

        return new LoaderManager($loader, $htmlDataStore);
    }

    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return !empty($container->get('config')[self::class][$requestedName]);
    }
}
