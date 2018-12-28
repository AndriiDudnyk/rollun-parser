<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\Ebay\Parser\Manager\Factory;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use rollun\parser\Parser\Manager\BaseFactory;
use rollun\parser\Parser\Manager\ParserManagerInterface;

class SearchFactory extends BaseFactory
{
    const KEY_TASK_DATASTORE = 'taskDataStore';

    protected function createParserManager(
        ContainerInterface $container,
        array $serviceConfig,
        $class
    ): ParserManagerInterface {
        $parser = $this->createParser($container, $serviceConfig);
        $parseResultDataStore = $this->createParseResultDataStore($container, $serviceConfig);
        $documentDataStore = $this->createParserTask($container, $serviceConfig);

        if (!isset($serviceConfig[static::KEY_TASK_DATASTORE])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_TASK_DATASTORE . "'");
        }

        $taskDataStore = $container->get($serviceConfig[static::KEY_TASK_DATASTORE]);
        $options = $this->getOptions($serviceConfig);

        return new $class($parser, $parseResultDataStore, $documentDataStore, $taskDataStore, $options);
    }
}
