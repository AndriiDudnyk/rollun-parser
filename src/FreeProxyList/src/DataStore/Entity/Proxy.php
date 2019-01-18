<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\FreeProxyList\DataStore\Entity;

use Psr\Log\LoggerInterface;
use rollun\datastore\DataStore\Aspect\AspectAbstract;
use rollun\datastore\DataStore\Interfaces\DataStoresInterface;
use rollun\datastore\Rql\RqlQuery;
use rollun\dic\InsideConstruct;
use rollun\parser\DataStore\Entity\LoaderTaskInterface;
use rollun\service\Parser\FreeProxyList\Parser\Parser\HomePage;
use Xiag\Rql\Parser\Node\AbstractQueryNode;
use Xiag\Rql\Parser\Node\LimitNode;
use Xiag\Rql\Parser\Node\Query\LogicOperator\AndNode;
use Xiag\Rql\Parser\Node\Query\ScalarOperator\EqNode;
use Xiag\Rql\Parser\Node\SortNode;

class Proxy extends AspectAbstract implements ProxyInterface
{
    public function create($itemData, $rewriteIfExist = false)
    {
        if (isset($itemData[$this->dataStore->getIdentifier()])) {
            return parent::create($itemData, $rewriteIfExist);
        }

        if ($record = $this->getRecordByFields(['uri' => $itemData['uri']])) {
            return $record;
        }

        if (!isset($itemData[self::COLUMN_LEVEL])) {
            $itemData[self::COLUMN_LEVEL] = 0;
        } elseif ($itemData[self::COLUMN_LEVEL] < self::MIN_LEVEL || $itemData[self::COLUMN_LEVEL] > self::MAX_LEVEL) {
            throw new \InvalidArgumentException(
                "Invalid column " . self::COLUMN_LEVEL . " = {$itemData[self::COLUMN_LEVEL]}"
            );
        }

        $itemData['created_at'] = time();
        $itemData[self::COLUMN_UPDATED_AT] = microtime(true);

        return parent::create($itemData, $rewriteIfExist);
    }

    public function getRecordByFields($itemData)
    {
        $eqNodes = array_map(function ($field, $value) {
            return new EqNode($field, $value);
        }, array_keys($itemData), array_values($itemData));
        $query = new RqlQuery();
        $query->setQuery(new AndNode($eqNodes));

        $proxies = $this->dataStore->query($query);

        if (count($proxies)) {
            return array_shift($proxies);
        }

        return null;
    }

    public function addNewUri($uri)
    {
        $record = $this->create([
            'uri' => $uri,
        ]);

        return $record[$this->dataStore->getIdentifier()];
    }

    public function update($itemData, $createIfAbsent = false)
    {
        unset($itemData[self::COLUMN_CREATED_AT]);
        $itemData[self::COLUMN_UPDATED_AT] = microtime(true);

        return parent::update($itemData, $createIfAbsent);
    }
}
