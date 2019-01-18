<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\Loader\Manager;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Log\LoggerInterface;
use ReflectionException;
use rollun\dic\InsideConstruct;
use rollun\parser\DataStore\Entity\LoaderTaskInterface;
use rollun\parser\DataStore\Entity\ParserTaskInterface;
use rollun\parser\Loader\Loader\LoaderException;
use rollun\parser\Loader\Loader\LoaderInterface;
use rollun\parser\Parser\Manager\ParserManagerInterface;
use rollun\datastore\Rql\RqlQuery;
use Xiag\Rql\Parser\Node\Query\LogicOperator\AndNode;
use Xiag\Rql\Parser\Node\Query\LogicOperator\OrNode;
use Xiag\Rql\Parser\Node\Query\ScalarOperator\EqNode;

class Base implements LoaderManagerInterface
{
    protected $loader;

    protected $parserTask;

    protected $loaderTask;

    protected $parserNames;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    protected $options;

    /**
     * Base constructor.
     * @param LoaderInterface $loader
     * @param LoaderTaskInterface $loaderTask
     * @param ParserTaskInterface $parserTask
     * @param array $parserNames
     * @param LoggerInterface|null $logger
     * @param array $options
     * @throws ReflectionException
     */
    public function __construct(
        LoaderInterface $loader,
        LoaderTaskInterface $loaderTask,
        ParserTaskInterface $parserTask,
        array $parserNames,
        LoggerInterface $logger = null,
        $options = []
    ) {
        InsideConstruct::setConstructParams(["logger" => LoggerInterface::class]);
        $this->parserNames = $parserNames;
        $this->loader = $loader;
        $this->loaderTask = $loaderTask;
        $this->parserTask = $parserTask;
        $this->options = $options;
    }

    public function __invoke()
    {
        $startTime = microtime(true);
        $failed = false;

        try {
            $loaderTaskId = $this->executeLoading();
        } catch (\Throwable $e) {
            $failed = true;
            $this->logger->critical('Failed invoke loading', ['exception' => $e]);
        }

        $endTime = microtime(true);
        $parseTime = $endTime - $startTime;
        $timed = 'Start at ' . date('m.d H:i:s', intval($startTime))
            . '. End at ' . date('m.d H:i:s', intval($endTime));

        if (isset($loaderTaskId)) {
            $isFailed = $failed ? ' and failed' : '';
            $message = "Loading task #{$loaderTaskId} take {$parseTime} microseconds{$isFailed}. {$timed}";
        } else {
            $message = "Loading without task take {$parseTime} microseconds. {$timed}";
        }

        $this->logger->info($message);
    }

    public function executeLoading()
    {
        if (!$loaderTask = $this->getNewLoaderTask()) {
            $this->logger->debug("Free task for '" . static::class . "' loader manager not found");

            return null;
        }

        $this->logger->debug("Loader start task #{$loaderTask['id']}");

        if (!$document = $this->load($loaderTask)) {
            $this->loaderTask->setStatus($loaderTask['id'], ParserTaskInterface::STATUS_FAILED);
            $this->logger->error("Loader CAN NOT LOAD document #{$loaderTask['id']}");
            return $loaderTask['id'];
        }

        $this->save($loaderTask, $document);
        $this->afterSave($loaderTask);

        return $loaderTask['id'];
    }

    protected function save($loaderTask, $document)
    {
        try {
            $this->processResult($loaderTask, $document);
            $this->loaderTask->setStatus($loaderTask['id'], LoaderTaskInterface::STATUS_SUCCESS);
            $this->logger->debug("Loader successfully finish task #{$loaderTask['id']}");
        } catch (\Throwable $e) {
            $this->loaderTask->setStatus($loaderTask['id'], LoaderTaskInterface::STATUS_FAILED);
            $this->logger->error("Loader failed SAVE loaded document #{$loaderTask['id']}", [
                'exception' => $e,
            ]);
        }
    }

    protected function load($loaderTask)
    {
        try {
            $this->loaderTask->setStatus($loaderTask['id'], LoaderTaskInterface::STATUS_IN_PROCESS);

            return $this->getDocument($loaderTask);
        } catch (\Throwable $e) {
            $status = $e instanceof LoaderException ? $e->getCode() : LoaderTaskInterface::STATUS_FAILED;
            $this->loaderTask->setStatus($loaderTask['id'], $status);
            $this->logger->error("Loader failed LOAD document #{$loaderTask['id']}", [
                'exception' => $e,
            ]);

            return null;
        }
    }

    /**
     * @param $loaderTask
     * @return string
     * @throws ClientExceptionInterface
     * @throws LoaderException
     */
    protected function getDocument($loaderTask)
    {
        $loader = $this->getLoader($loaderTask['options']);
        $document = $loader->load($loaderTask['uri']) ?? '';

        return $document;
    }

    protected function processResult($loaderTask, $document)
    {
        $parserTaskOptions = $loaderTask['options'][ParserManagerInterface::KEY_OPTIONS] ?? [];
        $this->parserTask->addParserTask($loaderTask['parser'], $document, $parserTaskOptions);
    }

    protected function getLoader(array $options): LoaderInterface
    {
        $loaderOptions = array_merge($options, $this->options);
        $loader = clone $this->loader;
        unset($loaderOptions[ParserManagerInterface::KEY_OPTIONS]);
        $loader->setOptions($loaderOptions);

        return $loader;
    }

    protected function afterSave($loaderTask)
    {
    }

    protected function repeatTask($loaderTask)
    {
        $this->loaderTask->create([
            'uri' => $loaderTask['uri'],
            'parser' => $loaderTask['parser'],
            'status' => LoaderTaskInterface::STATUS_NEW,
        ]);
    }

    protected function getNewLoaderTask(): ?array
    {
        $eqNodes = [];

        foreach ($this->parserNames as $parserName) {
            $eqNodes[] = new EqNode(LoaderTaskInterface::COLUMN_PARSER_NAME, $parserName);
        }

        $parserOrNode = new OrNode($eqNodes);

        $query = new RqlQuery();
        $query->setQuery(new AndNode([
            new EqNode(LoaderTaskInterface::COLUMN_STATUS, LoaderTaskInterface::STATUS_NEW),
            $parserOrNode,
        ]));

        $tasks = $this->loaderTask->query($query);

        if (!count($tasks)) {
            return null;
        }

        usort($tasks, function ($left, $right) {
            return $left['updated_at'] <=> $right['updated_at'];
        });

        return array_shift($tasks);
    }

    public function __sleep()
    {
        return ['parserTask', 'loaderTask', 'loader', 'parserNames'];
    }

    public function __wakeup()
    {
        InsideConstruct::initWakeup(['logger' => LoggerInterface::class]);
    }
}
