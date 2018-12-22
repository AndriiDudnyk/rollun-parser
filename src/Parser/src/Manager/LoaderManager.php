<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Manager;

use Parser\DataStore\DocumentDataStoreInterface;
use Parser\Loader\LoaderInterface;
use Psr\Log\LoggerInterface;
use ReflectionException;
use rollun\datastore\DataStore\Interfaces\DataStoresInterface;
use rollun\datastore\Rql\RqlQuery;
use rollun\dic\InsideConstruct;

class LoaderManager
{
    protected $loader;

    protected $htmlDataStore;

    protected $taskDataStore;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * LoaderManager constructor.
     * @param LoaderInterface $loader
     * @param DataStoresInterface $taskDataStore (!) should have to be able serialize itself
     * @param DocumentDataStoreInterface $htmlDataStore (!) should have to be able serialize itself
     * @param LoggerInterface|null $logger
     * @throws ReflectionException
     */
    public function __construct(
        LoaderInterface $loader,
        DataStoresInterface $taskDataStore,
        DocumentDataStoreInterface $htmlDataStore,
        LoggerInterface $logger = null
    ) {
        InsideConstruct::setConstructParams(["logger" => LoggerInterface::class]);
        $this->loader = $loader;
        $this->taskDataStore = $taskDataStore;
        $this->htmlDataStore = $htmlDataStore;
    }

    public function __invoke()
    {
        if (!$task = $this->getTask()) {
            $this->logger->info("Free task for loader manager not found");
            return;
        }

        $this->logger->info("Loader start task #{$task['id']}");
        $loader = clone $this->loader;
        $loader->setOptions($task['options']);

        try {
            $html = $loader->load($task['uri']) ?? '';
            $this->htmlDataStore->create([
                'html' => $html,
                'parser' => $task['parser'],
                'created_at' => time(),
                'status' => 0,
            ]);
            $this->taskDataStore->update([
                'id' => $task['id'],
                'status' => 1,
            ]);
            $this->logger->info("Loader successfully finish task #{$task['id']}");
        } catch (\Throwable $t) {
            $this->logger->info("Loader failed task #{$task['id']}", [
                'exception' => $t,
            ]);
        }
    }

    protected function getTask(): ?array
    {
        $tasks = $this->taskDataStore->query(new RqlQuery('eq(status,0)'));

        if (!count($tasks)) {
            return null;
        }

        usort($tasks, function ($left, $right) {
            return $left['time'] <=> $right['time'];
        });

        return array_shift($tasks);
    }

    public function __sleep()
    {
        return ['htmlDataStore', 'taskDataStore', 'loader'];
    }

    public function __wakeup()
    {
        InsideConstruct::initWakeup(['logger' => LoggerInterface::class]);
    }
}
