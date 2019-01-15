<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\Parser\Parser;

use Psr\Container\ContainerInterface;
use Zend\ServiceManager\Config;

class ParserPluginManagerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $pluginManager = new ParserPluginManager($container);

        // If we do not have a config service, nothing more to do
        if (! $container->has('config')) {
            return $pluginManager;
        }

        $config = $container->get('config');

        // If we do not have log_filters configuration, nothing more to do
        if (! isset($config['parsers']) || ! is_array($config['parsers'])) {
            return $pluginManager;
        }

        // Wire service configuration for log_filters
        (new Config($config['parsers']))->configureServiceManager($pluginManager);

        return $pluginManager;
    }
}
