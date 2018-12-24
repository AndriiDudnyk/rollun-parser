<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Manager\Factory;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use Parser\Manager\BaseParserManager;
use Parser\Manager\SearchParserManager;

class SearchParserManagerFactory extends BaseParserManagerFactory
{
    const KEY_TASK_DATASTORE = 'taskDataStore';

    protected function createParserManager(
        ContainerInterface $container,
        array $serviceConfig,
        $class
    ): BaseParserManager {
        $parser = $this->createParser($container, $serviceConfig);
        $parseResultDataStore = $this->createParseResultDataStore($container, $serviceConfig);
        $documentDataStore = $this->createDocumentDataStore($container, $serviceConfig);

        if (!isset($serviceConfig[static::KEY_TASK_DATASTORE])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_TASK_DATASTORE . "'");
        }

        $taskDataStore = $container->get($serviceConfig[static::KEY_TASK_DATASTORE]);
        $options = $this->getOptions($serviceConfig);

        return new SearchParserManager($parser, $parseResultDataStore, $documentDataStore, $taskDataStore, $options);
    }
}
