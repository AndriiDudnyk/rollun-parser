<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Manager\Parser;

use InvalidArgumentException;
use Parser\DataStore\Document;
use Parser\Parser\Compatible as CompatibleParser;
use Psr\Log\LoggerInterface;
use rollun\datastore\DataStore\Interfaces\DataStoresInterface;

class Compatible extends BaseManager
{
    public function __construct(
        CompatibleParser $parser,
        DataStoresInterface $parseResultDataStore,
        Document $documentDataStore,
        array $options,
        LoggerInterface $logger = null
    ) {
        parent::__construct($parser, $parseResultDataStore, $documentDataStore, $options);
    }

    protected function saveResult(array $uris)
    {
        if (!$productId = $this->options['productId'] ?? null) {
            throw new InvalidArgumentException("Invalid option 'productId'");
        }

        foreach ($uris as $record) {
            $this->parseResultDataStore->create(array_merge(
                [
                    'product_id' => $productId,
                ],
                $record
            ));
        }
    }
}
