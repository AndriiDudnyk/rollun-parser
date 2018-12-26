<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\DataStore\Storage\Search;

use Parser\Loader\Loader;
use Parser\Loader\SearchLoaderHelper;
use Parser\Parser\Search\Simple as SimpleSearchParser;

class Simple extends BaseSearch
{
    protected $searchLoaderHelper;

    public function __construct(Loader $loader, SimpleSearchParser $parser, SearchLoaderHelper $searchLoaderHelper)
    {
        $this->searchLoaderHelper = $searchLoaderHelper;
        parent::__construct($loader, $parser, $searchLoaderHelper);
    }
}
