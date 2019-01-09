<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\DataStore;

use rollun\datastore\DataStore\Aspect\AspectAbstract;
use rollun\datastore\DataStore\Interfaces\DataStoresInterface;

class AutoGenerateIdAspect extends AspectAbstract
{
    public function __construct(DataStoresInterface $dataStore)
    {
        parent::__construct($dataStore);
    }

    public function preCreate($itemData, $rewriteIfExist = false)
    {
        if (!isset($itemData[$this->getIdentifier()])) {
            $itemData[$this->getIdentifier()] = uniqid(md5(openssl_random_pseudo_bytes(1024)));
        }

        return $itemData;
    }
}
