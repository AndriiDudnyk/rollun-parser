<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\DataStore\Entity;

use InvalidArgumentException;
use rollun\datastore\DataStore\Interfaces\DataStoresInterface;
use rollun\datastore\Rql\RqlQuery;
use rollun\parser\DataStore\JsonAspect;
use Xiag\Rql\Parser\Node\Query\LogicOperator\AndNode;
use Xiag\Rql\Parser\Node\Query\ScalarOperator\EqNode;
use Xiag\Rql\Parser\Query;

class ParserTask extends JsonAspect implements ParserTaskInterface
{
    /**
     * HtmlDataStore constructor.
     * @param DataStoresInterface $dataStore
     * @param string $storeDir
     */
    public function __construct(DataStoresInterface $dataStore, string $storeDir)
    {
        if (!file_exists($storeDir)) {
            throw new InvalidArgumentException("Directory '{$storeDir}' not found");
        }

        parent::__construct($dataStore);
        $this->storeDir = rtrim($storeDir, DIRECTORY_SEPARATOR);
    }

    protected function getJsonFields(): array
    {
        return ['options'];
    }

    protected $storeDir;

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

    public function addParserTask($parser, $document, $options = [])
    {
        $record = $this->create([
            'parser' => $parser,
            'document' => $document,
            'status' => self::STATUS_NEW,
            'options' => $options
        ]);

        return $record[$this->dataStore->getIdentifier()];
    }

    /**
     * @inheritdoc
     */
    public function preCreate($itemData, $rewriteIfExist = false)
    {
        $itemData = $this->preProcessJsonFields($itemData);

        if (!isset($itemData['document'])) {
            throw new InvalidArgumentException('Invalid incoming data');
        }

        return $this->htmlToFile($itemData);
    }

    /**
     * @inheritdoc
     */
    public function preUpdate($itemData, $createIfAbsent = false)
    {
        $itemData = $this->preProcessJsonFields($itemData);

        if (!isset($itemData['document'])) {
            return $itemData;
        }

        if (!isset($itemData['id'])) {
            throw new InvalidArgumentException('Invalid incoming data');
        }

        $record = $this->dataStore->read($itemData['id']);

        if (!isset($record)) {
            throw new InvalidArgumentException('Invalid incoming data');
        }

        unlink($record['file']);
        return $this->htmlToFile($itemData);
    }

    /**
     * @inheritdoc
     */
    public function postRead($result, $id)
    {
        $result = $this->postProcessJsonFields($result);

        $result['document'] = file_get_contents($result['file']);
        unset($result['file']);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function postQuery($result, Query $query)
    {
        foreach ($result as $k => $record) {
            $result[$k] = $this->postProcessJsonFields($record);
        }

        foreach ($result as $key => $record) {
            $result[$key] = $this->fileToHtml($record);
        }

        return $result;
    }

    protected function htmlToFile($result)
    {
        if ($result['document']) {
            $fileName = md5($result['document']);
            $filePath = $this->storeDir . DIRECTORY_SEPARATOR . $fileName . '.html';
            file_put_contents($filePath, $result['document']);
        } else {
            $filePath = '';
        }

        unset($result['document']);
        $result['file'] = $filePath;

        return $result;
    }

    protected function fileToHtml($result)
    {
        if ($result['file']) {
            $result['document'] = file_get_contents($result['file']);
        } else {
            $result['document'] = '';
        }

        unset($result['file']);

        return $result;
    }

    /**
     * @param $id
     * @param $status
     * @return void
     */
    public function setStatus($id, $status)
    {
        $this->update([
            'id' => $id,
            'status' => $status,
        ]);
    }

    /**
     * @param $fields
     * @return array|iterable|[]
     */
    public function getParserTaskByFields($fields)
    {
        $eqNodes = [];

        foreach ($fields as $field => $value) {
            if (is_scalar($value)) {
                $eqNodes[] = new EqNode($field, $value);
            }
        }

        $query = new RqlQuery();
        $query->setQuery(new AndNode($eqNodes));

        return $this->query($query);
    }
}
