<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\DataStore\Storage;

use InvalidArgumentException;
use rollun\datastore\DataStore\DataStoreException;
use Xiag\Rql\Parser\Node\Query\LogicOperator\AndNode;
use Xiag\Rql\Parser\Node\Query\ScalarOperator\EqNode;
use Xiag\Rql\Parser\Query;

abstract class AbstractStorage implements StorageInterface
{
    /**
     * @return int|void
     * @throws DataStoreException
     */
    public function count()
    {
        throw new DataStoreException("Method don't support.");
    }

    /**
     * @param array $itemData
     * @param bool $rewriteIfExist
     * @return array|void
     * @throws DataStoreException
     */
    public function create($itemData, $rewriteIfExist = false)
    {
        throw new DataStoreException("Method don't support.");
    }

    /**
     * @return int|void
     * @throws DataStoreException
     */
    public function deleteAll()
    {
        throw new DataStoreException("Method don't support.");
    }

    /**
     * @param int|string $id
     * @return array|void
     * @throws DataStoreException
     */
    public function delete($id)
    {
        throw new DataStoreException("Method don't support.");
    }

    /**
     * @param array $itemData
     * @param bool $createIfAbsent
     * @return array|void
     * @throws DataStoreException
     */
    public function update($itemData, $createIfAbsent = false)
    {
        throw new DataStoreException("Method don't support.");
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return null;
    }

    /**
     * @return \Traversable|void
     * @throws DataStoreException
     */
    public function getIterator()
    {
        throw new DataStoreException("Method don't support.");
    }

    /**
     * @param int|string $id
     * @return array|void|null
     * @throws DataStoreException
     */
    public function read($id)
    {
        throw new DataStoreException("Method don't support.");
    }

    /**
     * @param int|string $id
     * @return bool|void
     * @throws DataStoreException
     */
    public function has($id)
    {
        throw new DataStoreException("Method don't support.");
    }

    /**
     * @param Query $query
     * @return array
     */
    public function parseQuery(Query $query): array
    {
        $params = [];
        $queryNode = $query->getQuery();

        if ($queryNode instanceof AndNode) {
            $nodes = $queryNode->getQueries();

            foreach ($nodes as $node) {
                if ($node instanceof EqNode) {
                    $this->addParam($params, $node);
                }
            }
        } elseif ($queryNode instanceof EqNode) {
            $this->addParam($params, $queryNode);
        }

        return $params;
    }

    protected function addParam(&$params, EqNode $node)
    {
        $fieldName = $node->getField();
        $value = $node->getValue();
        $params[$fieldName] = $value;
    }

    /**
     * @param $params
     * @param $requiredParams
     */
    protected function validate($params, $requiredParams)
    {
        foreach ($requiredParams as $requiredParam) {
            if (!isset($params[$requiredParam])) {
                throw new InvalidArgumentException("Required param '{$requiredParam}' missing");
            }
        }
    }
}
