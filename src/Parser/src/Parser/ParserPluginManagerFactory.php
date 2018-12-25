<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Parser;

use Psr\Container\ContainerInterface;

class ParserPluginManagerFactory
{
    const KEY = 'parsers';

    public function __invoke(ContainerInterface $container): ParserPluginManager
    {
        $parserPluginManager = new ParserPluginManager($container);

        return $parserPluginManager;
    }
}
