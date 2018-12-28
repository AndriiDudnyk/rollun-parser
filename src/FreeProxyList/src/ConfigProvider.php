<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\FreeProxyList;

use rollun\callback\Callback\Factory\CallbackAbstractFactoryAbstract;
use rollun\callback\Callback\Factory\MultiplexerAbstractFactory;
use rollun\callback\Callback\Interrupter\Factory\InterruptAbstractFactoryAbstract;
use rollun\callback\Callback\Interrupter\Factory\ProcessAbstractFactory;
use rollun\callback\Callback\Interrupter\Process;
use rollun\callback\Callback\Multiplexer;
use rollun\datastore\DataStore\CsvBase;
use rollun\datastore\DataStore\Factory\DataStoreAbstractFactory;
use rollun\parser\DataStore\Entity\LoaderTaskInterface;
use rollun\parser\DataStore\Entity\ParserTaskInterface;
use rollun\parser\Loader\Loader\LoaderAbstractFactory;
use rollun\parser\Loader\Loader\LoaderInterface;
use rollun\service\Parser\FreeProxyList\DataStore\Entity\Proxy as ProxyEntityStore;
use rollun\service\Parser\FreeProxyList\DataStore\Entity\ProxyFactory as ProxyEntityStoreFactory;
use rollun\service\Parser\FreeProxyList\DataStore\Entity\ProxyInterface as ProxyEntityStoreInterface;

use rollun\service\Parser\FreeProxyList\Parser\Manager\HomePage as HomePageParserManager;
use rollun\service\Parser\FreeProxyList\Parser\Parser\HomePage as HomePageParser;
use rollun\parser\Parser\Manager\BaseFactory as BaseParserManagerFactory;
use rollun\parser\Parser\Manager\AbstractFactory as AbstractParserManagerFactory;

// Loader managers
use rollun\parser\Loader\Manager\Base as BaseLoaderManager;
use rollun\parser\Loader\Manager\AbstractFactory as AbstractLoaderManagerFactory;
use rollun\parser\Loader\Manager\BaseFactory as BaseLoaderManagerFactory;

use rollun\parser\DataStore\Entity\ParserTaskInterface as ParserTaskStoreEntityInterface;

class ConfigProvider
{
    const PROXY_LOADER_MANAGER = __NAMESPACE__ . 'proxyLoaderManager';

    public function __invoke()
    {
        return [
            'dependencies' => [
                'invokables' => [
                    HomePageParser::class => HomePageParser::class,
                ],
                'factories' => [
                    ProxyEntityStore::class => ProxyEntityStoreFactory::class,
                    __NAMESPACE__ . 'proxyLoaderManager' => BaseLoaderManagerFactory::class,
                    __NAMESPACE__ . 'proxyParserManager' => BaseParserManagerFactory::class
                ],
                'abstract_factories' => [
                    LoaderAbstractFactory::class,
                ],
                'aliases' => [
                    ProxyEntityStoreInterface::class => ProxyEntityStore::class,
                ],
            ],
            ProxyEntityStoreFactory::class => [
                ProxyEntityStoreFactory::KEY_LOADER_TASK_DATASTORE => LoaderTaskInterface::class,
                ProxyEntityStoreFactory::KEY_ASPECT_DATASTORE => __NAMESPACE__ . 'proxyDataStore',
                ProxyEntityStoreFactory::KEY_PROXY_LIST_URI => 'https://free-proxy-list.net/',
                ProxyEntityStoreFactory::KEY_TASK_OPTIONS => [
                    LoaderInterface::USE_PROXY_OPTION => 0,
                ],
            ],
            InterruptAbstractFactoryAbstract::KEY => [
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
                'proxy-loaders' => [
                    MultiplexerAbstractFactory::KEY_CLASS => Multiplexer::class,
                    MultiplexerAbstractFactory::KEY_CALLBACKS_SERVICES => [
                        __NAMESPACE__ . 'proxyLoaderProcess',
                    ],
                ],
                'proxy-parsers' => [
                    MultiplexerAbstractFactory::KEY_CLASS => Multiplexer::class,
                    MultiplexerAbstractFactory::KEY_CALLBACKS_SERVICES => [
                        __NAMESPACE__ . 'proxyParserProcess',
                    ],
                ],
            ],
            AbstractLoaderManagerFactory::KEY => [
                __NAMESPACE__ . 'proxyLoaderManager' => [
                    BaseLoaderManagerFactory::KEY_CLASS => BaseLoaderManager::class,
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
                    'class' => CsvBase::class,
                    'filename' => 'data/datastores/proxies.csv',
                    'delimiter' => ',',
                ],
            ],
        ];
    }
}
