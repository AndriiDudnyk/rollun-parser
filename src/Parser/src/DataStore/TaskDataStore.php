<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\DataStore;

use rollun\datastore\DataStore\Aspect\AspectAbstract;
use rollun\utils\Json\Coder;
use Xiag\Rql\Parser\Query;

class TaskDataStore extends AspectAbstract
{
    protected function preUpdate($itemData, $createIfAbsent = false)
    {
        if (array_key_exists('options', $itemData)) {
            $itemData = $this->preProcessOptionColumn($itemData);
        } elseif ($createIfAbsent) {
            throw new \InvalidArgumentException('options' . ' column in data store is required');
        }
        return $itemData;
    }

    protected function preCreate($itemData, $rewriteIfExist = false)
    {
        if (!array_key_exists('options', $itemData)) {
            throw new \InvalidArgumentException('options' . ' column in data store is required');
        }

        $itemData = $this->preProcessOptionColumn($itemData);

        return $itemData;
    }

    protected function preProcessOptionColumn($itemData)
    {
        if (!is_array($itemData['options'])) {
            throw new \InvalidArgumentException(
                'options' . ' column in data store must be an array'
            );
        }

        $itemData['options'] = Coder::jsonEncode($itemData['options']);

        return $itemData;
    }

    protected function postProcessOptionColumn($result)
    {
        foreach ($result as $key => $item) {
            $result[$key]['options'] = Coder::jsonDecode($result[$key]['options']);
        }

        return $result;
    }

    protected function postRead($result, $id)
    {
        $result = $this->postProcessOptionColumn($result);

        return $result;
    }

    protected function postQuery($result, Query $query)
    {
        $result = $this->postProcessOptionColumn($result);

        return $result;
    }
}
