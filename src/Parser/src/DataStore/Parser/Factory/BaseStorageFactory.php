<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\DataStore\Parser\Factory;

use InvalidArgumentException;
use Parser\DataStore\Parser\BaseStorage;
use Parser\DataStore\Parser\ParserStorageInterface;
use Parser\Loader\LoaderInterface;
use Parser\Manager\BaseParserManager;
use Parser\Parser\ParserInterface;
use Psr\Container\ContainerInterface;

class BaseStorageFactory extends AbstractStorageFactory
{
    const KEY_LOADER = 'loader';

    const KEY_PARSER = 'parser';

    /**
     * @param ContainerInterface $container
     * @param $serviceConfig
     * @param $class
     * @return ParserStorageInterface
     */
    public function createParserStorage(ContainerInterface $container, $serviceConfig, $class): ParserStorageInterface
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
                BaseParserManager::class,
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
