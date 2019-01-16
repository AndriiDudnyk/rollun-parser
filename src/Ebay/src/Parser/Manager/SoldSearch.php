<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\Ebay\Parser\Manager;

use rollun\datastore\Rql\RqlQuery;
use rollun\parser\DataStore\Entity\LoaderTaskInterface;
use rollun\parser\DataStore\Entity\ParserTaskInterface;
use rollun\parser\Parser\Parser\ParserInterface;
use Psr\Log\LoggerInterface;
use rollun\service\Parser\Ebay\DataStore\Entity\Interfaces\SoldProductInterface;
use rollun\parser\Parser\Manager\Base as BaseParserManager;
use Xiag\Rql\Parser\Node\Query\LogicOperator\AndNode;
use Xiag\Rql\Parser\Node\Query\ScalarOperator\EqNode;

class SoldSearch extends BaseParserManager
{
    const DEF_MAX_CORRUPT_RECORDS = 30;

    protected $loaderTask;

    public function __construct(
        ParserInterface $parser,
        SoldProductInterface $entity,
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

        foreach ($products as $key => $record) {
            if ($corruptCount >= $maxCorruptRecords) {
                $this->logger->warning('Stop parsing. Exceeded max corrupt count');
                break;
            }

            if (!$record['item_id'] || !$record['uri'] || !$record['seller'] || !$record['date']) {
                $maxCorruptRecords++;
                $this->logger->warning('Corrupted item found', [
                    'record' => $record,
                ]);

                if ($this->options['saveCorruptedProducts']) {
                    $checkedRecord = $record;
                } else {
                    continue;
                }
            } else {
                $checkedRecord = $record;
            }

//            $checkedRecord['date'] = (\DateTime::createFromFormat('F-d H:i', $checkedRecord['date']))->getTimestamp();
            $checkedRecords[] = $checkedRecord;
        }

        if ($result['nextPage'] && $this->options['throughPagination']) {
            $this->loaderTask->addLoaderTask($this->parser->getName(), $result['nextPage']);
        } elseif (!$result['nextPage']) {
            $this->logger->warning('Next page not found or it can be last one in ' . static::class);
        }

        return $checkedRecords;
    }

    protected function saveResult(array $products)
    {
        foreach ($products as $product) {
            if ($this->isNewSoldProduct($product)) {
                $this->entity->create($this->createRecordForStore($product));
            }
        }
    }

    protected function isNewSoldProduct($product)
    {
        $query = new RqlQuery();
        $query->setQuery(new AndNode([
            new EqNode(SoldProductInterface::COLUMN_ITEM_ID, $product['item_id']),
            new EqNode(SoldProductInterface::COLUMN_DATE, $product['date'])
        ]));

        $soldProducts = $this->entity->query($query);

        return !count($soldProducts);
    }

    public function __sleep()
    {
        $properties = parent::__sleep();

        return array_merge($properties, ['loaderTask']);
    }

    protected function createRecordForStore($record)
    {
        return [
            SoldProductInterface::COLUMN_ITEM_ID => $record['item_id'] ?? '',
            SoldProductInterface::COLUMN_DATE => $record['data'] ?? '',
            SoldProductInterface::COLUMN_URI => $record['uri'] ?? '',
            SoldProductInterface::COLUMN_PRICE => $record['price'] ?? '',
            SoldProductInterface::COLUMN_SELLER => $record['seller'] ?? '',
            SoldProductInterface::COLUMN_SHIPPING => $record['shipping'] ?? '',
            SoldProductInterface::COLUMN_TITLE => $record['title'] ?? '',
        ];
    }
}
