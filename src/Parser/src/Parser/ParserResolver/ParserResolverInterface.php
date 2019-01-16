<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\Parser\ParserResolver;

use rollun\parser\Parser\Parser\ParserInterface;

interface ParserResolverInterface
{
    public function getParser($document): ?ParserInterface;
}
