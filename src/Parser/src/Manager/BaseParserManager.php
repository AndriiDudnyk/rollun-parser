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

class BaseParserManager
{
    protected $parser;

    protected $parseResultDataStore;

    protected $documentDataStore;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        ParserInterface $parser,
        DataStoresInterface $parseResultDataStore,
        DocumentDataStoreInterface $documentDataStore,
        LoggerInterface $logger = null
    ) {
        InsideConstruct::setConstructParams(["logger" => LoggerInterface::class]);
        $this->parser = $parser;
        $this->parseResultDataStore = $parseResultDataStore;
        $this->documentDataStore = $documentDataStore;
    }

    public function __invoke()
    {
        $this->parse();
    }

    public function parse()
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
            $this->processResult($records);
        } catch (\Throwable $t) {
            $this->logger->info("Parser failed parsing document #{$document['id']}", [
                'exception' => $t,
            ]);
        }
    }

    protected function processResult($records)
    {
        foreach ($records as $record) {
            $this->parseResultDataStore->create($record);
        }
    }

    protected function getDocument($parser): ?array
    {
        $tasks = $this->documentDataStore->query(new RqlQuery("and(eq(status,0),eq(parser,{$parser}))"));

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
