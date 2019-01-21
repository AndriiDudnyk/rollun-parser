<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\FreeProxyList;

use rollun\callback\Callback\Factory\CallbackAbstractFactoryAbstract;
use rollun\callback\Callback\Factory\MultiplexerAbstractFactory;
use rollun\callback\Callback\Factory\TickerAbstractFactory;
use rollun\callback\Callback\Interrupter\Factory\InterruptAbstractFactoryAbstract;
use rollun\callback\Callback\Interrupter\Factory\ProcessAbstractFactory;
use rollun\callback\Callback\Interrupter\Process;
use rollun\callback\Callback\Multiplexer;
use rollun\callback\Callback\Ticker;
use rollun\datastore\DataStore\Factory\DataStoreAbstractFactory;
use rollun\parser\DataStore\AutoGenerateIdAspect;
use rollun\parser\DataStore\Entity\LoaderTaskInterface;
use rollun\parser\DataStore\Entity\ParserTaskInterface;
use rollun\parser\DataStore\LoggedDbTable;
use rollun\parser\Loader\Loader\LoaderAbstractFactory;
use rollun\parser\Loader\Loader\LoaderInterface;
use rollun\service\Parser\FreeProxyList\DataStore\Entity\Proxy as ProxyEntityStore;
use rollun\service\Parser\FreeProxyList\DataStore\Entity\ProxyInterface as ProxyEntityStoreInterface;

use rollun\service\Parser\FreeProxyList\Parser\Manager\HomePage as HomePageParserManager;
use rollun\service\Parser\FreeProxyList\Parser\Parser\HomePage as HomePageParser;
use rollun\parser\Parser\Manager\BaseFactory as BaseParserManagerFactory;
use rollun\parser\Parser\Manager\AbstractFactory as AbstractParserManagerFactory;

// Loader managers
use rollun\service\Parser\FreeProxyList\Loader\Manager\Proxy as ProxyLoaderManager;
use rollun\parser\Loader\Manager\AbstractFactory as AbstractLoaderManagerFactory;
use rollun\parser\Loader\Manager\BaseFactory as BaseLoaderManagerFactory;

use rollun\parser\DataStore\Entity\ParserTaskInterface as ParserTaskStoreEntityInterface;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'dependencies' => [
                'invokables' => [
                    HomePageParser::class => HomePageParser::class,
                ],
                'factories' => [
                    ProxyManager::class => ProxyManagerFactory::class,
                    __NAMESPACE__ . 'proxyLoaderManager' => BaseLoaderManagerFactory::class,
                    __NAMESPACE__ . 'proxyParserManager' => BaseParserManagerFactory::class
                ],
                'abstract_factories' => [
                    LoaderAbstractFactory::class,
                ],
            ],
            ProxyManagerFactory::class => [
                ProxyManagerFactory::KEY_LOADER_TASK_DATASTORE => LoaderTaskInterface::class,
                ProxyManagerFactory::KEY_PROXY_DATASTORE => ProxyEntityStoreInterface::class,
                ProxyManagerFactory::KEY_PROXY_LIST_URI => 'https://free-proxy-list.net/',
                ProxyManagerFactory::KEY_LOADER_TASK_OPTIONS => [
                    LoaderInterface::USE_PROXY_OPTION => 0,
                ],
            ],
            InterruptAbstractFactoryAbstract::KEY => [
                'proxyProcess' => [
                    ProcessAbstractFactory::KEY_CLASS => Process::class,
                    ProcessAbstractFactory::KEY_CALLBACK_SERVICE => __NAMESPACE__ . 'proxyTicker',
                ],
                __NAMESPACE__ . 'proxyLoaderProcess' => [
                    ProcessAbstractFactory::KEY_CLASS => Process::class,
                    ProcessAbstractFactory::KEY_CALLBACK_SERVICE => __NAMESPACE__ . 'proxyLoaderManager',
                ],
                __NAMESPACE__ . 'proxyParserProcess' => [
                    ProcessAbstractFactory::KEY_CLASS => Process::class,
                    ProcessAbstractFactory::KEY_CALLBACK_SERVICE => __NAMESPACE__ . 'proxyParserManager',
                ],
            ],
            CallbackAbstractFactoryAbstract::KEY => [
                __NAMESPACE__ . 'proxyLoaders' => [
                    MultiplexerAbstractFactory::KEY_CLASS => Multiplexer::class,
                    MultiplexerAbstractFactory::KEY_CALLBACKS_SERVICES => [
                        __NAMESPACE__ . 'proxyLoaderProcess',
                    ],
                ],
                __NAMESPACE__ . 'proxyParsers' => [
                    MultiplexerAbstractFactory::KEY_CLASS => Multiplexer::class,
                    MultiplexerAbstractFactory::KEY_CALLBACKS_SERVICES => [
                        __NAMESPACE__ . 'proxyParserProcess',
                    ],
                ],
                __NAMESPACE__ . 'proxyMultiplexer' => [
                    MultiplexerAbstractFactory::KEY_CLASS => Multiplexer::class,
                    MultiplexerAbstractFactory::KEY_CALLBACKS_SERVICES => [
                        __NAMESPACE__ . 'proxyLoaders',
                        __NAMESPACE__ . 'proxyParsers',
                    ]
                ],
                __NAMESPACE__ . 'proxyTicker' => [
                    TickerAbstractFactory::KEY_CLASS => Ticker::class,
                    TickerAbstractFactory::KEY_TICKS_COUNT => intval(getenv('PROXY_TICK_COUNT')),
                    TickerAbstractFactory::KEY_TICK_DURATION => intval(60 / getenv('PROXY_TICK_COUNT')),
                    TickerAbstractFactory::KEY_CALLBACK => __NAMESPACE__ . 'proxyMultiplexer',
                ]
            ],
            AbstractLoaderManagerFactory::KEY => [
                __NAMESPACE__ . 'proxyLoaderManager' => [
                    BaseLoaderManagerFactory::KEY_CLASS => ProxyLoaderManager::class,
                    BaseLoaderManagerFactory::KEY_LOADER => LoaderInterface::class,
                    BaseLoaderManagerFactory::KEY_LOADER_TASK_DATASTORE => LoaderTaskInterface::class,
                    BaseLoaderManagerFactory::KEY_PARSER_TASK_DATASTORE => ParserTaskInterface::class,
                    BaseLoaderManagerFactory::KEY_PARSER_NAMES => [
                        HomePageParser::PARSER_NAME
                    ],
                ],
            ],
            AbstractParserManagerFactory::KEY => [
                __NAMESPACE__ . 'proxyParserManager' => [
                    BaseParserManagerFactory::KEY_CLASS => HomePageParserManager::class,
                    BaseParserManagerFactory::KEY_PARSER => HomePageParser::class,
                    BaseParserManagerFactory::KEY_PARSER_TASK_DATASTORE => ParserTaskStoreEntityInterface::class,
                    BaseParserManagerFactory::KEY_PARSE_RESULT_DATASTORE => ProxyEntityStoreInterface::class,
                    BaseParserManagerFactory::KEY_OPTIONS => [
                        'maxCorruptRecords' => 30,
                        'saveCorruptedProducts' => 1,
                    ],
                ],
            ],
            DataStoreAbstractFactory::KEY_DATASTORE => [
                __NAMESPACE__ . 'proxyDataStore' => [
                    'class' => LoggedDbTable::class,
                    'tableGateway' => 'proxies',
                ],
                __NAMESPACE__ . 'proxyDataStoreAutoGeneratedId' => [
                    'class' => AutoGenerateIdAspect::class,
                    'dataStore' => __NAMESPACE__ . 'proxyDataStore',
                ],

                ProxyEntityStoreInterface::class => [
                    'class' => ProxyEntityStore::class,
                    'dataStore' => __NAMESPACE__ . 'proxyDataStoreAutoGeneratedId',
                ],
            ],
            'tableGateway' => [
                'proxies' => [],
            ]
        ];
    }
}
