<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\Ebay\Parser\Manager\Search;

use Psr\Log\LoggerInterface;
use rollun\parser\DataStore\Entity\LoaderTaskInterface;
use rollun\parser\DataStore\Entity\ParserTaskInterface;
use rollun\parser\Loader\Loader\LoaderInterface;
use rollun\parser\Parser\Parser\ParserInterface;
use rollun\parser\Parser\Manager\Base as BaseParserManager;
use rollun\service\Parser\Ebay\DataStore\Entity\Interfaces\ProductSearchInterface;
use rollun\service\Parser\Ebay\Parser\Parser\Product as ProductParser;
use rollun\service\Parser\Ebay\Parser\Parser\Compatible as CompatibleParser;

class Base extends BaseParserManager
{
    const DEF_MAX_CORRUPT_RECORDS = 30;

    protected $loaderTask;

    public function __construct(
        ParserInterface $parser,
        ProductSearchInterface $entity,
        ParserTaskInterface $parserTask,
        LoaderTaskInterface $loaderTask,
        array $options,
        LoggerInterface $logger = null
    ) {
        parent::__construct($parser, $entity, $parserTask, $options);
        $this->loaderTask = $loaderTask;
    }

    protected function processResult(array $records): array
    {
        $maxCorruptRecords = intval($this->options['maxCorruptRecords'] ?? self::DEF_MAX_CORRUPT_RECORDS);
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
        $this->loaderTask->addLoaderTask(ProductParser::PARSER_NAME, $productUri, [
            LoaderInterface::USE_PROXY_OPTION => 1,
            LoaderInterface::FAKE_USER_AGENT_OPTION => 1,
        ]);

        $compatibleUri = rtrim($this->options['compatibleUri']) . $itemId;
        $this->loaderTask->addLoaderTask(CompatibleParser::PARSER_NAME, $compatibleUri, [
            LoaderInterface::USE_PROXY_OPTION => 1,
            LoaderInterface::FAKE_USER_AGENT_OPTION => 1,
            self::KEY_OPTIONS => [
                'productId' => $itemId
            ]
        ]);
    }

    public function __sleep()
    {
        $properties = parent::__sleep();

        return array_merge($properties, ['loaderTask']);
    }

    public function __wakeup()
    {
        parent::__wakeup();
    }

    protected function saveResult(array $records)
    {
        foreach ($records as $record) {
            $record[ProductSearchInterface::COLUMN_WATCH] = $record['watch'] ?? '';
            $record[ProductSearchInterface::COLUMN_SOLD] = $record['sold'] ?? '';
            $this->entity->create($record);
        }
    }
}
