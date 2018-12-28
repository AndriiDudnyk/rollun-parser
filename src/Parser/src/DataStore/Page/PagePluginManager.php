<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\DataStore\Page;

use Zend\ServiceManager\AbstractPluginManager;

class PagePluginManager extends AbstractPluginManager
{
    protected $instanceOf = PageInterface::class;
}
