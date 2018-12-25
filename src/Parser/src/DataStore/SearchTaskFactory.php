<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\DataStore;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;

class SearchTaskFactory
{
    const KEY_SEARCH_LOADER_HELPER = 'searchLoaderHelper';
    const KEY_TASK_DATASTORE = 'taskDataStore';

    public function __invoke(ContainerInterface $container)
    {
        $serviceConfig = $container->get('config')[self::class] ?? [];

        if (!isset($serviceConfig[self::KEY_SEARCH_LOADER_HELPER])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_SEARCH_LOADER_HELPER . "'");
        }

        if (!isset($serviceConfig[self::KEY_TASK_DATASTORE])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_TASK_DATASTORE . "'");
        }

        $searchLoaderHelper = $container->get($serviceConfig[self::KEY_SEARCH_LOADER_HELPER]);
        $taskDataStore = $container->get($serviceConfig[self::KEY_TASK_DATASTORE]);

        return new SearchTask($taskDataStore, $searchLoaderHelper);
    }
}
