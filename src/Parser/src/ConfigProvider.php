<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

declare(strict_types = 1);

namespace Parser;

use Parser\DataStore\Factory\ProxyFactory as ProxyDataStoreFactory;
use Parser\DataStore\Factory\SearchTaskFactory as SearchTaskDataStoreFactory;
use Parser\DataStore\Factory\DocumentFactory as DocumentDataStoreFactory;


// DataStores
use Parser\DataStore\Document as DocumentDataStore;
use Parser\DataStore\Product as ProductDataStore;
use Parser\DataStore\SearchTask as SearchTaskDataStore;
use Parser\DataStore\Task as TaskDataStore;
use Parser\DataStore\Proxy as ProxyDataStore;

// Storage (extends DataStore)
use Parser\DataStore\Storage\Factory\AbstractStorageFactory;
use Parser\DataStore\Storage\Factory\BaseStorageFactory;
use Parser\DataStore\Storage\Factory\SearchFactory as SearchStorageFactory;
use Parser\DataStore\Storage\Factory\StoragePluginManagerFactory;
use Parser\DataStore\Storage\Product as ProductStorage;
use Parser\DataStore\Storage\Search as SearchStorage;
use Parser\DataStore\Storage\Compatible as CompatibleStorage;
use Parser\DataStore\Storage\StorageDetector;
use Parser\DataStore\Storage\StorageDetectorFactory;
use Parser\DataStore\Storage\StoragePluginManager;

// Loader
use Parser\Loader\SearchLoaderHelper;
use Parser\Loader\SearchLoaderHelperFactory;
use Parser\Loader\Loader;
use Parser\Loader\LoaderAbstractFactory;

// Parsers
use Parser\Parser\Compatible as CompatibleParser;
use Parser\Parser\Product as ProductParser;
use Parser\Parser\Search\Simple as SimpleSearchParser;
use Parser\Parser\Search\EbayMotors as EbayMotorsSearchParser;
use Parser\Parser\Proxy as ProxyParser;

use Parser\Manager\LoaderManagerFactory;
use Parser\Manager\LoaderManager;
use Parser\Manager\Parser\Proxy;
use Parser\Manager\Parser\Factory\AbstractParserManagerFactory;
use Parser\Manager\Parser\Factory\BaseParserManagerFactory;
use Parser\Manager\Parser\Factory\SearchParserManagerFactory;
use Parser\Manager\Parser\Product as ProductParserManager;
use Parser\Manager\Parser\Search\Simple as SimpleSearchParserManager;
use Parser\Manager\Parser\BaseManager as BaseParserManager;
use Parser\Manager\Parser\Compatible as CompatibleParserManager;

use rollun\callback\Callback\Factory\CallbackAbstractFactoryAbstract;
use rollun\callback\Callback\Factory\MultiplexerAbstractFactory;
use rollun\callback\Callback\Interrupter\Factory\InterruptAbstractFactoryAbstract;
use rollun\callback\Callback\Interrupter\Factory\ProcessAbstractFactory;
use rollun\callback\Callback\Interrupter\Process;
use rollun\callback\Callback\Multiplexer;

// Default datastores
use rollun\datastore\DataStore\CsvBase;
use rollun\datastore\DataStore\DbTable;
use rollun\datastore\DataStore\Factory\DataStoreAbstractFactory;
use rollun\datastore\TableGateway\Factory\TableGatewayAbstractFactory;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.zendframework.com/zend-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     *
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                'invokables' => [
                    UserAgentGenerator::class => UserAgentGenerator::class,
                    ProductParser::class => ProductParser::class,
                    SimpleSearchParser::class => SimpleSearchParser::class,
                    CompatibleParser::class => CompatibleParser::class,
                    ProxyParser::class => ProxyParser::class,
                ],
                'factories' => [
                    LoaderManager::class => LoaderManagerFactory::class,

                    // Manager factories
                    BaseParserManager::class => BaseParserManagerFactory::class,
                    ProductParserManager::class => BaseParserManagerFactory::class,
                    SimpleSearchParserManager::class => SearchParserManagerFactory::class,
                    CompatibleParserManager::class => BaseParserManagerFactory::class,
                    Proxy::class => BaseParserManagerFactory::class,

                    // DataStores
                    DocumentDataStore::class => DocumentDataStoreFactory::class,
                    SearchTaskDataStore::class => SearchTaskDataStoreFactory::class,
                    ProxyDataStore::class => ProxyDataStoreFactory::class,

                    // Storage factories
                    ProductStorage::class => BaseStorageFactory::class,
                    CompatibleStorage::class => BaseStorageFactory::class,
                    SearchStorage::class => SearchStorageFactory::class,
                    StorageDetector::class => StorageDetectorFactory::class,
                    StoragePluginManager::class => StoragePluginManagerFactory::class,

                    SearchLoaderHelper::class => SearchLoaderHelperFactory::class,
                ],
                'abstract_factories' => [
                    LoaderAbstractFactory::class,
                ],
                'aliases' => [
                    'productStorage' => ProductStorage::class,
                    'searchStorage' => SearchStorage::class,
                    'compatibleStorage' => CompatibleStorage::class,
                    'parserStorage' => StorageDetector::class,
                    'searchTaskDataStore' => SearchTaskDataStore::class,
                    'aspectProxyDataStore' => ProxyDataStore::class,
                ],
            ],
            StorageDetectorFactory::class => [
                'productStorage' => "/https\:\/\/www\.ebay\.com\/itm\/[0-9]+/",
                'searchStorage' => [
                    "/https\:\/\/www\.ebay\.com\/sch\//",
                    "/https\:\/\/www\.ebay\.com\/str\//"
                ],
                'compatibleStorage' => "/https\:\/\/frame\.ebay\.com\/ebaymotors\/ws\/eBayISAPI\.dll\?GetFitmentData/",
            ],
            SearchLoaderHelperFactory::class => [
                SearchLoaderHelperFactory::KEY_REDIRECT_URI => 'https://www.ebay.com/sch/FindingCustomization/'
                    . '?_fcdm=1&_fcss=12&_fcps=3&_fcippl=2&_fcso=1&_fcpd=1&_fcsbm=1&_pppn=v3'
                    . '&_fcpe=7%7C5%7C3%7C2%7C4&_fcie=1%7C36&_fcse=10%7C42%7C43&_fcsp=',
                SearchLoaderHelperFactory::KEY_CLIENT => 'parseLoader',
            ],
            AbstractStorageFactory::KEY => [
                ProductStorage::class => [
                    'loader' => 'parseLoader',
                    'parser' => ProductParser::class,
                ],
                SearchStorage::class => [
                    'loader' => 'parseLoader',
                    'parser' => SimpleSearchParser::class,
                    'searchLoaderHelper' => SearchLoaderHelper::class,
                ],
                CompatibleStorage::class => [
                    'loader' => 'parseLoader',
                    'parser' => CompatibleParser::class,
                ],
            ],
            LoaderAbstractFactory::class => [
                'parseLoader' => [
                    LoaderAbstractFactory::KEY_PROXY_DATASTORE => 'aspectProxyDataStore',
                    LoaderAbstractFactory::KEY_OPTIONS => [
                        Loader::CREATE_TASK_IF_NO_PROXIES_OPTION => 1,
                        Loader::COOKIE_DOMAIN_OPTION => '.ebay.com',
                        Loader::USE_PROXY_OPTION => 1,
                        Loader::FAKE_USER_OS_OPTION => 1,
                        Loader::MAX_ATTEMPTS_OPTION => 10,
                        Loader::CONNECTION_TIMEOUT_OPTION => 10,
                        Loader::ALLOW_REDIRECT_OPTION => 1,
                    ],
                ],
            ],
            DocumentDataStoreFactory::class => [
                DocumentDataStoreFactory::KEY_DOWNLOAD_DATASTORE => 'downloadDataStore',
                DocumentDataStoreFactory::KEY_STORE_DIR => 'data/documents',
            ],
            SearchTaskDataStoreFactory::class => [
                SearchTaskDataStoreFactory::KEY_TASK_DATASTORE => 'aspectTaskDataStore',
                SearchTaskDataStoreFactory::KEY_SEARCH_LOADER_HELPER => SearchLoaderHelper::class,
            ],
            ProxyDataStoreFactory::class => [
                ProxyDataStoreFactory::KEY_TASK_DATASTORE => 'aspectTaskDataStore',
                ProxyDataStoreFactory::KEY_ASPECT_DATASTORE => 'proxyDataStore',
                ProxyDataStoreFactory::KEY_PROXY_LIST_URI => 'https://free-proxy-list.net/',
                ProxyDataStoreFactory::KEY_TASK_OPTIONS => [
                    Loader::USE_PROXY_OPTION => 0,
                    Loader::FAKE_USER_OS_OPTION => 1,
                ],
            ],
            LoaderManagerFactory::class => [
                LoaderManagerFactory::KEY_DOCUMENT_DATASTORE => DocumentDataStore::class,
                LoaderManagerFactory::KEY_LOADER => 'parseLoader',
                LoaderManagerFactory::KEY_TASK_DATASTORE => 'aspectTaskDataStore',
            ],
            CallbackAbstractFactoryAbstract::KEY => [
                'loaders' => [
                    MultiplexerAbstractFactory::KEY_CLASS => Multiplexer::class,
                    MultiplexerAbstractFactory::KEY_CALLBACKS_SERVICES => [
                        'loaderProcess',
                    ],
                ],
                'parsers' => [
                    MultiplexerAbstractFactory::KEY_CLASS => Multiplexer::class,
                    MultiplexerAbstractFactory::KEY_CALLBACKS_SERVICES => [
                        'searchParserProcess',
                        'productParserProcess',
                        'compatibleParserProcess',
                        'proxyParserProcess',
                    ],
                ],
            ],
            AbstractParserManagerFactory::KEY => [
                SimpleSearchParserManager::class => [
                    BaseParserManagerFactory::KEY_PARSER => SimpleSearchParser::class,
                    BaseParserManagerFactory::KEY_DOCUMENT_DATASTORE => DocumentDataStore::class,
                    SearchParserManagerFactory::KEY_TASK_DATASTORE => 'aspectTaskDataStore',
                    BaseParserManagerFactory::KEY_PARSE_RESULT_DATASTORE => 'searchProductDataStore',
                    BaseParserManagerFactory::KEY_OPTIONS => [
                        'createProductParseTask' => 1,
                        'productUri' => 'https://www.ebay.com/itm',
                        'compatibleUri' => 'https://frame.ebay.com/ebaymotors/ws/eBayISAPI.dll?'
                            . 'GetFitmentData&site=100&vs=0&req=2&cid=43977&item=',
                        'maxCorruptRecords' => 30,
                        'saveCorruptedProducts' => 1,
                    ],
                ],
                ProductParserManager::class => [
                    BaseParserManagerFactory::KEY_PARSER => ProductParser::class,
                    BaseParserManagerFactory::KEY_DOCUMENT_DATASTORE => DocumentDataStore::class,
                    BaseParserManagerFactory::KEY_PARSE_RESULT_DATASTORE => 'aspectProductDataStore',
                    BaseParserManagerFactory::KEY_OPTIONS => [
                        'maxCorruptRecords' => 30,
                        'saveCorruptedProducts' => 1,
                    ],
                ],
                CompatibleParserManager::class => [
                    BaseParserManagerFactory::KEY_PARSER => CompatibleParser::class,
                    BaseParserManagerFactory::KEY_DOCUMENT_DATASTORE => DocumentDataStore::class,
                    BaseParserManagerFactory::KEY_PARSE_RESULT_DATASTORE => 'compatibleProductDataStore',
                    BaseParserManagerFactory::KEY_OPTIONS => [],
                ],
                Proxy::class => [
                    BaseParserManagerFactory::KEY_PARSER => ProxyParser::class,
                    BaseParserManagerFactory::KEY_DOCUMENT_DATASTORE => DocumentDataStore::class,
                    BaseParserManagerFactory::KEY_PARSE_RESULT_DATASTORE  => 'aspectProxyDataStore',
                    BaseParserManagerFactory::KEY_OPTIONS => [],
                ],
            ],
            InterruptAbstractFactoryAbstract::KEY => [
                'loaderProcess' => [
                    ProcessAbstractFactory::KEY_CLASS => Process::class,
                    ProcessAbstractFactory::KEY_CALLBACK_SERVICE => LoaderManager::class,
                ],
                'searchParserProcess' => [
                    ProcessAbstractFactory::KEY_CLASS => Process::class,
                    ProcessAbstractFactory::KEY_CALLBACK_SERVICE => SimpleSearchParserManager::class,
                ],
                'productParserProcess' => [
                    ProcessAbstractFactory::KEY_CLASS => Process::class,
                    ProcessAbstractFactory::KEY_CALLBACK_SERVICE => ProductParserManager::class,
                ],
                'compatibleParserProcess' => [
                    ProcessAbstractFactory::KEY_CLASS => Process::class,
                    ProcessAbstractFactory::KEY_CALLBACK_SERVICE => CompatibleParserManager::class,
                ],
                'proxyParserProcess' => [
                    ProcessAbstractFactory::KEY_CLASS => Process::class,
                    ProcessAbstractFactory::KEY_CALLBACK_SERVICE => Proxy::class,
                ],
            ],
            DataStoreAbstractFactory::KEY_DATASTORE => [
                'productDataStore' => [
                    'class' => CsvBase::class,
                    'filename' => 'data/datastores/products.csv',
                    'delimiter' => ',',
                ],
                'searchProductDataStore' => [
                    'class' => CsvBase::class,
                    'filename' => 'data/datastores/search_products.csv',
                    'delimiter' => ',',
                ],
                'compatibleProductDataStore' => [
                    'class' => CsvBase::class,
                    'filename' => 'data/datastores/compatibles.csv',
                    'delimiter' => ',',
                ],

                'proxyDataStore' => [
                    'class' => CsvBase::class,
                    'filename' => 'data/datastores/proxies.csv',
                    'delimiter' => ',',
                ],
                'downloadDataStore' => [
                    'class' => CsvBase::class,
                    'filename' => 'data/datastores/documents.csv',
                    'delimiter' => ',',
                ],
                'taskDataStore' => [
                    'class' => CsvBase::class,
                    'filename' => 'data/datastores/tasks.csv',
                    'delimiter' => ',',
                ],

                // Aspects
                'aspectTaskDataStore' => [
                    'class' => TaskDataStore::class,
                    'dataStore' => 'taskDataStore',
                ],
                'aspectProductDataStore' => [
                    'class' => ProductDataStore::class,
                    'dataStore' => 'productDataStore',
                ],

                // Logs
                'logDataStore' => [
                    'class' => DbTable::class,
                    'tableGateway' => 'logs',
                ],
            ],
            TableGatewayAbstractFactory::KEY_TABLE_GATEWAY => [
                'logs' => [],
            ],
        ];
    }
}
