<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\Ebay\DataStore\Page\Search;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use rollun\parser\DataStore\Page\Factory\BaseFactory;
use rollun\parser\DataStore\Page\PageInterface;

class SearchFactory extends BaseFactory
{
    const KEY_SEARCH_PAGE_HELPER = 'searchLoaderHelper';

    /**
     * @param ContainerInterface $container
     * @param $serviceConfig
     * @param $class
     * @return PageInterface
     */
    public function createParserStorage(ContainerInterface $container, $serviceConfig, $class): PageInterface
    {
        $loader = $this->getLoader($container, $serviceConfig);
        $parser = $this->getParser($container, $serviceConfig);

        if (!isset($serviceConfig[self::KEY_SEARCH_PAGE_HELPER])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_SEARCH_PAGE_HELPER . "'");
        }

        $searchLoaderHelper = $container->get($serviceConfig[self::KEY_SEARCH_PAGE_HELPER]);

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
