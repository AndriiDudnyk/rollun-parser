<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Parser;

abstract class AbstractParser implements ParserInterface
{
    public const PARSER_NAME = null;

    public function getName(): string
    {
        return static::PARSER_NAME;
    }
}
