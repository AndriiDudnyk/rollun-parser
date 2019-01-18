<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\FreeProxyList\Loader\Manager;

use rollun\parser\Loader\Loader\LoaderInterface;
use rollun\parser\Loader\Manager\Base;

class Proxy extends Base
{
    public function executeLoading()
    {
        $this->options[LoaderInterface::USE_PROXY_OPTION] = 0;
        parent::executeLoading();
    }
}
