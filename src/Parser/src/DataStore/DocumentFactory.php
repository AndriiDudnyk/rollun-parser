<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\DataStore;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;

class DocumentFactory
{
    const KEY_DOWNLOAD_DATASTORE = 'downloadDataStore';

    const KEY_STORE_DIR = 'storeDir';

    public function __invoke(ContainerInterface $container)
    {
        $serviceConfig = $container->get('config')[self::class] ?? [];

        if (!isset($serviceConfig[self::KEY_DOWNLOAD_DATASTORE])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_DOWNLOAD_DATASTORE . "'");
        }

        if (!isset($serviceConfig[self::KEY_STORE_DIR])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_STORE_DIR . "'");
        }

        $storeDir = $serviceConfig[self::KEY_STORE_DIR];
        $downloadDataStore = $container->get($serviceConfig[self::KEY_DOWNLOAD_DATASTORE]);

        return new Document($downloadDataStore, $storeDir);
    }
}
