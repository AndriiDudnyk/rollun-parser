<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Manager\Parser;

use InvalidArgumentException;

class Compatible extends BaseManager
{
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
