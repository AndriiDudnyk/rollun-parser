<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Parser;

use Parser\Parser\Search\Simple as SimpleSeacrhParser;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Factory\InvokableFactory;

class ParserPluginManager extends AbstractPluginManager
{
    protected $instanceOf = ParserInterface::class;

    protected $factories = [
        Product::class => InvokableFactory::class,
        SimpleSeacrhParser::class => InvokableFactory::class,
        Compatible::class => InvokableFactory::class,
    ];
}
