<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Manager\Factory;

use Interop\Container\ContainerInterface;
use Parser\Manager\BaseParserManager;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

abstract class AbstractParserManagerAbstractFactory implements AbstractFactoryInterface
{
    const KEY = 'parserManagers';

    const KEY_CLASS = 'class';

    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $serviceConfig = $this->getServiceConfig($container, $requestedName);

        if (empty($serviceConfig[self::KEY_CLASS])) {
            return false;
        }

        return $this->isClassCorrect($serviceConfig[self::KEY_CLASS]);
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $serviceConfig = $this->getServiceConfig($container, $requestedName);

        return $this->createParserManager($container, $serviceConfig);
    }

    abstract protected function isClassCorrect($class): bool;

    abstract protected function createParserManager(
        ContainerInterface $container,
        array $serviceConfig
    ): BaseParserManager;

    protected function getServiceConfig(ContainerInterface $container, $requestedName)
    {
        return $container->get('config')[static::KEY][$requestedName] ?? [];
    }
}
