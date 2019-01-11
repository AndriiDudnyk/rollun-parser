<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\DataStore;

use Psr\Log\LoggerInterface;
use rollun\datastore\DataStore\SerializedDbTable;
use rollun\dic\InsideConstruct;
use Zend\Db\TableGateway\TableGateway;

class LoggedDbTable extends SerializedDbTable
{
    protected $logger;

    public function __construct(TableGateway $tableGateway, LoggerInterface $logger = null)
    {
        InsideConstruct::setConstructParams(["logger" => LoggerInterface::class]);
        parent::__construct($tableGateway);
        $this->logger = $logger;
    }

    public function create($itemData, $rewriteIfExist = false)
    {
        try {
            return parent::create($itemData, $rewriteIfExist);
        } catch (\Throwable $e) {
            $this->logger->warning(
                "Failed create record for table {$this->dbTable->getTable()}. Reason {$e->getMessage()}",
                [
                    'itemData' => $itemData,
                    'rewriteIfExist' => $rewriteIfExist,
                ]
            );
            throw $e;
        }
    }

    public function update($itemData, $createIfAbsent = false)
    {
        try {
            return parent::update($itemData, $createIfAbsent);
        } catch (\Throwable $e) {
            $this->logger->warning(
                "Failed update record for table {$this->dbTable->getTable()}. Reason {$e->getMessage()}",
                [
                    'itemData' => $itemData,
                    'createIfAbsent' => $createIfAbsent,
                ]
            );
            throw $e;
        }
    }

    public function __wakeup()
    {
        parent::__wakeup();
        InsideConstruct::initWakeup([
            "logger" => LoggerInterface::class,
        ]);
    }
}
