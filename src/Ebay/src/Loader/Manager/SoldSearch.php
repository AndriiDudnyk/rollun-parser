<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\Ebay\Loader\Manager;

use rollun\parser\Loader\Loader\LoaderInterface;

class SoldSearch extends Search
{
    public function executeLoading()
    {
        $this->options[LoaderInterface::ALLOW_REDIRECT_OPTION] = true;
        parent::executeLoading();
    }

    protected function afterSave($loaderTask)
    {
        $this->repeatTask($loaderTask);
    }
}
