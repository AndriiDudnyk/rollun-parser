<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\DataStore;

use rollun\datastore\DataStore\Aspect\AspectAbstract;
use rollun\utils\Json\Coder;
use Xiag\Rql\Parser\Query;

abstract class JsonAspect extends AspectAbstract
{
    abstract protected function getJsonFields(): array;

    protected function preUpdate($itemData, $createIfAbsent = false)
    {
        return $this->preProcessJsonFields($itemData);
    }

    protected function preCreate($itemData, $rewriteIfExist = false)
    {
        return $this->preProcessJsonFields($itemData);
    }

    protected function preProcessJsonFields($itemData)
    {
        foreach ($this->getJsonFields() as $jsonField) {
            if (isset($itemData[$jsonField])) {
                $itemData[$jsonField] = Coder::jsonEncode($itemData[$jsonField]);
            }
        }

        return $itemData;
    }

    protected function postProcessJsonFields($result)
    {
        foreach ($this->getJsonFields() as $jsonField) {
            if (isset($result[$jsonField]) && $result[$jsonField]) {
                $result[$jsonField] = Coder::jsonDecode($result[$jsonField]);
            }
        }

        return $result;
    }

    protected function postRead($result, $id)
    {
        $result = $this->postProcessJsonFields($result);

        return $result;
    }

    protected function postQuery($result, Query $query)
    {
        foreach ($result as $k => $record) {
            $result[$k] = $this->postProcessJsonFields($record);
        }

        return $result;
    }
}
