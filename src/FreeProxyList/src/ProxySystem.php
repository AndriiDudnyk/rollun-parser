<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\FreeProxyList;

use rollun\service\Parser\FreeProxyList\DataStore\Entity\ProxyInterface;

class ProxySystem
{
    protected $proxy;

    const MAX_LEVEL = 10;
    const MIN_LEVEL = 1;

    public function __construct(ProxyInterface $proxy)
    {
        $this->proxy = $proxy;
    }

    public function get()
    {

    }

    public function upgrade()
    {

    }

    public function downgrade()
    {

    }

    protected function
}
