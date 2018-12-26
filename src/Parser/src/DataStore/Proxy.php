<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\DataStore;

use Psr\Log\LoggerInterface;
use rollun\datastore\DataStore\Aspect\AspectAbstract;
use rollun\datastore\DataStore\Interfaces\DataStoresInterface;
use rollun\datastore\Rql\RqlQuery;
use rollun\dic\InsideConstruct;
use Xiag\Rql\Parser\Node\LimitNode;
use Xiag\Rql\Parser\Node\Query\LogicOperator\AndNode;
use Xiag\Rql\Parser\Node\Query\ScalarOperator\EqNode;
use Parser\Parser\Proxy as ProxyParser;

class Proxy extends AspectAbstract
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    protected $taskDataStore;

    protected $proxyListUri;

    protected $taskOptions;

    public function __construct(
        DataStoresInterface $dataStore,
        Task $taskDataStore,
        string $proxyListUri,
        array $taskOptions,
        LoggerInterface $logger = null
    ) {
        parent::__construct($dataStore);
        $this->taskDataStore = $taskDataStore;
        $this->taskOptions = $taskOptions;
        $this->proxyListUri = $proxyListUri;
        InsideConstruct::setConstructParams(['logger' => LoggerInterface::class]);
    }

    public function create($itemData, $rewriteIfExist = false)
    {
        if (isset($itemData[$this->dataStore->getIdentifier()])) {
            return parent::create($itemData, $rewriteIfExist);
        }

        if ($record = $this->getRecordByFields($itemData)) {
            return $record;
        }

        $itemData['created_at'] = time();

        return parent::create($itemData, $rewriteIfExist);
    }

    protected function getRecordByFields($itemData)
    {
        $eqNodes = array_map(function ($field, $value) {
            return new EqNode($field, $value);
        }, array_keys($itemData), array_values($itemData));
        $query = new RqlQuery();
        $query->setQuery(new AndNode($eqNodes));

        $proxies = $this->dataStore->query($query);

        if (count($proxies)) {
            return array_shift($proxies);
        }

        return null;
    }

    public function addNewUri($uri)
    {
        $record = $this->create([
            'uri' => $uri,
            'is_used' => 0,
        ]);

        return $record[$this->dataStore->getIdentifier()];
    }

    public function update($itemData, $createIfAbsent = false)
    {
        unset($itemData['created_at']);

        return parent::update($itemData, $createIfAbsent);
    }

    public function getUnusedProxy($createTaskIfNotExist = false): ?string
    {
        $proxies = $this->query(new RqlQuery('eqf(is_used)&limit(1)'));

        if (!count($proxies) && $createTaskIfNotExist) {
            $taskId = $this->taskDataStore->addTask(ProxyParser::PARSER_NAME, $this->proxyListUri, $this->taskOptions);
            $this->logger->info("Create new task #{$taskId}");

            return null;
        }

        $proxy = current($proxies);

        return $proxy['uri'];
    }

    public function setUsedProxy(string $uri)
    {
        $query = new RqlQuery();
        $query->setQuery(new EqNode('uri', $uri));
        $query->setLimit(new LimitNode(1));

        $proxies = $this->query($query);
        $proxy = current($proxies);
        $this->update([
            'id' => $proxy['id'],
            'is_used' => 1,
        ]);
    }

    public function __sleep()
    {
        return ['taskDataStore', 'proxyListUri', 'dataStore', 'taskOptions'];
    }

    public function __wakeup()
    {
        InsideConstruct::initWakeup(['logger' => LoggerInterface::class]);
    }
}
