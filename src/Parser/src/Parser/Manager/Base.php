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
        $startTime = microtime(true);
        $failed = false;

        try {
            $parserTaskId = $this->executeParsing();
        } catch (\Throwable $e) {
            $failed = true;
            $this->logger->critical('Failed invoke parsing', ['exception' => $e]);
        }

        $endTime = microtime(true);
        $parseTime = $endTime - $startTime;
        $timed = 'Start at ' . date('m.d H:i:s', intval($startTime))
            . '. End at ' . date('m.d H:i:s', intval($endTime));

        if (isset($parserTaskId)) {
            $isFailed = $failed ? ' and failed' : '';
            $message = "Parsing task #{$parserTaskId} take {$parseTime} microseconds{$isFailed}. {$timed}";
        } else {
            $message = "Parsing without task take {$parseTime} microseconds. {$timed}";
        }

        $this->logger->info($message);
    }

    public function executeParsing()
    {
        if (!$parserTask = $this->getNewParserTask($this->parser->getName())) {
            $this->logger->debug("Free documents for parser manager not found");

            return null;
        }

        $this->setOptions($parserTask['options']);
        $this->logger->debug("Parser start task #{$parserTask['id']}");
        $status = null;

        $records = $this->parse($parserTask);

        if (!$records) {
            $this->parserTask->setStatus($parserTask['id'], ParserTaskInterface::STATUS_FAILED);
            $this->logger->error("Parser CAN NOT PARSE document #{$parserTask['id']}");

            return $parserTask['id'];
        }

        $this->save($records, $parserTask);
        $this->afterSave($parserTask);

        return $parserTask['id'];
    }

    protected function save($records, $parserTask)
    {
        try {
            $records = $this->processResult($records, $parserTask);
            $this->saveResult($records);
            $this->parserTask->setStatus($parserTask['id'], ParserTaskInterface::STATUS_SUCCESS);
        } catch (\Throwable $t) {
            $this->parserTask->setStatus($parserTask['id'], ParserTaskInterface::STATUS_FAILED);
            $this->logger->error("Parser failed SAVE document #{$parserTask['id']}", [
                'exception' => $t,
            ]);
        }
    }

    protected function parse($parserTask): array
    {
        try {
            $this->parserTask->setStatus($parserTask['id'], ParserTaskInterface::STATUS_IN_PROCESS);

            if ($this->parser->canParse($parserTask['document'])) {
                return $this->parser->parse($parserTask['document']);
            }
        } catch (\Throwable $e) {
            $this->parserTask->setStatus($parserTask['id'], ParserTaskInterface::STATUS_FAILED);
            $this->logger->error("Parser failed PARSE document #{$parserTask['id']}", [
                'exception' => $e,
            ]);
        }

        return [];
    }

    protected function processResult(array $data, $parserTask): array
    {
        return $data;
    }

    protected function afterSave($parserTask)
    {
    }

    abstract protected function saveResult(array $records);

    protected function getNewParserTask($parserName): ?array
    {
        try {
            $parserTasks = $this->parserTask->getParserTaskByFields([
                ParserTaskInterface::COLUMN_STATUS => ParserTaskInterface::STATUS_NEW,
                ParserTaskInterface::COLUMN_PARSER_NAME => $parserName,
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
