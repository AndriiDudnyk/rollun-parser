<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\Parser\Parser;

interface ParserInterface
{
    /**
     * @param string $data
     * @return array
     */
    public function parse(string $data): array;

    /**
     * @param string $data
     * @return bool
     */
    public function canParse(string $data): bool;

    /**
     * @return string
     */
    public function getName(): string;
}
