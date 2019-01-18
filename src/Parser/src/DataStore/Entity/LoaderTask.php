<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\DataStore\Entity;

use Psr\Log\LoggerInterface;
use rollun\datastore\DataStore\Interfaces\DataStoresInterface;
use rollun\datastore\Rql\RqlQuery;
use rollun\dic\InsideConstruct;
use rollun\parser\DataStore\JsonAspect;
use rollun\parser\Loader\Heartbeat;
use Xiag\Rql\Parser\Node\Query\LogicOperator\AndNode;
use Xiag\Rql\Parser\Node\Query\ScalarOperator\EqNode;
use Xiag\Rql\Parser\Node\Query\ScalarOperator\GtNode;
use Xiag\Rql\Parser\Node\SortNode;

class LoaderTask extends JsonAspect implements LoaderTaskInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(DataStoresInterface $dataStore, LoggerInterface $logger = null)
    {
        parent::__construct($dataStore);
        InsideConstruct::setConstructParams(['logger' => LoggerInterface::class]);
    }

    const MERGE_TIMEOUT = 60;

    protected function getJsonFields(): array
    {
        return [self::COLUMN_OPTIONS];
    }

    public function create($itemData, $rewriteIfExist = false)
    {
        if ($mergeLoaderTask = $this->getMergeLoaderTask($itemData)) {
            $this->logger->notice("Duplicate loader task", [
                'uri' => $itemData[self::COLUMN_URI] ?? '',
                'parser' => $itemData[self::COLUMN_PARSER_NAME] ?? '',
            ]);
            return $mergeLoaderTask;
        }

        $itemData[self::COLUMN_CREATED_AT] = microtime(true);
        $itemData[self::COLUMN_UPDATED_AT] = microtime(true);

        if (!isset($itemData[self::COLUMN_HEARTBEAT_EXPIRATION])) {
            $itemData[self::COLUMN_HEARTBEAT_EXPIRATION] = time() + Heartbeat::HEARTBEAT_TIMEOUT;
        }

        if (!isset($itemData[self::COLUMN_HEARTBEAT_ATTEMPT])) {
            $itemData[self::COLUMN_HEARTBEAT_ATTEMPT] = 0;
        }

        if (!isset($itemData[self::COLUMN_OPTIONS])) {
            $itemData[self::COLUMN_OPTIONS] = [];
        }

        return parent::create($itemData, $rewriteIfExist);
    }

    public function update($itemData, $createIfAbsent = false)
    {
        unset($itemData[self::COLUMN_CREATED_AT]);
        $itemData[self::COLUMN_UPDATED_AT] = microtime(true);

        return parent::update($itemData, $createIfAbsent);
    }

    public function getMergeLoaderTask($itemData)
    {
        $sortNode = new SortNode();
        $sortNode->addField(self::COLUMN_CREATED_AT, -1);
        $query = new RqlQuery();
        $query->setSort($sortNode);
        $query->setQuery(new AndNode([
            new GtNode(self::COLUMN_CREATED_AT, time() - self::MERGE_TIMEOUT),
            new EqNode(self::COLUMN_URI, $itemData[self::COLUMN_URI] ?? ''),
            new EqNode(self::COLUMN_PARSER_NAME, $itemData[self::COLUMN_PARSER_NAME] ?? ''),
        ]));

        $loaderTasks = $this->query($query);

        if (count($loaderTasks)) {
            return current($loaderTasks);
        }

        return null;
    }

    public function addLoaderTask($parser, $uri, $options = [])
    {
        $record = $this->create([
            self::COLUMN_PARSER_NAME => $parser,
            self::COLUMN_URI => $uri,
            self::COLUMN_STATUS => 0,
            self::COLUMN_OPTIONS => $options
        ]);

        return $record[$this->dataStore->getIdentifier()];
    }


    /**
     * @param $id
     * @param $status
     * @return void
     */
    public function setStatus($id, $status)
    {
        $this->update([
            $this->dataStore->getIdentifier() => $id,
            self::COLUMN_STATUS => $status,
        ]);
    }

    public function __wakeup()
    {
        InsideConstruct::initWakeup(['logger' => LoggerInterface::class]);
    }

    public function __sleep()
    {
        return ['dataStore'];
    }
}
