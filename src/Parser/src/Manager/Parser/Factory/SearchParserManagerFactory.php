<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Manager\Parser\Factory;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use Parser\Manager\Parser\BaseManager;
use Parser\Manager\Parser\Search\Simple;

class SearchParserManagerFactory extends BaseParserManagerFactory
{
    const KEY_TASK_DATASTORE = 'taskDataStore';

    protected function createParserManager(
        ContainerInterface $container,
        array $serviceConfig,
        $class
    ): BaseManager {
        $parser = $this->createParser($container, $serviceConfig);
        $parseResultDataStore = $this->createParseResultDataStore($container, $serviceConfig);
        $documentDataStore = $this->createDocumentDataStore($container, $serviceConfig);

        if (!isset($serviceConfig[static::KEY_TASK_DATASTORE])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_TASK_DATASTORE . "'");
        }

        $taskDataStore = $container->get($serviceConfig[static::KEY_TASK_DATASTORE]);
        $options = $this->getOptions($serviceConfig);

        return new Simple($parser, $parseResultDataStore, $documentDataStore, $taskDataStore, $options);
    }
}
