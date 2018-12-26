<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\DataStore\Storage\Search;

use Parser\Loader\Loader;
use Parser\Loader\SearchLoaderHelper;
use Parser\Parser\Search\EbayMotors as EbayMotorsSearchParser;

class EbayMotors extends BaseSearch
{
    protected $searchLoaderHelper;

    public function __construct(Loader $loader, EbayMotorsSearchParser $parser, SearchLoaderHelper $searchLoaderHelper)
    {
        parent::__construct($loader, $parser, $searchLoaderHelper);
    }
}
