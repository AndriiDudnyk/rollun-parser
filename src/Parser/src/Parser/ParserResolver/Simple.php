<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\Parser\ParserResolver;

use InvalidArgumentException;
use rollun\parser\Parser\Parser\ParserInterface;

class Simple implements ParserResolverInterface
{
    protected $parsers;

    public function __construct(array $parsers)
    {
        foreach ($parsers as $parser) {
            if (!$parser instanceof ParserInterface) {
                throw new InvalidArgumentException('Parsers should implement ' . ParserInterface::class);
            }
        }

        $this->parsers = $parsers;
    }

    public function getParser($document): ?ParserInterface
    {
        foreach ($this->parsers as $parser) {
            if ($parser->canParse($document)) {
                return $parser;
            }
        }

        return null;
    }
}
