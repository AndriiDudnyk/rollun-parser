<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\Loader;

use Psr\Log\LoggerInterface;
use rollun\datastore\Rql\RqlQuery;
use rollun\dic\InsideConstruct;
use rollun\parser\DataStore\Entity\LoaderTaskInterface;
use Xiag\Rql\Parser\Node\Query\LogicOperator\AndNode;
use Xiag\Rql\Parser\Node\Query\ScalarOperator\EqNode;
use Xiag\Rql\Parser\Node\Query\ScalarOperator\LtNode;

class Heartbeat
{
    const HEARTBEAT_TIMEOUT = 1;
    const HEARTBEAT_MAX_ATTEMPTS = 5;

    /**
     * @var LoaderTaskInterface
     */
    protected $loaderTask;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(LoaderTaskInterface $loaderTask = null, LoggerInterface $logger = null)
    {
        InsideConstruct::setConstructParams([
            'logger' => LoggerInterface::class,
            'loaderTask' => LoaderTaskInterface::class
        ]);
    }

    public function __invoke()
    {
        $ltNode = new LtNode(LoaderTaskInterface::COLUMN_HEARTBEAT_EXPIRATION, time());
        $eqNode = new EqNode(LoaderTaskInterface::COLUMN_STATUS, LoaderTaskInterface::STATUS_FAILED);
        $query = new RqlQuery();
        $query->setQuery(new AndNode([$ltNode, $eqNode]));

        $loaderTasks = $this->loaderTask->query($query);

        if (count($loaderTasks)) {
            foreach ($loaderTasks as $loaderTask) {
                $this->heartbeat($loaderTask);
            }
        }
    }

    public function heartbeat($loaderTask)
    {
        $attempts = $loaderTask[LoaderTaskInterface::COLUMN_HEARTBEAT_ATTEMPT];

        if ($attempts > self::HEARTBEAT_MAX_ATTEMPTS) {
            return;
        }

        if ($attempts == self::HEARTBEAT_MAX_ATTEMPTS) {
            $this->logger->critical(
                "Can't reanimate loader task with id #{$loaderTask['id']} using {$attempts} attempts"
            );
            $status = LoaderTaskInterface::STATUS_FAILED;
        } else {
            $status = LoaderTaskInterface::STATUS_NEW;
        }

        $attempts++;
        $this->loaderTask->update([
            'id' => $loaderTask['id'],
            LoaderTaskInterface::COLUMN_HEARTBEAT_ATTEMPT => $attempts,
            LoaderTaskInterface::COLUMN_STATUS => $status,
            LoaderTaskInterface::COLUMN_HEARTBEAT_EXPIRATION => time() + self::HEARTBEAT_TIMEOUT,
        ]);
    }

    public function __sleep()
    {
        return ['loaderTask'];
    }

    public function __wakeup()
    {
        InsideConstruct::initWakeup(['logger' => LoggerInterface::class]);
    }
}
