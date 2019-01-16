<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\Parser\Parser;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

class LazyParserAbstractFactory implements AbstractFactoryInterface
{
    const KEY_PARSER_RESOLVER = 'parserResolver';

    const KEY_PARSER_NAME = 'parserName';

    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return !empty($container->get('config')[self::class][$requestedName] ?? []);
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config')[self::class][$requestedName];

        if (!isset($config[self::KEY_PARSER_NAME])) {
            throw new \InvalidArgumentException("Invalid option '" . self::KEY_PARSER_NAME . "'");
        }

        if (!isset($config[self::KEY_PARSER_RESOLVER])) {
            throw new \InvalidArgumentException("Invalid option '" . self::KEY_PARSER_RESOLVER . "'");
        }

        return new LazyParser(
            $container->get($config[self::KEY_PARSER_RESOLVER]),
            $config[self::KEY_PARSER_NAME]
        );
    }
}
