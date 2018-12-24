<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\DataStore;

class TaskDataStore extends JsonDecorator
{
    protected function getJsonFields(): array
    {
        return ['options'];
    }
}
