<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\DataStore\Storage\Search;

use Parser\DataStore\Storage\BaseStorage;
use Parser\Loader\Loader;
use Parser\Loader\SearchLoaderHelper;
use Parser\Parser\ParserInterface;

class BaseSearch extends BaseStorage
{
    protected $searchLoaderHelper;

    public function __construct(Loader $loader, ParserInterface $parser, SearchLoaderHelper $searchLoaderHelper)
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
