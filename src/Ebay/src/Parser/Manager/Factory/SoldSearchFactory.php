<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\Ebay\Parser\Manager\Factory;

use InvalidArgumentException;
use rollun\service\Parser\Ebay\Parser\Manager\SoldSearch as SoldEbayMotorsParserManager;

class SoldSearchFactory extends SearchFactory
{
    protected function checkClass($class)
    {
        if (!is_a($class, SoldEbayMotorsParserManager::class, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    "Expected class %s, given $class",
                    SoldEbayMotorsParserManager::class
                )
            );
        }
    }
}
