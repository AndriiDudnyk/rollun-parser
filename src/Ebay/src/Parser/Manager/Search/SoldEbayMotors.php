<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\Ebay\Parser\Manager\Search;

use rollun\datastore\Rql\RqlQuery;
use rollun\parser\DataStore\Entity\LoaderTaskInterface;
use rollun\parser\DataStore\Entity\ParserTaskInterface;
use rollun\service\Parser\Ebay\Parser\Parser\Search\SoldEbayMotors as SoldEbayMotorsSearchParser;
use Psr\Log\LoggerInterface;
use rollun\service\Parser\Ebay\DataStore\Entity\Interfaces\SoldProductInterface;
use rollun\parser\Parser\Manager\Base as BaseParserManager;
use Xiag\Rql\Parser\Node\Query\LogicOperator\AndNode;
use Xiag\Rql\Parser\Node\Query\ScalarOperator\EqNode;

class SoldEbayMotors extends BaseParserManager
{
    const DEF_MAX_CORRUPT_RECORDS = 30;

    protected $loaderTask;

    public function __construct(
        SoldEbayMotorsSearchParser $parser,
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
            $this->loaderTask->addLoaderTask(SoldEbayMotorsSearchParser::PARSER_NAME, $result['nextPage']);
        } elseif (!$result['nextPage']) {
            $this->logger->warning('Next page not found or it can be last one in ' . static::class);
        }

        return $checkedRecords;
    }

    protected function saveResult(array $products)
    {
        foreach ($products as $product) {
            if ($this->isNewSoldProduct($product)) {
                $this->entity->create($product);
            }
        }
    }

    protected function isNewSoldProduct($product)
    {
        $query = new RqlQuery();
        $query->setQuery(new AndNode([
            new EqNode('item_id', $product['item_id']),
            new EqNode('date', $product['date'])
        ]));

        $soldProducts = $this->entity->query($query);

        return !count($soldProducts);
    }

    public function __sleep()
    {
        $properties = parent::__sleep();

        return array_merge($properties, ['loaderTask']);
    }
}
