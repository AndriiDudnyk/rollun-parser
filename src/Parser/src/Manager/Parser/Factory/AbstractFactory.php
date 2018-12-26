<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Manager\Parser\Factory;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use Parser\Manager\Parser\BaseManager;

abstract class AbstractFactory
{
    const KEY = 'parserManagers';

    const KEY_CLASS = 'class';

    public function __invoke(ContainerInterface $container, $requestedName)
    {
        $serviceConfig = $this->getServiceConfig($container, $requestedName);
        $class = $this->getClass($serviceConfig, $requestedName);

        return $this->createParserManager($container, $serviceConfig, $class);
    }

    abstract protected function createParserManager(
        ContainerInterface $container,
        array $serviceConfig,
        $requestedName
    ): BaseManager;

    protected function getServiceConfig(ContainerInterface $container, $requestedName)
    {
        return $container->get('config')[static::KEY][$requestedName] ?? [];
    }

    protected function getClass($serviceConfig, $requestedName)
    {
        $class = null;

        if (isset($serviceConfig[self::KEY_CLASS])) {
            $class = $serviceConfig[self::KEY_CLASS];
        } else {
            $class = $requestedName;
        }

        if (!is_a($class, BaseManager::class, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Expected class %, given %s',
                    BaseManager::class,
                    is_object($class) ? get_class($class) : gettype($class)
                )
            );
        }

        return $class;
    }
}
