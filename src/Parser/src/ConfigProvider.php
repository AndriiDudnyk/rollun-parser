<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

declare(strict_types = 1);

namespace Parser;

use Parser\DataStore\DocumentDataStore;
use Parser\DataStore\DocumentDataStoreFactory;
use Parser\DataStore\ProductDataStore;
use Parser\DataStore\TaskDataStore;
use Parser\Loader\Loader;
use Parser\Loader\LoaderFactory;
use Parser\Manager\BaseParserManager;
use Parser\Manager\CompatibleParserManager;
use Parser\Manager\Factory\AbstractParserManagerFactory;
use Parser\Manager\Factory\BaseParserManagerFactory;
use Parser\Manager\Factory\LoaderManagerFactory;
use Parser\Manager\Factory\SearchParserManagerFactory;
use Parser\Manager\LoaderManager;
use Parser\Manager\ProductParserManager;
use Parser\Manager\SearchParserManager;
use Parser\Parser\CompatibleParser;
use Parser\Parser\ProductParser;
use Parser\Parser\SearchParser;
use Parser\Parser\SellerParser;
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
                    SellerParser::class => SellerParser::class,
                    CompatibleParser::class => CompatibleParser::class,
                ],
                'factories' => [
                    Loader::class => LoaderFactory::class,
                    LoaderManager::class => LoaderManagerFactory::class,
                    DocumentDataStore::class => DocumentDataStoreFactory::class,
                    BaseParserManager::class => BaseParserManagerFactory::class,
                    ProductParserManager::class => BaseParserManagerFactory::class,
                    SearchParserManager::class => SearchParserManagerFactory::class,
                    CompatibleParserManager::class => BaseParserManagerFactory::class,
                ],
            ],
            LoaderFactory::class => [
                LoaderFactory::KEY_PROXY_DATASTORE => 'proxyDataStore',
            ],
            DocumentDataStoreFactory::class => [
                DocumentDataStoreFactory::KEY_DOWNLOAD_DATASTORE => 'downloadDataStore',
                DocumentDataStoreFactory::KEY_STORE_DIR => 'data/documents',
            ],
            LoaderManagerFactory::class => [
                LoaderManagerFactory::KEY_DOCUMENT_DATASTORE => DocumentDataStore::class,
                LoaderManagerFactory::KEY_LOADER => Loader::class,
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
//                        'searchParserProcess',
                        'productParserProcess',
//                        'compatibleParserProcess',
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
