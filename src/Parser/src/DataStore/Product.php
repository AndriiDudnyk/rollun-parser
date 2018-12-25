<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\DataStore;

class Product extends JsonDecorator
{
    public function getJsonFields(): array
    {
        return ['specs', 'shipping', 'imgs'];
    }
}
