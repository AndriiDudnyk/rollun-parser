<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\Parser\ParserResolver;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

class SimpleAbstractFactory implements AbstractFactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        // TODO: Implement __invoke() method.
    }

    public function canCreate(ContainerInterface $container, $requestedName)
    {
        // TODO: Implement canCreate() method.
    }
}
