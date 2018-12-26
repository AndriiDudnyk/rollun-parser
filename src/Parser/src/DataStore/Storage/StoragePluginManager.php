<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\DataStore\Storage;

use Zend\ServiceManager\AbstractPluginManager;

class StoragePluginManager extends AbstractPluginManager
{
    protected $instanceOf = StorageInterface::class;
}
