<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\Ebay\DataStore\Page\Search;

use rollun\service\Parser\Ebay\Parser\Parser\Search\EbayMotors as EbayMotorsSearchParser;
use rollun\parser\Loader\Loader\LoaderInterface;
use rollun\service\Parser\Ebay\Helper\SearchPage;

class EbayMotors extends BaseSearch
{
    protected $searchPage;

    public function __construct(LoaderInterface $loader, EbayMotorsSearchParser $parser, SearchPage $searchPage)
    {
        parent::__construct($loader, $parser, $searchPage);
    }
}
