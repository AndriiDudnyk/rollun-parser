<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\Ebay\DataStore\Entity\Interfaces;

use rollun\datastore\DataStore\Interfaces\DataStoresInterface;

interface SoldProductInterface extends DataStoresInterface
{
    const COLUMN_ITEM_ID = 'item_id';
    const COLUMN_URI = 'uri';
    const COLUMN_TITLE = 'title';
    const COLUMN_PRICE = 'price';
    const COLUMN_SHIPPING = 'shipping';
    const COLUMN_SELLER = 'seller';
    const COLUMN_DATE = 'date';
}
