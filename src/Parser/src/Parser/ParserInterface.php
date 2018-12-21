<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Parser;

interface ParserInterface
{
    /**
     * @param string $html
     * @return array
     */
    public function parse(string $html): array;
}
