<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser;

use rollun\datastore\DataStore\DataStoreException;
use rollun\datastore\DataStore\Interfaces\DataStoreInterface;
use rollun\datastore\DataStore\Memory;
use rollun\datastore\Rql\RqlQuery;
use Xiag\Rql\Parser\Query;

class ProxyManager
{
    /**
     * @var DataStoreInterface
     */
    protected $dataStore;

    /**
     * ProxyManager constructor.
     * @param array $proxies
     * @throws DataStoreException
     */
    public function __construct(array $proxies)
    {
        $this->dataStore = new Memory(['id', 'uri', 'is_used']);

        foreach ($proxies as $proxy) {
            $this->addProxy($proxy);
        }
    }

    /**
     * @param $proxy
     * @throws DataStoreException
     */
    public function addProxy($proxy): void
    {
        $id = md5($proxy);

        if (!$this->dataStore->has($id)) {
            $this->dataStore->create([
                'id' => $id,
                'uri' => $proxy,
                'is_used' => 0
            ]);
        }
    }

    /**
     * @return mixed
     * @throws DataStoreException
     */
    public function getUnusedProxy()
    {
        $proxies = $this->dataStore->query(new RqlQuery('eqf(is_used)&limit(1)'));

        if (!count($proxies)) {
            $this->refresh();
        }

        $proxy = current($proxies);

        return $proxy['uri'];
    }

    /**
     * @param $uri
     * @throws DataStoreException
     */
    public function setUsedProxy($uri)
    {
        $proxies = $this->dataStore->query(new RqlQuery("eq(uri,{$uri})&limit(1)"));
        $proxy = current($proxies);
        $this->dataStore->update([
            'id' => $proxy['id'],
            'is_used' => 1
        ]);
    }

    /**
     * Empty used array
     *
     * @throws DataStoreException
     */
    public function refresh()
    {
        $proxies = $this->dataStore->query(new Query());

        foreach ($proxies as $proxy) {
            $this->dataStore->update([
                'id' => $proxy['id'],
                'is_used' => 0
            ]);
        }
    }
}
