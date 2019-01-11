<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\Ebay\Parser\Manager\Search;

use Psr\Log\LoggerInterface;
use rollun\parser\DataStore\Entity\LoaderTaskInterface;
use rollun\parser\DataStore\Entity\ParserTaskInterface;
use rollun\parser\Parser\Parser\ParserInterface;
use rollun\parser\Parser\Manager\Base as BaseParserManager;
use rollun\service\Parser\Ebay\DataStore\Entity\Interfaces\ProductInterface;
use rollun\service\Parser\Ebay\Parser\Parser\Product as ProductParser;
use rollun\service\Parser\Ebay\Parser\Parser\Search\Simple as SimpleSearchParser;
use rollun\service\Parser\Ebay\Parser\Parser\Search\EbayMotors as EbayMotorsSearchParser;

class Base extends BaseParserManager
{
    const DEF_MAX_CORRUPT_RECORDS = 30;

    protected $loaderTask;

    public function __construct(
        ParserInterface $parser,
        ProductInterface $entity,
        ParserTaskInterface $parserTask,
        LoaderTaskInterface $loaderTask,
        array $options,
        LoggerInterface $logger = null
    ) {
        parent::__construct($parser, $entity, $parserTask, $options);
        $this->loaderTask = $loaderTask;
    }

    protected function processResult(array $result, $parserTask): array
    {
        $maxCorruptRecords = intval($this->options['maxCorruptRecords'] ?? self::DEF_MAX_CORRUPT_RECORDS);
        $corruptCount = 0;
        $checkedRecords = [];
        $products = $result['products'];

        foreach ($products as $record) {
            if ($corruptCount >= $maxCorruptRecords) {
                $this->logger->warning('Stop parsing. Exceeded max corrupt count');
                break;
            }

            if (!isset($record['item_id']) || !isset($record['uri'])) {
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

        if ($result['nextPage'] && $this->options['throughPagination']) {
            $parser = $this->options['type'] == 'ebaySimple'
                ? SimpleSearchParser::PARSER_NAME
                : EbayMotorsSearchParser::PARSER_NAME;
            $this->loaderTask->addLoaderTask($parser, $result['nextPage']);
        } elseif (!$result['nextPage']) {
            $this->logger->warning('Next page not found or it can be last one in ' . static::class);
        }

        return $products;
    }

    protected function createNewTasks($itemId)
    {
        $productUri = rtrim($this->options['productUri'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $itemId;
        $this->loaderTask->addLoaderTask(ProductParser::PARSER_NAME, $productUri, [
            self::KEY_OPTIONS => [
                'item_id' => $itemId,
            ],
        ]);
    }

    public function __sleep()
    {
        $properties = parent::__sleep();

        return array_merge($properties, ['loaderTask']);
    }

    protected function saveResult(array $records)
    {
        foreach ($records as $record) {
            $record['id'] = $record['item_id'];
            unset($record['item_id']);

            if ($this->entity->has($record['id'])) {
                $this->logger->notice("Product with id #{$record['id']} already exist");
            }

            $record[ProductInterface::COLUMN_WATCH] = $record['watch'] ?? '';
            $record[ProductInterface::COLUMN_SOLD] = $record['sold'] ?? '';
            $this->entity->create($record, true);
        }
    }
}
