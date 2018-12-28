<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\Ebay\Parser\Manager;

use InvalidArgumentException;
use rollun\parser\DataStore\Entity\ParserTaskInterface;
use rollun\service\Parser\Ebay\DataStore\Entity\Interfaces\CompatibleInterface;
use rollun\service\Parser\Ebay\Parser\Parser\Compatible as CompatibleParser;
use Psr\Log\LoggerInterface;
use rollun\parser\Parser\Manager\Base as BaseParserManager;

class Compatible extends BaseParserManager
{
    public function __construct(
        CompatibleParser $parser,
        CompatibleInterface $entity,
        ParserTaskInterface $parserTask,
        array $options,
        LoggerInterface $logger = null
    ) {
        parent::__construct($parser, $entity, $parserTask, $options);
    }

    protected function saveResult(array $records)
    {
        if (!$productId = $this->options['productId'] ?? null) {
            throw new InvalidArgumentException("Invalid option 'productId'");
        }

        foreach ($records as $record) {
            $this->entity->create(array_merge([
                'product_id' => $productId,
            ], $record));
        }
    }
}
