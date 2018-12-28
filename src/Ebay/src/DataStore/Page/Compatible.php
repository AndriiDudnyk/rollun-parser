<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\Ebay\DataStore\Page;

use rollun\parser\DataStore\Page\Base;
use rollun\parser\Loader\Loader\LoaderInterface;
use rollun\service\Parser\Ebay\Parser\Parser\Compatible as CompatibleParser;

class Compatible extends Base
{
    protected $searchLoaderHelper;

    public function __construct(LoaderInterface $loader, CompatibleParser $parser)
    {
        parent::__construct($loader, $parser);
    }
}
