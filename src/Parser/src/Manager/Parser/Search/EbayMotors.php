<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Manager\Parser\Search;

use Parser\DataStore\Document;
use Parser\Manager\Parser\BaseManager;
use Parser\Parser\ParserInterface;
use Psr\Log\LoggerInterface;
use rollun\datastore\DataStore\Interfaces\DataStoresInterface;

class EbayMotors extends BaseManager
{
    public function __construct(
        ParserInterface $parser,
        DataStoresInterface $parseResultDataStore,
        Document $documentDataStore,
        array $options,
        LoggerInterface $logger = null
    ) {
        parent::__construct($parser, $parseResultDataStore, $documentDataStore, $options);
    }

    protected function saveResult(array $uris)
    {
        // TODO: Implement saveResult() method.
    }
}
