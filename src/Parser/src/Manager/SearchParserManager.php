<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Manager;

use Parser\DataStore\DocumentDataStoreInterface;
use Parser\Parser\ParserInterface;
use Parser\Parser\ProductParser;
use Psr\Log\LoggerInterface;
use rollun\datastore\DataStore\Interfaces\DataStoresInterface;

class SearchParserManager extends BaseParserManager
{
    const DEF_MAX_CORRUPT_RECORDS = 30;

    protected $taskDataStore;

    public function __construct(
        ParserInterface $parser,
        DataStoresInterface $parseResultDataStore,
        DocumentDataStoreInterface $documentDataStore,
        DataStoresInterface $taskDataStore,
        array $options,
        LoggerInterface $logger = null
    ) {
        parent::__construct($parser, $parseResultDataStore, $documentDataStore, $options, $logger);
        $this->taskDataStore = $taskDataStore;
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

            if (!isset($record['item_id']) || !isset($record['url'])) {
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

            if (!$this->options['createProductParseTask']) {
                continue;
            }

            $itemId = $record['item_id'];
            $productUri = rtrim($this->options['productUri'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $itemId;

            $this->taskDataStore->create([
                'parser' => ProductParser::PARSER_NAME,
                'uri' => $productUri,
                'created_at' => time(),
                'options' => [
                    'useProxy' => 1,
                    'fakeUserAgent' => 1
                ],
                'status' => 0
            ]);
        }

        return $records;
    }

    public function __sleep()
    {
        $properties = parent::__sleep();
        return array_merge($properties, ['options', 'taskDataStore']);
    }
}
