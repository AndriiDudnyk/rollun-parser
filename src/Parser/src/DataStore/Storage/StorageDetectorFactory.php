<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\DataStore\Storage;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;

class StorageDetectorFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config')[self::class] ?? [];
        $storagePluginManager = $container->get(StoragePluginManager::class);

        if (empty($config)) {
            throw new InvalidArgumentException('Config for storage detector not found');
        }

        return new StorageDetector($storagePluginManager, $config);
    }
}
