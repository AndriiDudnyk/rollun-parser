<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\Ebay\Loader\Manager;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use rollun\parser\Loader\Manager\BaseFactory;
use rollun\parser\Loader\Manager\LoaderManagerInterface;
use rollun\service\Parser\Ebay\Helper\SearchPage as SearchPageHelper;

class SearchFactory extends BaseFactory
{
    const KEY_SEARCH_PAGE_HELPER = 'searchPageHelper';

    protected function createInstance(ContainerInterface $container, $serviceConfig, $class): LoaderManagerInterface
    {
        $loader = $this->getLoader($container, $serviceConfig);
        $parserTask = $this->getParserTaskDataStore($container, $serviceConfig);
        $loaderTask = $this->getLoaderTaskDataStore($container, $serviceConfig);
        $parserNames = $this->getParserNames($serviceConfig);
        $searchPageHelper = $this->getSearchPageHelper($container, $serviceConfig);

        return new $class($loader, $loaderTask, $parserTask, $searchPageHelper, $parserNames);
    }

    protected function getSearchPageHelper(ContainerInterface $container, $serviceConfig): SearchPageHelper
    {
        if (!isset($serviceConfig[self::KEY_SEARCH_PAGE_HELPER])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_SEARCH_PAGE_HELPER . "'");
        }

        return $container->get($serviceConfig[self::KEY_SEARCH_PAGE_HELPER]);
    }

    protected function checkInstance($class)
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
