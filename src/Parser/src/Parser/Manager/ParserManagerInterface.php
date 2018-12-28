<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\Parser\Manager;

interface ParserManagerInterface
{
    const KEY_OPTIONS = 'parserManagerOptions';

    public function executeParsing();
}
