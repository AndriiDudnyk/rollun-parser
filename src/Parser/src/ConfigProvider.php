<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

declare(strict_types = 1);

namespace Parser;

use Parser\DataStore\Parser\Factory\AbstractStorageFactory;
use Parser\DataStore\Parser\Factory\BaseStorageFactory;
use Parser\DataStore\Parser\Factory\SearchStorageFactory;
use Parser\DataStore\Parser\Factory\StoragePluginManagerFactory;
use Parser\DataStore\Parser\Product as ProductStorage;
use Parser\DataStore\Parser\Search as SearchStorage;
use Parser\DataStore\Parser\Compatible as CompatibleStorage;
use Parser\DataStore\Parser\StorageDetector;
use Parser\DataStore\Parser\StorageDetectorFactory;
use Parser\DataStore\Parser\StoragePluginManager;
use Parser\Loader\SearchLoaderHelper;
use Parser\Loader\SearchLoaderHelperFactory;
use Parser\Parser\Compatible as CompatibleParser;
use Parser\Parser\Product as ProductParser;
use Parser\Parser\Search as SearchParser;
use Parser\DataStore\Document as DocumentDataStore;
use Parser\DataStore\Product as ProductDataStore;
use Parser\DataStore\SearchTask as SearchTaskDataStore;
use Parser\DataStore\Task as TaskDataStore;
use Parser\DataStore\SearchTaskFactory;
use Parser\DataStore\DocumentFactory;
use Parser\Loader\Loader;
use Parser\Loader\LoaderAbstractFactory;
use Parser\Manager\BaseParserManager;
use Parser\Manager\CompatibleParserManager;
use Parser\Manager\Factory\AbstractParserManagerFactory;
use Parser\Manager\Factory\BaseParserManagerFactory;
use Parser\Manager\Factory\LoaderManagerFactory;
use Parser\Manager\Factory\SearchParserManagerFactory;
use Parser\Manager\LoaderManager;
use Parser\Manager\ProductParserManager;
use Parser\Manager\SearchParserManager;
use rollun\callback\Callback\Factory\CallbackAbstractFactoryAbstract;
use rollun\callback\Callback\Factory\MultiplexerAbstractFactory;
use rollun\callback\Callback\Interrupter\Factory\InterruptAbstractFactoryAbstract;
use rollun\callback\Callback\Interrupter\Factory\ProcessAbstractFactory;
use rollun\callback\Callback\Interrupter\Process;
use rollun\callback\Callback\Multiplexer;
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
                    SearchParser::class => SearchParser::class,
                    CompatibleParser::class => CompatibleParser::class,
                ],
                'factories' => [
                    LoaderManager::class => LoaderManagerFactory::class,
                    DocumentDataStore::class => DocumentFactory::class,
                    BaseParserManager::class => BaseParserManagerFactory::class,
                    ProductParserManager::class => BaseParserManagerFactory::class,
                    SearchParserManager::class => SearchParserManagerFactory::class,
                    CompatibleParserManager::class => BaseParserManagerFactory::class,
                    SearchTaskDataStore::class => SearchTaskFactory::class,
                    SearchLoaderHelper::class => SearchLoaderHelperFactory::class,
                    ProductStorage::class => BaseStorageFactory::class,
                    CompatibleStorage::class => BaseStorageFactory::class,
                    SearchStorage::class => SearchStorageFactory::class,
                    StorageDetector::class => StorageDetectorFactory::class,
                    StoragePluginManager::class => StoragePluginManagerFactory::class
                ],
                'abstract_factories' => [
                    LoaderAbstractFactory::class,
                ],
                'aliases' => [
                    'productStorage' => ProductStorage::class,
                    'searchStorage' => SearchStorage::class,
                    'compatibleStorage' => CompatibleStorage::class,
                    'parserStorage' => StorageDetector::class
                ]
            ],
            StorageDetectorFactory::class => [
                'productStorage' => "/https\:\/\/www\.ebay\.com\/itm\/[0-9]+/",
                'searchStorage' => "/https\:\/\/www\.ebay\.com\/sch\//",
                'compatibleStorage' => "/https\:\/\/frame\.ebay\.com\/ebaymotors\/ws\/eBayISAPI\.dll\?GetFitmentData/",
            ],
            SearchLoaderHelperFactory::class => [
                SearchLoaderHelperFactory::KEY_REDIRECT_URI => 'https://www.ebay.com/sch/FindingCustomization/'
                    . '?_fcdm=1&_fcss=12&_fcps=3&_fcippl=2&_fcso=1&_fcpd=1&_fcsbm=1&_pppn=v3'
                    . '&_fcpe=7%7C5%7C3%7C2%7C4&_fcie=1%7C36&_fcse=10%7C42%7C43&_fcsp=',
                SearchLoaderHelperFactory::KEY_CLIENT => 'searchTaskLoader'
            ],
            AbstractStorageFactory::KEY => [
                ProductStorage::class => [
                    'loader' => 'parseLoader',
                    'parser' => ProductParser::class,
                ],
                SearchStorage::class => [
                    'loader' => 'parseLoader',
                    'parser' => SearchParser::class,
                    'searchLoaderHelper' => SearchLoaderHelper::class
                ],
                CompatibleStorage::class => [
                    'loader' => 'parseLoader',
                    'parser' => CompatibleParser::class,
                ],
            ],
            LoaderAbstractFactory::class => [
                'parseLoader' => [
                    LoaderAbstractFactory::KEY_PROXY_DATASTORE => 'proxyDataStore',
                    LoaderAbstractFactory::KEY_OPTIONS => [
                        Loader::COOKIE_DOMAIN_OPTION => '.ebay.com',
                        Loader::USE_PROXY_OPTION => 1,
                        Loader::MAX_ATTEMPTS_OPTION => 10,
                        Loader::CONNECTION_TIMEOUT_OPTION => 10,
                        Loader::ALLOW_REDIRECT_OPTION => 1,
                    ],
                ],
                'searchTaskLoader' => [
                    LoaderAbstractFactory::KEY_PROXY_DATASTORE => 'proxyDataStore',
                    LoaderAbstractFactory::KEY_OPTIONS => [
                        Loader::USE_PROXY_OPTION => 0,
                        Loader::MAX_ATTEMPTS_OPTION => 5,
                    ],
                ],
            ],
            DocumentFactory::class => [
                DocumentFactory::KEY_DOWNLOAD_DATASTORE => 'downloadDataStore',
                DocumentFactory::KEY_STORE_DIR => 'data/documents',
            ],
            SearchTaskFactory::class => [
                SearchTaskFactory::KEY_TASK_DATASTORE => 'aspectTaskDataStore',
                SearchTaskFactory::KEY_SEARCH_LOADER_HELPER => SearchLoaderHelper::class,
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
                    ],
                ],
            ],
            AbstractParserManagerFactory::KEY => [
                SearchParserManager::class => [
                    'parser' => SearchParser::class,
                    'documentDataStore' => DocumentDataStore::class,
                    'taskDataStore' => 'aspectTaskDataStore',
                    'parseResultDataStore' => 'searchProductDataStore',
                    'options' => [
                        'createProductParseTask' => 1,
                        'productUri' => 'https://www.ebay.com/itm',
                        'compatibleUri' => 'https://frame.ebay.com/ebaymotors/ws/eBayISAPI.dll?'
                            . 'GetFitmentData&site=100&vs=0&req=2&cid=43977&item=',
                        'maxCorruptRecords' => 30,
                        'saveCorruptedProducts' => 1,
                    ],
                ],
                ProductParserManager::class => [
                    'parser' => ProductParser::class,
                    'documentDataStore' => DocumentDataStore::class,
                    'parseResultDataStore' => 'aspectProductDataStore',
                    'options' => [
                        'maxCorruptRecords' => 30,
                        'saveCorruptedProducts' => 1,
                    ],
                ],
                CompatibleParserManager::class => [
                    'parser' => CompatibleParser::class,
                    'documentDataStore' => DocumentDataStore::class,
                    'parseResultDataStore' => 'compatibleProductDataStore',
                    'options' => [],
                ],
            ],
            InterruptAbstractFactoryAbstract::KEY => [
                'loaderProcess' => [
                    ProcessAbstractFactory::KEY_CLASS => Process::class,
                    ProcessAbstractFactory::KEY_CALLBACK_SERVICE => LoaderManager::class,
                ],
                'searchParserProcess' => [
                    ProcessAbstractFactory::KEY_CLASS => Process::class,
                    ProcessAbstractFactory::KEY_CALLBACK_SERVICE => SearchParserManager::class,
                ],
                'productParserProcess' => [
                    ProcessAbstractFactory::KEY_CLASS => Process::class,
                    ProcessAbstractFactory::KEY_CALLBACK_SERVICE => ProductParserManager::class,
                ],
                'compatibleParserProcess' => [
                    ProcessAbstractFactory::KEY_CLASS => Process::class,
                    ProcessAbstractFactory::KEY_CALLBACK_SERVICE => CompatibleParserManager::class,
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


                // Use 'aspectTaskDataStore'
                'taskDataStore' => [
                    'class' => CsvBase::class,
                    'filename' => 'data/datastores/tasks.csv',
                    'delimiter' => ',',
                ],
                'aspectTaskDataStore' => [
                    'class' => TaskDataStore::class,
                    'dataStore' => 'taskDataStore',
                ],

                'aspectDataStore' => [
                    'class' => CsvBase::class,
                    'filename' => 'data/datastores/products.csv',
                    'delimiter' => ',',
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
