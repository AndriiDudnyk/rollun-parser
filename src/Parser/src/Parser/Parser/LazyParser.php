<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\Parser\Parser;

use rollun\parser\Parser\ParserResolver\ParserResolverInterface;

class LazyParser implements ParserInterface
{
    protected $parserName;

    protected $parserResolver;

    public function __construct(ParserResolverInterface $parserResolver, string $parserName)
    {
        $this->parserName = $parserName;
        $this->parserResolver = $parserResolver;
    }

    public function parse(string $data): array
    {
        return $this->parserResolver->getParser($data)->parse($data);
    }

    public function canParse(string $data): bool
    {
        return !empty($this->parserResolver->getParser($data));
    }

    public function getName(): string
    {
        return $this->parserName;
    }
}
