<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Manager\Parser;

use Parser\DataStore\Document;
use Parser\DataStore\Proxy as ProxyDataStore;
use Parser\Parser\ParserInterface;
use Psr\Log\LoggerInterface;

class Proxy extends BaseManager
{
    /**
     * @var ProxyDataStore
     */
    protected $parseResultDataStore;

    public function __construct(
        ParserInterface $parser,
        ProxyDataStore $proxyDataStore,
        Document $documentDataStore,
        array $options,
        LoggerInterface $logger = null
    ) {
        parent::__construct($parser, $proxyDataStore, $documentDataStore, $options);
    }

    protected function saveResult(array $uris)
    {
        foreach ($uris as $uri) {
            $this->parseResultDataStore->addNewUri($uri);
        }
    }
}
