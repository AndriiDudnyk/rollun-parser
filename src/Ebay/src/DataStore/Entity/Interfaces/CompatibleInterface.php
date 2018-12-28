<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\Ebay\DataStore\Entity\Interfaces;

use rollun\datastore\DataStore\Interfaces\DataStoresInterface;

interface CompatibleInterface extends DataStoresInterface
{
    const COLUMN_PRODUCT_ID = 'product_id';
    const COLUMN_MAKE = 'make';
    const COLUMN_MODEL = 'model';
    const COLUMN_SUBMODEL = 'submodel';
    const COLUMN_YEAR = 'year';
    const COLUMN_CREATED_AT = 'created_at';
}
