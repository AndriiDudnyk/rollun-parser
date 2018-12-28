<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\DataStore\Page;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;

class PageDetectorFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config')[self::class] ?? [];
        $storagePluginManager = $container->get(PagePluginManager::class);

        if (empty($config)) {
            throw new InvalidArgumentException('Config for storage detector not found');
        }

        return new PageDetector($storagePluginManager, $config);
    }
}
