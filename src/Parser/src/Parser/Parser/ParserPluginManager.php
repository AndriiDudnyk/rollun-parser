<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\Parser\Parser;

use Zend\ServiceManager\AbstractPluginManager;

class ParserPluginManager extends AbstractPluginManager
{
    protected $instanceOf = ParserInterface::class;
}
