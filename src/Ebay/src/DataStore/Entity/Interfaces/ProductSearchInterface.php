<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\Ebay\DataStore\Entity\Interfaces;

use rollun\datastore\DataStore\Interfaces\DataStoresInterface;

interface ProductSearchInterface extends DataStoresInterface
{
    const COLUMN_ITEM_ID = 'item_id';
    const COLUMN_EBAY_ID = 'ebay_id';
    const COLUMN_IMG = 'img';
    const COLUMN_PRICE = 'price';
    const COLUMN_SHIPPING = 'shipping';
    const COLUMN_SELLER = 'seller';
    const COLUMN_WATCH = 'watch';
    const COLUMN_SOLD = 'sold';
}
