<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\Parser\ParserResolver;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

class SimpleAbstractFactory implements AbstractFactoryInterface
{
    protected $container;

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if (empty($options)) {
            $parsers = $container->get('config')[self::class][$requestedName];
        } else {
            $parsers = $options;
        }

        if (!$parsers) {
            throw new InvalidArgumentException('Invalid config for ' . Simple::class);
        }

        $servedParsers = [];

        foreach ($parsers as $parser) {
            if (is_scalar($parser) && $container->has($parser)) {
                $servedParsers[] = $container->get($parser);
            } elseif (is_object($parser)) {
                $servedParsers[] = $parser;
            }
        }

        return new Simple($servedParsers);
    }

    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return !empty($container->get('config')[self::class][$requestedName] ?? []);
    }
}
