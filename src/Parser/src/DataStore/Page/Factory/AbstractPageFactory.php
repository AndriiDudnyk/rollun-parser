<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\DataStore\Page\Factory;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use rollun\parser\DataStore\Page\PageInterface;

abstract class AbstractPageFactory
{
    const KEY = 'parserStorage';

    /**
     * @param ContainerInterface $container
     * @param string $class
     * @return PageInterface
     */
    public function __invoke(ContainerInterface $container, $class)
    {
        $serviceConfig = $container->get('config')[self::KEY][$class];

        $this->validateClass($class);

        return $this->createParserStorage($container, $serviceConfig, $class);
    }

    /**
     * @param $serviceConfig
     * @return mixed
     * @throws InvalidArgumentException
     */
    abstract protected function validateClass($serviceConfig);

    abstract protected function createParserStorage(
        ContainerInterface $container,
        $serviceConfig,
        $class
    ): PageInterface;
}
