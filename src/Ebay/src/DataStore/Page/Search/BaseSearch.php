<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\Ebay\DataStore\Page\Search;

use rollun\parser\DataStore\Page\Base;
use rollun\parser\Loader\Loader\LoaderInterface;
use rollun\parser\Parser\Parser\ParserInterface;
use rollun\service\Parser\Ebay\Helper\SearchPage;

class BaseSearch extends Base
{
    protected $searchPage;

    public function __construct(LoaderInterface $loader, ParserInterface $parser, SearchPage $searchPage)
    {
        $this->searchPage = $searchPage;
        parent::__construct($loader, $parser);
    }

    public function executeQuery($params)
    {
        $this->loader->setOptions([
            LoaderInterface::COOKIES_OPTION => $this->searchPage->getCookie($params['uri'])
        ]);

        $trueUri = $this->searchPage->getTrueUri($params['uri']);
        $html = $this->loader->load($trueUri);
        $records = $this->parser->parse($html);

        return $records;
    }
}
