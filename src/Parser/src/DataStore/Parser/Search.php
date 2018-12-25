<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\DataStore\Parser;

use Parser\Loader\Loader;
use Parser\Loader\SearchLoaderHelper;
use Parser\Parser\Search as SearchParser;

class Search extends BaseStorage
{
    protected $searchLoaderHelper;

    public function __construct(Loader $loader, SearchParser $parser, SearchLoaderHelper $searchLoaderHelper)
    {
        $this->searchLoaderHelper = $searchLoaderHelper;
        parent::__construct($loader, $parser);
    }

    public function executeQuery($params)
    {
        $this->loader->setOptions([
            Loader::COOKIES_OPTION => $this->searchLoaderHelper->getCookie($params['uri'])
        ]);

        $trueUri = $this->searchLoaderHelper->getTrueUri($params['uri']);
        $html = $this->loader->load($trueUri);
        $records = $this->parser->parse($html);

        return $records;
    }
}
