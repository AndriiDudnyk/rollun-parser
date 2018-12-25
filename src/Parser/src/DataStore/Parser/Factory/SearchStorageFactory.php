<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\DataStore\Parser\Factory;

use InvalidArgumentException;
use Parser\DataStore\Parser\ParserStorageInterface;
use Parser\DataStore\Parser\Search;
use Psr\Container\ContainerInterface;

class SearchStorageFactory extends BaseStorageFactory
{
    const KEY_SEARCH_LOADER_HELPER = 'searchLoaderHelper';

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

        if (!isset($serviceConfig[self::KEY_SEARCH_LOADER_HELPER])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_SEARCH_LOADER_HELPER . "'");
        }

        $searchLoaderHelper = $container->get($serviceConfig[self::KEY_SEARCH_LOADER_HELPER]);

        return new $class($loader, $parser, $searchLoaderHelper);
    }

    protected function validateClass($class)
    {
        if (!is_a($class, Search::class, true)) {
            throw new InvalidArgumentException(sprintf(
                'Expected class %, given %s',
                Search::class,
                is_object($class) ? get_class($class) : gettype($class)
            ));
        }
    }
}
