<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\Parser\Manager;

use Psr\Log\LoggerInterface;
use rollun\datastore\DataStore\Interfaces\DataStoresInterface;
use rollun\dic\InsideConstruct;
use rollun\parser\DataStore\Entity\ParserTaskInterface;
use rollun\parser\Parser\Parser\ParserInterface;
use RuntimeException;

abstract class Base implements ParserManagerInterface
{
    protected $parser;

    protected $entity;

    protected $parserTask;

    protected $options;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        ParserInterface $parser,
        DataStoresInterface $entity,
        ParserTaskInterface $parserTask,
        array $options,
        LoggerInterface $logger = null
    ) {
        InsideConstruct::setConstructParams(["logger" => LoggerInterface::class]);
        $this->parser = $parser;
        $this->entity = $entity;
        $this->parserTask = $parserTask;
        $this->options = $options;
    }

    public function setOptions($options, $override = true)
    {
        foreach ($options as $key => $option) {
            if ($override || !isset($this->options[$key])) {
                $this->options[$key] = $option;
            }
        }
    }

    public function __invoke()
    {
        $this->executeParsing();
    }

    public function executeParsing()
    {
        if (!$parserTask = $this->getNewParserTask($this->parser->getName())) {
            $this->logger->info("Free documents for parser manager not found");
            return;
        }

        $this->setOptions($parserTask['options']);
        $status = null;

        try {
            $this->parserTask->setStatus($parserTask['id'], ParserTaskInterface::STATUS_IN_PROCESS);
            $records = $this->parser->parse($parserTask['document']);

            if ($records) {
                $records = $this->processResult($records, $parserTask);
                $this->saveResult($records);
                $this->parserTask->setStatus($parserTask['id'], ParserTaskInterface::STATUS_SUCCESS);
            } else {
                $this->logger->warning("Parser did not parse any data from document #{$parserTask['id']}");
                $this->parserTask->setStatus($parserTask['id'], ParserTaskInterface::STATUS_NOT_PARSED);
            }
        } catch (\Throwable $t) {
            $this->parserTask->setStatus($parserTask['id'], ParserTaskInterface::STATUS_FAILED);
            $this->logger->warning("Parser failed parsing document #{$parserTask['id']}", [
                'exception' => $t,
            ]);
        }
    }

    protected function processResult(array $data, $parserTask): array
    {
        return $data;
    }

    abstract protected function saveResult(array $records);

    protected function getNewParserTask($parserName): ?array
    {
        try {
            $parserTasks = $this->parserTask->getParserTaskByFields([
                ParserTaskInterface::COLUMN_STATUS => ParserTaskInterface::STATUS_NEW,
                ParserTaskInterface::COLUMN_PARSER_NAME => $parserName
            ]);
        } catch (\Throwable $t) {
            throw new RuntimeException("Can't load document for parser '{$parserName}'", 0, $t);
        }

        if (!count($parserTasks)) {
            return null;
        }

        usort($parserTasks, function ($left, $right) {
            return $left['updated_at'] <=> $right['updated_at'];
        });

        return array_shift($parserTasks);
    }

    public function __sleep()
    {
        return ['entity', 'parserTask', 'parser', 'options'];
    }

    public function __wakeup()
    {
        InsideConstruct::initWakeup(['logger' => LoggerInterface::class]);
    }
}
