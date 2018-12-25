<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\DataStore\Parser\Factory;

use InvalidArgumentException;
use Parser\DataStore\Parser\ParserStorageInterface;
use Psr\Container\ContainerInterface;

abstract class AbstractStorageFactory
{
    const KEY = 'parserStorage';

    /**
     * @param ContainerInterface $container
     * @param string $class
     * @return ParserStorageInterface
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
    ): ParserStorageInterface;
}
