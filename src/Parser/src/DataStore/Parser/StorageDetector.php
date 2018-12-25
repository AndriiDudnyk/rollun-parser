<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\DataStore\Parser;

use InvalidArgumentException;
use Xiag\Rql\Parser\Query;

class StorageDetector extends AbstractStorage
{
    protected $storagePluginManager;

    protected $patterns;

    public function __construct(StoragePluginManager $storagePluginManager, $patterns)
    {
        $this->storagePluginManager = $storagePluginManager;
        $this->patterns = $patterns;
    }

    public function query(Query $query)
    {
        $params = $this->parseQuery($query);
        $this->validate($params, ['uri']);

        $storage = $this->createStorage($params['uri']);

        return $storage->query($query);
    }

    public function createStorage($uri): ParserStorageInterface
    {
        $storageService = $this->getStorageService($uri);

        if (!$this->storagePluginManager->has($storageService)) {
            throw new InvalidArgumentException("Storage '$storageService' not found");
        }

        return $this->storagePluginManager->get($storageService);
    }

    public function getStorageService($uri): string
    {
        $parserStorage = [];

        foreach ($this->patterns as $storage => $pattern) {
            if (preg_match($pattern, $uri)) {
                $parserStorage[] = $storage;
            }
        }

        if (count($parserStorage) > 1) {
            throw new InvalidArgumentException("More then one storage detected, please check your config");
        } elseif (!count($parserStorage)) {
            throw new InvalidArgumentException("No one storage detected, please check your config and uri");
        }

        return array_shift($parserStorage);
    }
}
