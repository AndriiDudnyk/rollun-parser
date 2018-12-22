<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

declare(strict_types = 1);

namespace Parser;

use Parser\DataStore\DocumentDataStore;
use Parser\DataStore\DocumentDataStoreFactory;
use Parser\DataStore\TaskDataStore;
use Parser\Loader\Loader;
use Parser\Loader\LoaderFactory;
use Parser\Manager\BaseParserManager;
use Parser\Manager\Factory\BaseParserManagerAbstractFactory;
use Parser\Manager\Factory\LoaderManagerFactory;
use Parser\Manager\LoaderManager;
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
use rollun\datastore\DataStore\Factory\DataStoreAbstractFactory;

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
                ],
                'factories' => [
                    Loader::class => LoaderFactory::class,
                    LoaderManager::class => LoaderManagerFactory::class,
                    DocumentDataStore::class => DocumentDataStoreFactory::class,
                ],
                'abstract_factories' => [
                    BaseParserManagerAbstractFactory::class
                ]
            ],
            LoaderFactory::class => [
                LoaderFactory::KEY_PROXY_DATASTORE => 'proxyDataStore',
            ],
            DocumentDataStoreFactory::class => [
                DocumentDataStoreFactory::KEY_DOWNLOAD_DATASTORE => 'downloadDataStore',
                DocumentDataStoreFactory::KEY_STORE_DIR => 'data',
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
                    ]
                ],
                'parsers' => [
                    MultiplexerAbstractFactory::KEY_CLASS => Multiplexer::class,
                    MultiplexerAbstractFactory::KEY_CALLBACKS_SERVICES => [
                        'parserProcess',
                    ]
                ],
            ],
            'parserManagers' => [
                'simpleParserManager' => [
                    'class' => BaseParserManager::class,
                    'parser' => SearchParser::class,
                    'documentDataStore' => DocumentDataStore::class,
                    'parseResultDataStore' => 'products',
                ]
            ],
            InterruptAbstractFactoryAbstract::KEY => [
                'loaderProcess' => [
                    ProcessAbstractFactory::KEY_CLASS => Process::class,
                    ProcessAbstractFactory::KEY_CALLBACK_SERVICE => LoaderManager::class,
                ],
                'parserProcess' => [
                    ProcessAbstractFactory::KEY_CLASS => Process::class,
                    ProcessAbstractFactory::KEY_CALLBACK_SERVICE => 'simpleParserManager',
                ],
            ],
            DataStoreAbstractFactory::KEY_DATASTORE => [
                'products' => [
                    'class' => CsvBase::class,
                    'filename' => 'data/datastores/products.csv',
                    'delimiter' => ','
                ],
                'proxyDataStore' => [
                    'class' => CsvBase::class,
                    'filename' => 'data/datastores/proxies.csv',
                    'delimiter' => ','
                ],
                'downloadDataStore' => [
                    'class' => CsvBase::class,
                    'filename' => 'data/datastores/documents.csv',
                    'delimiter' => ','
                ],

                // Use 'aspectTaskDataStore'
                'taskDataStore' => [
                    'class' => CsvBase::class,
                    'filename' => 'data/datastores/tasks.csv',
                    'delimiter' => ','
                ],
                'aspectTaskDataStore' => [
                    'class' => TaskDataStore::class,
                    'dataStore' => 'taskDataStore',
                ],
            ],
        ];
    }
}
