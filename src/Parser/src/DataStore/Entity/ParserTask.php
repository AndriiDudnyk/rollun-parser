<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser\DataStore\Entity;

use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use rollun\datastore\DataStore\Interfaces\DataStoresInterface;
use rollun\datastore\Rql\RqlQuery;
use rollun\dic\InsideConstruct;
use rollun\parser\DataStore\JsonAspect;
use Xiag\Rql\Parser\Node\Query\LogicOperator\AndNode;
use Xiag\Rql\Parser\Node\Query\ScalarOperator\EqNode;
use Xiag\Rql\Parser\Query;

class ParserTask extends JsonAspect implements ParserTaskInterface
{
    protected $storeDir;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * ParserTask constructor.
     * @param DataStoresInterface $dataStore
     * @param string $storeDir
     * @param LoggerInterface|null $logger
     * @throws \ReflectionException
     */
    public function __construct(DataStoresInterface $dataStore, string $storeDir, LoggerInterface $logger = null)
    {
        InsideConstruct::setConstructParams(['logger' => LoggerInterface::class]);

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

    public function create($itemData, $rewriteIfExist = false)
    {
        $itemData[self::COLUMN_CREATED_AT] = microtime(true);
        $itemData[self::COLUMN_UPDATED_AT] = microtime(true);

        return parent::create($itemData, $rewriteIfExist);
    }

    public function update($itemData, $createIfAbsent = false)
    {
        unset($itemData[self::COLUMN_CREATED_AT]);
        $itemData[self::COLUMN_UPDATED_AT] = microtime(true);

        return parent::update($itemData, $createIfAbsent);
    }

    public function addParserTask($parser, $document, $options = [])
    {
        $record = $this->create([
            self::COLUMN_PARSER_NAME => $parser,
            self::COLUMN_ABSTRACT_DOCUMENT => $document,
            self::COLUMN_STATUS => self::STATUS_NEW,
            self::COLUMN_OPTIONS => $options
        ]);

        return $record[$this->dataStore->getIdentifier()];
    }

    /**
     * @inheritdoc
     */
    public function preCreate($itemData, $rewriteIfExist = false)
    {
        $itemData = $this->preProcessJsonFields($itemData);

        if (!isset($itemData[self::COLUMN_ABSTRACT_DOCUMENT])) {
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

        if (!isset($itemData[self::COLUMN_ABSTRACT_DOCUMENT])) {
            return $itemData;
        }

        if (!isset($itemData['id'])) {
            throw new InvalidArgumentException('Invalid incoming data');
        }

        $record = $this->dataStore->read($itemData['id']);

        if (!isset($record)) {
            throw new InvalidArgumentException('Invalid incoming data');
        }

        unlink($record[self::COLUMN_FILE]);
        return $this->htmlToFile($itemData);
    }

    /**
     * @inheritdoc
     */
    public function postRead($result, $id)
    {
        $result = $this->postProcessJsonFields($result);

        $result[self::COLUMN_ABSTRACT_DOCUMENT] = file_get_contents($result[self::COLUMN_FILE]);
        unset($result[self::COLUMN_FILE]);

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

    public function findByFile($file)
    {
        $eqNode = new EqNode(self::COLUMN_FILE, $file);
        $query = new RqlQuery();
        $query->setQuery($eqNode);

        return $this->query($query);
    }

    protected function htmlToFile($result)
    {
        if ($result[self::COLUMN_ABSTRACT_DOCUMENT]) {
            $fileName = uniqid(md5($result[self::COLUMN_ABSTRACT_DOCUMENT]));
            $filePath = $this->storeDir . DIRECTORY_SEPARATOR . $fileName . '.html';
            file_put_contents($filePath, $result[self::COLUMN_ABSTRACT_DOCUMENT]);
        } else {
            $filePath = '';
        }

        unset($result[self::COLUMN_ABSTRACT_DOCUMENT]);
        $result[self::COLUMN_FILE] = $filePath;

        return $result;
    }

    protected function fileToHtml($result)
    {
        if ($result[self::COLUMN_FILE]) {
            $result[self::COLUMN_ABSTRACT_DOCUMENT] = file_get_contents($result[self::COLUMN_FILE]);
        } else {
            $result[self::COLUMN_ABSTRACT_DOCUMENT] = '';
        }

        unset($result[self::COLUMN_FILE]);

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

    public function __sleep()
    {
        return ['datastore', 'storeDir'];
    }

    public function __wakeup()
    {
        InsideConstruct::initWakeup(['logger' => LoggerInterface::class]);
    }
}
