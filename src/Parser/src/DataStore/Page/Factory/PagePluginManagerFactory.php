<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\DataStore\Page\Factory;

use Psr\Container\ContainerInterface;
use rollun\parser\DataStore\Page\PagePluginManager;

class PagePluginManagerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $pluginManager = new PagePluginManager($container);
        $config = $container->get("config");
        $pluginManager->configure($config["dependencies"]);

        return $pluginManager;
    }
}
