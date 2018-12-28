<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\FreeProxyList;

use rollun\datastore\DataStore\CsvBase;
use rollun\datastore\DataStore\Factory\DataStoreAbstractFactory;
use rollun\parser\DataStore\Entity\LoaderTaskInterface;
use rollun\parser\Loader\Loader\LoaderAbstractFactory;
use rollun\parser\Loader\Loader\LoaderInterface;
use rollun\service\Parser\FreeProxyList\DataStore\Entity\Proxy as ProxyEntityStore;
use rollun\service\Parser\FreeProxyList\DataStore\Entity\ProxyFactory as ProxyEntityStoreFactory;
use rollun\service\Parser\FreeProxyList\DataStore\Entity\ProxyInterface as ProxyEntityStoreInterface;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'dependencies' => [
                'invokables' => [
                ],
                'factories' => [
                    ProxyEntityStore::class => ProxyEntityStoreFactory::class,
                ],
                'abstract_factories' => [
                    LoaderAbstractFactory::class,
                ],
                'aliases' => [
                    ProxyEntityStoreInterface::class => ProxyEntityStore::class,
                ]
            ],
            ProxyEntityStoreFactory::class => [
                ProxyEntityStoreFactory::KEY_LOADER_TASK_DATASTORE => LoaderTaskInterface::class,
                ProxyEntityStoreFactory::KEY_ASPECT_DATASTORE => __NAMESPACE__ . 'proxyDataStore',
                ProxyEntityStoreFactory::KEY_PROXY_LIST_URI => 'https://free-proxy-list.net/',
                ProxyEntityStoreFactory::KEY_TASK_OPTIONS => [
                    LoaderInterface::USE_PROXY_OPTION => 0,
                ],
            ],
            DataStoreAbstractFactory::KEY_DATASTORE => [
                __NAMESPACE__ . 'proxyDataStore' => [
                    'class' => CsvBase::class,
                    'filename' => 'data/datastores/proxies.csv',
                    'delimiter' => ',',
                ],
            ],
        ];
    }
}
