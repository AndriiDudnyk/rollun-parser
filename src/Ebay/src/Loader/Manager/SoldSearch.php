<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\Ebay\Loader\Manager;

class SoldSearch extends Search
{
    protected function afterSave($loaderTask)
    {
        $this->repeatTask($loaderTask);
    }
}
