<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\DataStore\Storage\Factory;

use InvalidArgumentException;
use Parser\DataStore\Storage\BaseStorage;
use Parser\DataStore\Storage\StorageInterface;
use Parser\Loader\LoaderInterface;
use Parser\Manager\Parser\BaseManager;
use Parser\Parser\ParserInterface;
use Psr\Container\ContainerInterface;

class BaseFactory extends AbstractStorageFactory
{
    const KEY_LOADER = 'loader';

    const KEY_PARSER = 'parser';

    /**
     * @param ContainerInterface $container
     * @param $serviceConfig
     * @param $class
     * @return StorageInterface
     */
    public function createParserStorage(ContainerInterface $container, $serviceConfig, $class): StorageInterface
    {
        $loader = $this->getLoader($container, $serviceConfig);
        $parser = $this->getParser($container, $serviceConfig);

        return new $class($loader, $parser);
    }

    protected function validateClass($class)
    {
        if (!is_a($class, BaseStorage::class, true)) {
            throw new InvalidArgumentException(sprintf(
                'Expected class %, given %s',
                BaseManager::class,
                is_object($class) ? get_class($class) : gettype($class)
            ));
        }
    }

    protected function getLoader(ContainerInterface $container, $serviceConfig): LoaderInterface
    {
        if (!isset($serviceConfig[self::KEY_LOADER])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_LOADER . "'");
        }

        return $container->get($serviceConfig[self::KEY_LOADER]);
    }

    protected function getParser(ContainerInterface $container, $serviceConfig): ParserInterface
    {
        if (!isset($serviceConfig[self::KEY_PARSER])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_PARSER . "'");
        }

        return $container->get($serviceConfig[self::KEY_PARSER]);
    }
}
