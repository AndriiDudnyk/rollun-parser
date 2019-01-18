<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\FreeProxyList\DataStore\Entity;

use rollun\datastore\DataStore\Interfaces\DataStoresInterface;

interface ProxyInterface extends DataStoresInterface
{
    const COLUMN_URI = 'uri';
    const COLUMN_IS_USED = 'is_used';
    const COLUMN_CREATED_AT = 'created_at';
    const COLUMN_USAGE = 'usage';

    public function addNewUri($uri);

    public function setUsedProxy(string $uri);

    public function getUnusedProxy($createTaskIfNotExist = false): ?string;
}
