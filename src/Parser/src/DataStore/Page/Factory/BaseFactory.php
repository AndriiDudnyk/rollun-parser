<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\DataStore\Page\Factory;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use rollun\parser\DataStore\Page\Base;
use rollun\parser\DataStore\Page\PageInterface;
use rollun\parser\Loader\Loader\LoaderInterface;
use rollun\parser\Parser\Parser\ParserInterface;

class BaseFactory extends AbstractPageFactory
{
    const KEY_LOADER = 'loader';

    const KEY_PARSER = 'parser';

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

        return new $class($loader, $parser);
    }

    protected function validateClass($class)
    {
        if (!is_a($class, Base::class, true)) {
            throw new InvalidArgumentException(sprintf(
                'Expected class %, given %s',
                Base::class,
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
