<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\Ebay\Parser\Manager;

use rollun\parser\DataStore\Entity\ParserTaskInterface;
use rollun\service\Parser\Ebay\DataStore\Entity\Interfaces\ProductInterface;
use rollun\service\Parser\Ebay\Parser\Parser\Product as ProductParser;
use Psr\Log\LoggerInterface;
use rollun\parser\Parser\Manager\Base as BaseParserManager;

class Product extends BaseParserManager
{
    const DEF_MAX_CORRUPT_RECORDS = 30;

    public function __construct(
        ProductParser $parser,
        ProductInterface $entity,
        ParserTaskInterface $parserTask,
        array $options,
        LoggerInterface $logger = null
    ) {
        parent::__construct($parser, $entity, $parserTask, $options);
    }

    protected function processResult(array $records): array
    {
        $maxCorruptRecords = intval($this->options['maxCorruptRecords']) ?? self::DEF_MAX_CORRUPT_RECORDS;
        $corruptCount = 0;
        $checkedRecords = [];

        foreach ($records as $record) {
            if ($corruptCount >= $maxCorruptRecords) {
                $this->logger->warning('Stop parsing. Exceeded max corrupt count');
                break;
            }

            if (!isset($record['title']) || !isset($record['price'])) {
                $maxCorruptRecords++;
                $this->logger->warning('Corrupted item found', [
                    'record' => $record
                ]);

                if ($this->options['saveCorruptedProducts']) {
                    $checkedRecords[] = $record;
                }
            } else {
                $checkedRecords[] = $record;
            }
        }

        return $records;
    }

    protected function saveResult(array $records)
    {
        $this->entity->create($records);
    }
}
