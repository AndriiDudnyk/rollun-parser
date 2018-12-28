<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\Parser\Manager;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;

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
    ): ParserManagerInterface;

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

        $this->checkClass($class);

        return $class;
    }

    protected function checkClass($class)
    {
        if (!is_a($class, ParserManagerInterface::class, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Expected class %, given %s',
                    ParserManagerInterface::class,
                    is_object($class) ? get_class($class) : gettype($class)
                )
            );
        }
    }
}
