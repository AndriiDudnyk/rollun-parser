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
use rollun\parser\Loader\Loader\LoaderInterface;
use rollun\parser\Parser\Manager\ParserManagerInterface;
use RuntimeException;

class Base implements LoaderManagerInterface
{
    protected $loader;

    protected $parserTask;

    protected $loaderTask;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Base constructor.
     * @param LoaderInterface $loader
     * @param LoaderTaskInterface $loaderTask
     * @param ParserTaskInterface $parserTask
     * @param LoggerInterface|null $logger
     * @throws ReflectionException
     */
    public function __construct(
        LoaderInterface $loader,
        LoaderTaskInterface $loaderTask,
        ParserTaskInterface $parserTask,
        LoggerInterface $logger = null
    ) {
        InsideConstruct::setConstructParams(["logger" => LoggerInterface::class]);
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
            $this->logger->info("Free task for loader manager not found");
            return;
        }

        $this->logger->info("Loader start task #{$loaderTask['id']}");
        $status = null;

        try {
            $this->loaderTask->setStatus($loaderTask['id'], LoaderTaskInterface::STATUS_SUCCESS);
            $document = $this->getDocument($loaderTask);
            $this->processResult($loaderTask, $document);

            $this->loaderTask->setStatus($loaderTask['id'], LoaderTaskInterface::STATUS_SUCCESS);

            $this->logger->info("Loader successfully finish task #{$loaderTask['id']}");
        } catch (RuntimeException | ClientExceptionInterface $clientExc) {
            $this->loaderTask->setStatus($loaderTask['id'], LoaderTaskInterface::STATUS_FAILED);
            $this->logger->info("Loader failed task #{$loaderTask['id']}", [
                'exception' => $clientExc,
            ]);
        }
    }

    /**
     * @param $loaderTask
     * @return string
     * @throws ClientExceptionInterface
     * @throws RuntimeException
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

    protected function getNewLoaderTask($parserName = null): ?array
    {
        $fields[LoaderTaskInterface::COLUMN_STATUS] = LoaderTaskInterface::STATUS_NEW;

        if ($parserName) {
            $fields[LoaderTaskInterface::COLUMN_PARSER_NAME] = $parserName;
        }

        $tasks = $this->loaderTask->getLoaderTaskByFields($fields);

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
        return ['parserTask', 'loaderTask', 'loader'];
    }

    public function __wakeup()
    {
        InsideConstruct::initWakeup(['logger' => LoggerInterface::class]);
    }
}
