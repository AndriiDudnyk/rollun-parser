<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\Parser\ParseResolver;

use rollun\parser\Parser\Parser\ParserInterface;
use rollun\parser\Parser\Parser\ParserPluginManager;
use RuntimeException;

class Simple implements ParseResolverInterface
{
    protected $possibleParsers;

    protected $parserPluginManager;

    public function __construct(ParserPluginManager $parserPluginManager, array $possibleParsers)
    {
        $this->parserPluginManager = $parserPluginManager;
        $this->possibleParsers = $possibleParsers;
    }

    public function getParser($document): ParserInterface
    {
        foreach ($this->possibleParsers as $possibleParser) {
            /** @var ParserInterface $parser */
            $parser = $this->parserPluginManager->get($possibleParser);

            if ($parser->canParse($document)) {
                return $parser;
            }
        }

        throw new RuntimeException("Failed find correct parser for document");
    }
}
