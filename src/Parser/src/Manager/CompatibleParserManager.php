<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Manager;

use InvalidArgumentException;

class CompatibleParserManager extends BaseParserManager
{
    protected function saveResult(array $records)
    {
        if (!$productId = $this->options['productId'] ?? null) {
            throw new InvalidArgumentException("Invalid option 'productId'");
        }

        foreach ($records as $record) {
            $this->parseResultDataStore->create(array_merge(
                [
                    'product_id' => $productId,
                    'created_at' => time(),
                ],
                $record
            ));
        }
    }
}