<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\Ebay\DataStore\Entity;

use rollun\parser\DataStore\JsonAspect;
use rollun\service\Parser\Ebay\DataStore\Entity\Interfaces\ProductInterface;

class Product extends JsonAspect implements ProductInterface
{
    public function getJsonFields(): array
    {
        return ['specs', 'shipping', 'imgs'];
    }
}
