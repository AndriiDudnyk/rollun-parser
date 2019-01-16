<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\Ebay\Parser\Manager;

use Psr\Log\LoggerInterface;
use rollun\parser\DataStore\Entity\LoaderTaskInterface;
use rollun\parser\DataStore\Entity\ParserTaskInterface;
use rollun\parser\Parser\Parser\ParserInterface;
use rollun\parser\Parser\Manager\Base as BaseParserManager;
use rollun\service\Parser\Ebay\DataStore\Entity\Interfaces\ProductInterface;
use rollun\service\Parser\Ebay\Parser\Parser\Product as ProductParser;

class Search extends BaseParserManager
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

        if (!$products) {
            $this->logger->error("Parser DID NOT PARSE any data from document #{$parserTask['id']}");
            $this->parserTask->setStatus($parserTask['id'], ParserTaskInterface::STATUS_NOT_PARSED);
            die;
        }

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
                } else {
                    continue;
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
            $this->loaderTask->addLoaderTask($this->parser->getName(), $result['nextPage']);
        } elseif (!$result['nextPage']) {
            $this->logger->warning('Next page not found or it can be last one in ' . static::class);
        }

        return $checkedRecords;
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
            $product = $this->createRecordForStore($record);

            if ($this->entity->has($product['id'])) {
                $this->logger->notice("Product with id #{$product['id']} already exist");
            }

            $this->entity->create($product, true);
        }
    }

    protected function createRecordForStore($record)
    {
        return [
            'id' => $record['item_id'],
            ProductInterface::COLUMN_WATCH => $record['watch'] ?? '',
            ProductInterface::COLUMN_SOLD => $record['sold'] ?? '',
            ProductInterface::COLUMN_CATEGORY => $record['category'] ?? '',
            ProductInterface::COLUMN_IMGS => $record['imgs'] ?? '',
            ProductInterface::COLUMN_PRICE => $record['price'] ?? '',
            ProductInterface::COLUMN_SHIPPING => $record['shipping'] ?? '',
            ProductInterface::COLUMN_SPECS => $record['specs'] ?? '',
            ProductInterface::COLUMN_TITLE => $record['title'] ?? '',
            ProductInterface::COLUMN_URI => $record['uri'] ?? '',
            ProductInterface::COLUMN_SELLER => $record['seller'] ?? '',
            ProductInterface::COLUMN_EBAY_ID => $record['ebay_id'] ?? '',
        ];
    }
}
