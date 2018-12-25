<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\DataStore\Parser\Factory;

use Parser\DataStore\Parser\StoragePluginManager;
use Psr\Container\ContainerInterface;

class StoragePluginManagerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $pluginManager = new StoragePluginManager($container);
        $config = $container->get("config");
        $pluginManager->configure($config["dependencies"]);

        return $pluginManager;
    }
}
