<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Manager;

use Parser\DataStore\DocumentDataStore;
use Parser\Parser\ParserInterface;
use Psr\Log\LoggerInterface;
use rollun\datastore\DataStore\Interfaces\DataStoresInterface;
use rollun\datastore\Rql\RqlQuery;
use rollun\dic\InsideConstruct;
use RuntimeException;

abstract class BaseParserManager
{
    const KEY_OPTIONS = 'parserOptions';

    protected $parser;

    protected $parseResultDataStore;

    protected $documentDataStore;

    protected $options;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        ParserInterface $parser,
        DataStoresInterface $parseResultDataStore,
        DocumentDataStore $documentDataStore,
        array $options,
        LoggerInterface $logger = null
    ) {
        InsideConstruct::setConstructParams(["logger" => LoggerInterface::class]);
        $this->parser = $parser;
        $this->parseResultDataStore = $parseResultDataStore;
        $this->documentDataStore = $documentDataStore;
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
        if (!$document = $this->getDocument($this->parser->getName())) {
            $this->logger->info("Free documents for parser manager not found");
            return;
        }

        $this->setOptions($document['options']);

        try {
            $records = $this->parser->parse($document['document']);
            $this->documentDataStore->update([
                'id' => $document['id'],
                'status' => 1,
            ]);

            if (!$records) {
                $this->logger->warning("Parser did not parse any data from document #{$document['id']}");
                return;
            }

            $records = $this->processResult($records);
            $this->saveResult($records);
        } catch (\Throwable $t) {
            $this->logger->warning("Parser failed parsing document #{$document['id']}", [
                'exception' => $t,
            ]);
        }
    }

    protected function processResult(array $records): ?array
    {
        return $records;
    }

    abstract protected function saveResult(array $records);

    protected function getDocument($parser): ?array
    {
        try {
            $tasks = $this->documentDataStore->query(new RqlQuery("and(eq(status,0),eq(parser,{$parser}))"));
        } catch (\Throwable $t) {
            throw new RuntimeException("Can't load document for parser '{$parser}'", 0, $t);
        }

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
        return ['parseResultDataStore', 'documentDataStore', 'parser'];
    }

    public function __wakeup()
    {
        InsideConstruct::initWakeup(['logger' => LoggerInterface::class]);
    }
}
