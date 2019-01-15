<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\Parser\ParseResolver;

use rollun\parser\Parser\Parser\ParserInterface;

interface ParseResolverInterface
{
    public function getParser($document): ParserInterface;
}
