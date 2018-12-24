<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\DataStore;

use InvalidArgumentException;
use rollun\datastore\DataStore\Interfaces\DataStoresInterface;
use Xiag\Rql\Parser\Query;

class DocumentDataStore extends JsonDecorator
{
    protected function getJsonFields(): array
    {
        return ['options'];
    }

    protected $storeDir;

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
}
