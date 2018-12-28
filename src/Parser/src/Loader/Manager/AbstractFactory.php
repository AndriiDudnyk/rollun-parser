<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\Loader\Manager;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;

abstract class AbstractFactory
{
    const KEY = 'loaderManagers';

    const KEY_CLASS = 'class';

    public function __invoke(ContainerInterface $container, $requestedName): LoaderManagerInterface
    {
        $serviceConfig = $this->getServiceConfig($container, $requestedName);
        $class = $this->getClass($serviceConfig, $requestedName);

        return $this->createInstance($container, $serviceConfig, $class);
    }

    protected function getServiceConfig(ContainerInterface $container, $requestedName)
    {
        return $container->get('config')[static::KEY][$requestedName] ?? [];
    }

    abstract protected function createInstance(
        ContainerInterface $container,
        $serviceConfig,
        $class
    ): LoaderManagerInterface;

    protected function getClass($serviceConfig, $requestedName)
    {
        $class = null;

        if (isset($serviceConfig[self::KEY_CLASS])) {
            $class = $serviceConfig[self::KEY_CLASS];
        } else {
            $class = $requestedName;
        }

        $this->checkInstance($class);

        return $class;
    }

    protected function checkInstance($class)
    {
        if (!is_a($class, LoaderManagerInterface::class, true)) {
            throw new InvalidArgumentException(sprintf(
                'Expected class %s, given %s',
                LoaderManagerInterface::class,
                is_object($class) ? get_class($class) : gettype($class)
            ));
        }
    }
}
