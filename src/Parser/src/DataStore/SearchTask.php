<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\DataStore;

use InvalidArgumentException;
use Parser\Loader\Loader;
use Parser\Loader\SearchLoaderHelper;
use rollun\datastore\DataStore\Aspect\AspectAbstract;

class SearchTask extends AspectAbstract
{
    protected $searchLoaderHelper;

    public function __construct(Task $dataStore, SearchLoaderHelper $searchLoaderHelper)
    {
        $this->searchLoaderHelper = $searchLoaderHelper;

        parent::__construct($dataStore);
    }

    protected function preUpdate($itemData, $createIfAbsent = false)
    {
        if (isset($itemData['uri'])) {
            $itemData = $this->preWrite($itemData);
        }

        return parent::preUpdate($itemData, $createIfAbsent);
    }

    protected function preCreate($itemData, $rewriteIfExist = false)
    {
        if (!isset($itemData['uri'])) {
            throw new InvalidArgumentException("Required field 'uri' missing");
        }

        $itemData = $this->preWrite($itemData);

        return parent::preCreate($itemData, $rewriteIfExist);
    }

    protected function preWrite($itemData)
    {
        $cookies = $this->searchLoaderHelper->getCookie($itemData['uri']);

        // Expect that give real uri need to parse
        // But we need search listing with addition info (seller)
        // So we need to create new url (this is specific of ebay working)
        $trueUri = $this->searchLoaderHelper->getTrueUri($itemData['uri']);

        $itemData['options'][Loader::COOKIES_OPTION] = $cookies;
        $itemData['uri'] = $trueUri;

        return $itemData;
    }
}
