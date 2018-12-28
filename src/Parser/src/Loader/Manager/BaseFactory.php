<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\Loader\Manager;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use rollun\parser\DataStore\Entity\LoaderTaskInterface;
use rollun\parser\DataStore\Entity\ParserTaskInterface;
use rollun\parser\Loader\Loader\LoaderInterface;

class BaseFactory extends AbstractFactory
{
    const KEY_LOADER = 'loader';

    const KEY_PARSER_NAMES = 'parserNames';

    const KEY_PARSER_TASK_DATASTORE = 'parserTaskDataStore';

    const KEY_LOADER_TASK_DATASTORE = 'loaderTaskDataStore';

    protected function createInstance(ContainerInterface $container, $serviceConfig, $class): LoaderManagerInterface
    {
        $loader = $this->getLoader($container, $serviceConfig);
        $parserTask = $this->getParserTaskDataStore($container, $serviceConfig);
        $loaderTask = $this->getLoaderTaskDataStore($container, $serviceConfig);
        $parserNames = $this->getParserNames($serviceConfig);

        return new $class($loader, $loaderTask, $parserTask, $parserNames);
    }

    protected function checkInstance($class)
    {
        if (!is_a($class, Base::class, true)) {
            throw new InvalidArgumentException(sprintf(
                'Expected class %, given %s',
                Base::class,
                is_object($class) ? get_class($class) : gettype($class)
            ));
        }
    }

    protected function getParserTaskDataStore(ContainerInterface $container, array $serviceConfig): ParserTaskInterface
    {
        if (!isset($serviceConfig[self::KEY_PARSER_TASK_DATASTORE])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_PARSER_TASK_DATASTORE . "'");
        }

        return $container->get($serviceConfig[self::KEY_PARSER_TASK_DATASTORE]);
    }

    protected function getLoaderTaskDataStore(ContainerInterface $container, array $serviceConfig): LoaderTaskInterface
    {
        if (!isset($serviceConfig[self::KEY_LOADER_TASK_DATASTORE])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_LOADER_TASK_DATASTORE . "'");
        }

        return $container->get($serviceConfig[self::KEY_LOADER_TASK_DATASTORE]);
    }

    protected function getLoader(ContainerInterface $container, array $serviceConfig): LoaderInterface
    {
        if (!isset($serviceConfig[self::KEY_LOADER])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_LOADER . "'");
        }

        return $container->get($serviceConfig[self::KEY_LOADER]);
    }

    protected function getParserNames($serviceConfig)
    {
        if (!isset($serviceConfig[self::KEY_PARSER_NAMES])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_PARSER_NAMES . "'");
        }

        return $serviceConfig[self::KEY_PARSER_NAMES];
    }
}
