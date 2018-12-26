<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Manager;

use Parser\DataStore\Document;
use Parser\DataStore\Task;
use Parser\Loader\Loader;
use Parser\Parser\Compatible;
use Parser\Parser\ParserInterface;
use Parser\Parser\Product;
use Psr\Log\LoggerInterface;
use rollun\datastore\DataStore\Interfaces\DataStoresInterface;

class SearchParserManager extends BaseParserManager
{
    const DEF_MAX_CORRUPT_RECORDS = 30;

    protected $taskDataStore;

    public function __construct(
        ParserInterface $parser,
        DataStoresInterface $parseResultDataStore,
        Document $documentDataStore,
        Task $taskDataStore,
        array $options,
        LoggerInterface $logger = null
    ) {
        parent::__construct($parser, $parseResultDataStore, $documentDataStore, $options);
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
                    'record' => $record,
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

            $this->createNewTasks($record['item_id']);
        }

        return $records;
    }

    protected function createNewTasks($itemId)
    {
        $productUri = rtrim($this->options['productUri'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $itemId;
        $this->taskDataStore->addTask(Product::PARSER_NAME, $productUri, [
            Loader::USE_PROXY_OPTION => 1,
            Loader::FAKE_USER_AGENT_OPTION => 1,
        ]);

        $compatibleUri = rtrim($this->options['compatibleUri']) . $itemId;
        $this->taskDataStore->addTask(Compatible::PARSER_NAME, $compatibleUri, [
            Loader::USE_PROXY_OPTION => 1,
            Loader::FAKE_USER_AGENT_OPTION => 1,
            self::KEY_OPTIONS => [
                'productId' => $itemId
            ]
        ]);
    }

    public function __sleep()
    {
        $properties = parent::__sleep();

        return array_merge($properties, ['options', 'taskDataStore']);
    }

    public function __wakeup()
    {
        parent::__wakeup();
    }

    protected function saveResult(array $uris)
    {
        foreach ($uris as $record) {
            $this->parseResultDataStore->create($record);
        }
    }
}
