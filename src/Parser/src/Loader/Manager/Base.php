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

    /**
     * Base constructor.
     * @param LoaderInterface $loader
     * @param LoaderTaskInterface $loaderTask
     * @param ParserTaskInterface $parserTask
     * @param array $parserNames
     * @param LoggerInterface|null $logger
     * @throws ReflectionException
     */
    public function __construct(
        LoaderInterface $loader,
        LoaderTaskInterface $loaderTask,
        ParserTaskInterface $parserTask,
        array $parserNames,
        LoggerInterface $logger = null
    ) {
        InsideConstruct::setConstructParams(["logger" => LoggerInterface::class]);
        $this->parserNames = $parserNames;
        $this->loader = $loader;
        $this->loaderTask = $loaderTask;
        $this->parserTask = $parserTask;
    }

    public function __invoke()
    {
        $this->executeLoading();
    }

    public function executeLoading()
    {
        if (!$loaderTask = $this->getNewLoaderTask()) {
            $this->logger->info("Free task for '" . static::class . "' loader manager not found");
            return;
        }

        $this->logger->info("Loader start task #{$loaderTask['id']}");
        $status = null;

        try {
            $this->loaderTask->setStatus($loaderTask['id'], LoaderTaskInterface::STATUS_IN_PROCESS);
            $document = $this->getDocument($loaderTask);
            $this->processResult($loaderTask, $document);

            $this->loaderTask->setStatus($loaderTask['id'], LoaderTaskInterface::STATUS_SUCCESS);

            $this->logger->info("Loader successfully finish task #{$loaderTask['id']}");
        } catch (LoaderException | ClientExceptionInterface $e) {
            if ($e instanceof LoaderException) {
                $status = $e->getCode();
            } else {
                $status = LoaderTaskInterface::STATUS_FAILED;
            }

            $this->loaderTask->setStatus($loaderTask['id'], $status);
            $this->logger->info("Loader failed task #{$loaderTask['id']}", [
                'exception' => $e,
            ]);
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

    protected function getLoader($options): LoaderInterface
    {
        $loader = clone $this->loader;
        unset($options[ParserManagerInterface::KEY_OPTIONS]);
        $loader->setOptions($options);

        return $loader;
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
            $parserOrNode
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
