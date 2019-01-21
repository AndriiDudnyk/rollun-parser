<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\FreeProxyList\DataStore\Entity;

use rollun\datastore\DataStore\Interfaces\DataStoresInterface;

interface ProxyInterface extends DataStoresInterface
{
    const MAX_LEVEL = 10;
    const MIN_LEVEL = 1;
    const FAIL_LEVEL = 0;

    const COLUMN_URI = 'uri';
    const COLUMN_CREATED_AT = 'created_at';
    const COLUMN_UPDATED_AT = 'updated_at';
    const COLUMN_IS_USED = 'is_used';
    const COLUMN_LEVEL = 'level';

    public function addNewUri($uri);

    public function getRecordByFields($itemData);
}
