<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Manager\Factory;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use Parser\Manager\BaseParserManager;

abstract class AbstractParserManagerFactory
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
    ): BaseParserManager;

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

        if (!is_a($class, BaseParserManager::class, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Expected class %, given %s',
                    BaseParserManager::class,
                    is_object($class) ? get_class($class) : gettype($class)
                )
            );
        }

        return $class;
    }
}
