<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\DataStore\Storage\Factory;

use InvalidArgumentException;
use Parser\DataStore\Storage\Search\BaseSearch;
use Parser\DataStore\Storage\StorageInterface;
use Psr\Container\ContainerInterface;

class SearchFactory extends BaseFactory
{
    const KEY_SEARCH_LOADER_HELPER = 'searchLoaderHelper';

    /**
     * @param ContainerInterface $container
     * @param $serviceConfig
     * @param $class
     * @return BaseSearch
     */
    public function createParserStorage(ContainerInterface $container, $serviceConfig, $class): StorageInterface
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
        if (!is_a($class, BaseSearch::class, true)) {
            throw new InvalidArgumentException(sprintf(
                'Expected class %s, given %s',
                BaseSearch::class,
                is_object($class) ? get_class($class) : gettype($class)
            ));
        }
    }
}
