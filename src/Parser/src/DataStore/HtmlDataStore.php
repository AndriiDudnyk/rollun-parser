<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\DataStore;

use InvalidArgumentException;
use rollun\datastore\DataStore\Aspect\AspectAbstract;
use rollun\datastore\DataStore\Interfaces\DataStoresInterface;

class HtmlDataStore extends AspectAbstract implements HtmlDataStoreInterface
{
    protected $dirPath;

    /**
     * HtmlDataStore constructor.
     * @param DataStoresInterface $dataStore
     * @param string $dirPath
     */
    public function __construct(DataStoresInterface $dataStore, string $dirPath)
    {
        if (!file_exists($dirPath)) {
            throw new InvalidArgumentException("Directory '{$dirPath}' not found");
        }

        parent::__construct($dataStore);
        $this->dirPath = rtrim($dirPath, DIRECTORY_SEPARATOR);
    }

    /**
     * @inheritdoc
     */
    public function preCreate($itemData, $rewriteIfExist = false)
    {
        if (!isset($itemData['html'])) {
            throw new InvalidArgumentException('Invalid incoming data');
        }

        return $this->htmlToFile($itemData);
    }

    /**
     * @inheritdoc
     */
    public function preUpdate($itemData, $createIfAbsent = false)
    {
        if (!isset($itemData['html'])) {
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
        $result['html'] = file_get_contents($result['file']);
        unset($result['file']);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function postQuery($result, $id)
    {
        foreach ($result as $key => $record) {
            $result[$key] = $this->fileToHtml($record);
        }

        return $result;
    }

    protected function htmlToFile($result)
    {
        if ($result['html']) {
            $fileName = md5($result['html']);
            $filePath = $this->dirPath . DIRECTORY_SEPARATOR . $fileName . '.html';
            file_put_contents($filePath, $result['html']);
        } else {
            $filePath = '';
        }

        unset($result['html']);
        $result['file'] = $filePath;

        return $result;
    }

    protected function fileToHtml($result)
    {
        if ($result['file']) {
            $result['html'] = file_get_contents($result['file']);
        } else {
            $result['html'] = '';
        }

        unset($result['file']);

        return $result;
    }
}
