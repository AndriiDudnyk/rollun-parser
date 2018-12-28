<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\Ebay\Parser\Manager\Factory;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use rollun\parser\DataStore\Entity\LoaderTaskInterface;
use rollun\parser\Parser\Manager\BaseFactory;
use rollun\parser\Parser\Manager\ParserManagerInterface;
use rollun\service\Parser\Ebay\Parser\Manager\Compatible as CompatibleParserManager;

class CompatibleFactory extends BaseFactory
{
    const KEY_LOADER_TASK_DATASTORE = 'taskDataStore';

    protected function createParserManager(
        ContainerInterface $container,
        array $serviceConfig,
        $class
    ): ParserManagerInterface {
        $parser = $this->createParser($container, $serviceConfig);
        $parseResultDataStore = $this->createParseResultDataStore($container, $serviceConfig);
        $documentDataStore = $this->createParserTask($container, $serviceConfig);
        $loaderTaskDataStore = $this->getLoaderTaskDataStore($container, $serviceConfig);
        $options = $this->getOptions($serviceConfig);

        return new $class($parser, $parseResultDataStore, $documentDataStore, $loaderTaskDataStore, $options);
    }

    protected function getLoaderTaskDataStore(ContainerInterface $container, array $serviceConfig): LoaderTaskInterface
    {
        if (!isset($serviceConfig[static::KEY_LOADER_TASK_DATASTORE])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_LOADER_TASK_DATASTORE . "'");
        }

        return $container->get($serviceConfig[static::KEY_LOADER_TASK_DATASTORE]);
    }

    protected function checkClass($class)
    {
        if (!is_a($class, CompatibleParserManager::class, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Expected class %, given %s',
                    CompatibleParserManager::class,
                    is_object($class) ? get_class($class) : gettype($class)
                )
            );
        }
    }
}
