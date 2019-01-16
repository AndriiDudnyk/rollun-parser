<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\Ebay\DataStore\Entity\Interfaces;

use rollun\datastore\DataStore\Interfaces\DataStoresInterface;

interface ProductInterface extends DataStoresInterface
{
    const COLUMN_EBAY_ID = 'ebay_id';
    const COLUMN_TITLE = 'title';
    const COLUMN_PRICE = 'price';
    const COLUMN_SHIPPING = 'shipping';
    const COLUMN_CATEGORY = 'category';
    const COLUMN_SELLER = 'seller';
    const COLUMN_WATCH = 'watch';
    const COLUMN_SOLD = 'sold';
    const COLUMN_IMGS = 'imgs';
    const COLUMN_SPECS = 'specs';
    const COLUMN_URI = 'uri';
}
