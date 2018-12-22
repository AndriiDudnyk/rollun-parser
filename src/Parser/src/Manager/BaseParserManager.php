<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Manager;

use Parser\DataStore\DocumentDataStoreInterface;
use Parser\Parser\ParserInterface;
use Psr\Log\LoggerInterface;
use rollun\datastore\DataStore\Interfaces\DataStoresInterface;
use rollun\datastore\Rql\RqlQuery;
use rollun\dic\InsideConstruct;
use RuntimeException;

class BaseParserManager
{
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
        DocumentDataStoreInterface $documentDataStore,
        array $options,
        LoggerInterface $logger = null
    ) {
        InsideConstruct::setConstructParams(["logger" => LoggerInterface::class]);
        $this->parser = $parser;
        $this->parseResultDataStore = $parseResultDataStore;
        $this->documentDataStore = $documentDataStore;
        $this->options = $options;
    }

    public function __invoke()
    {
        if (!$document = $this->getDocument($this->parser->getName())) {
            $this->logger->info("Free documents for parser manager not found");
            return;
        }

        try {
            $records = $this->parser->parse($document['html']);
            $this->documentDataStore->update([
                'id' => $document['id'],
                'status' => 1,
            ]);
            $this->logger->info("Parser successfully finish parsing document #{$document['id']}");
            $records = $this->processResult($records);

            if ($records) {
                $this->saveResult($records);
            }
        } catch (\Throwable $t) {
            $this->logger->info("Parser failed parsing document #{$document['id']}", [
                'exception' => $t,
            ]);
        }
    }

    protected function processResult(array $records): ?array
    {
        return $records;
    }

    protected function saveResult(array $records)
    {
        foreach ($records as $record) {
            $this->parseResultDataStore->create($record);
        }
    }

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
