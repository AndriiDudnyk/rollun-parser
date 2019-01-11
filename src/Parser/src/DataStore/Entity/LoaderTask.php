<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\DataStore\Entity;

use rollun\parser\DataStore\JsonAspect;
use rollun\parser\Loader\Heartbeat;

class LoaderTask extends JsonAspect implements LoaderTaskInterface
{
    protected function getJsonFields(): array
    {
        return [self::COLUMN_OPTIONS];
    }

    public function create($itemData, $rewriteIfExist = false)
    {
        $itemData[self::COLUMN_CREATED_AT] = microtime(true);
        $itemData[self::COLUMN_UPDATED_AT] = microtime(true);

        if (!isset($itemData[self::COLUMN_HEARTBEAT_EXPIRATION])) {
            $itemData[self::COLUMN_HEARTBEAT_EXPIRATION] = time() + Heartbeat::HEARTBEAT_TIMEOUT;
        }

        if (!isset($itemData[self::COLUMN_HEARTBEAT_ATTEMPT])) {
            $itemData[self::COLUMN_HEARTBEAT_ATTEMPT] = time() + Heartbeat::HEARTBEAT_TIMEOUT;
        }

        return parent::create($itemData, $rewriteIfExist);
    }

    public function update($itemData, $createIfAbsent = false)
    {
        unset($itemData[self::COLUMN_CREATED_AT]);
        $itemData[self::COLUMN_UPDATED_AT] = microtime(true);

        return parent::update($itemData, $createIfAbsent);
    }

    public function addLoaderTask($parser, $uri, $options = [])
    {
        $record = $this->create([
            self::COLUMN_PARSER_NAME => $parser,
            self::COLUMN_URI => $uri,
            self::COLUMN_STATUS => 0,
            self::COLUMN_OPTIONS => $options
        ]);

        return $record[$this->dataStore->getIdentifier()];
    }


    /**
     * @param $id
     * @param $status
     * @return void
     */
    public function setStatus($id, $status)
    {
        $this->update([
            $this->dataStore->getIdentifier() => $id,
            self::COLUMN_STATUS => $status,
        ]);
    }
}
