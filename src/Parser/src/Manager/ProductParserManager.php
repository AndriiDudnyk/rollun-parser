<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Manager;

use Parser\DataStore\Document;
use Parser\DataStore\Product;
use Parser\Parser\ParserInterface;
use Psr\Log\LoggerInterface;

class ProductParserManager extends BaseParserManager
{
    const DEF_MAX_CORRUPT_RECORDS = 30;

    public function __construct(
        ParserInterface $parser,
        Product $parseResultDataStore,
        Document $documentDataStore,
        array $options,
        LoggerInterface $logger = null
    ) {
        parent::__construct($parser, $parseResultDataStore, $documentDataStore, $options);
    }

    protected function processResult(array $records): ?array
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

    protected function saveResult(array $uris)
    {
        $this->parseResultDataStore->create($uris);
    }
}
