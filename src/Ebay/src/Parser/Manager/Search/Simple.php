<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\Ebay\Parser\Manager\Search;

use Psr\Log\LoggerInterface;
use rollun\parser\DataStore\Entity\LoaderTaskInterface;
use rollun\parser\DataStore\Entity\ParserTaskInterface;
use rollun\service\Parser\Ebay\DataStore\Entity\Interfaces\ProductSearchInterface;
use rollun\service\Parser\Ebay\Parser\Parser\Search\Simple as SimpleSearchParser;

class Simple extends Base
{
    public function __construct(
        SimpleSearchParser $parser,
        ProductSearchInterface $entity,
        ParserTaskInterface $parserTask,
        LoaderTaskInterface $loaderTask,
        array $options,
        LoggerInterface $logger = null
    ) {
        parent::__construct($parser, $entity, $parserTask, $loaderTask, $options);
    }
}
