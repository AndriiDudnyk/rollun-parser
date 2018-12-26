<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\DataStore;

class Task extends JsonDecorator
{
    protected function getJsonFields(): array
    {
        return ['options'];
    }

    public function create($itemData, $rewriteIfExist = false)
    {
        $itemData['created_at'] = time();

        return parent::create($itemData, $rewriteIfExist);
    }

    public function update($itemData, $createIfAbsent = false)
    {
        unset($itemData['created_at']);

        return parent::update($itemData, $createIfAbsent);
    }

    public function addTask($parser, $uri, $options = [])
    {
        $record = $this->create([
            'parser' => $parser,
            'uri' => $uri,
            'status' => 0,
            'options' => $options
        ]);

        return $record[$this->dataStore->getIdentifier()];
    }
}
