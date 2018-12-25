<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\DataStore\Parser;

use Parser\Loader\Loader;
use Parser\Parser\Product as ProductParser;

class Product extends BaseStorage
{
    public function __construct(Loader $loader, ProductParser $parser)
    {
        parent::__construct($loader, $parser);
    }
}
