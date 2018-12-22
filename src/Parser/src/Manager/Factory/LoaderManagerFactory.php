<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Manager\Factory;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use Parser\Manager\LoaderManager;

class LoaderManagerFactory
{
    const KEY_LOADER = 'loader';

    const KEY_DOCUMENT_DATASTORE = 'documentDataStore';

    const KEY_TASK_DATASTORE = 'taskDataStore';

    public function __invoke(ContainerInterface $container)
    {
        $serviceConfig = $container->get('config')[self::class] ?? [];

        if (!isset($serviceConfig[self::KEY_DOCUMENT_DATASTORE])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_DOCUMENT_DATASTORE . "'");
        }

        if (!isset($serviceConfig[self::KEY_LOADER])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_LOADER . "'");
        }

        if (!isset($serviceConfig[self::KEY_TASK_DATASTORE])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_TASK_DATASTORE . "'");
        }

        $loader = $container->get($serviceConfig[self::KEY_LOADER]);
        $htmlDataStore = $container->get($serviceConfig[self::KEY_DOCUMENT_DATASTORE]);
        $taskDataStore = $container->get($serviceConfig[self::KEY_TASK_DATASTORE]);

        return new LoaderManager($loader, $taskDataStore, $htmlDataStore);
    }
}
