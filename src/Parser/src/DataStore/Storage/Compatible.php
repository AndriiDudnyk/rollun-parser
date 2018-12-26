<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\DataStore\Storage;

use Parser\Loader\Loader;
use Parser\Parser\Compatible as CompatibleParser;

class Compatible extends BaseStorage
{
    protected $searchLoaderHelper;

    public function __construct(Loader $loader, CompatibleParser $parser)
    {
        parent::__construct($loader, $parser);
    }
}
