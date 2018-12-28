<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\Loader\Manager;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;

class BaseFactory
{
    const KEY_LOADER = 'loader';

    const KEY_PARSER_TASK_DATASTORE = 'parserTaskDataStore';

    const KEY_LOADER_TASK_DATASTORE = 'loaderTaskDataStore';

    public function __invoke(ContainerInterface $container)
    {
        $serviceConfig = $container->get('config')[self::class] ?? [];

        if (!isset($serviceConfig[self::KEY_PARSER_TASK_DATASTORE])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_PARSER_TASK_DATASTORE . "'");
        }

        if (!isset($serviceConfig[self::KEY_LOADER])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_LOADER . "'");
        }

        if (!isset($serviceConfig[self::KEY_LOADER_TASK_DATASTORE])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_LOADER_TASK_DATASTORE . "'");
        }

        $loader = $container->get($serviceConfig[self::KEY_LOADER]);
        $htmlDataStore = $container->get($serviceConfig[self::KEY_PARSER_TASK_DATASTORE]);
        $taskDataStore = $container->get($serviceConfig[self::KEY_LOADER_TASK_DATASTORE]);

        return new Base($loader, $taskDataStore, $htmlDataStore);
    }
}
