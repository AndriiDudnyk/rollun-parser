<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\parser;

use rollun\datastore\DataStore\CsvBase;
use rollun\datastore\DataStore\Factory\DataStoreAbstractFactory;
use rollun\parser\DataStore\Entity\LoaderTask;
use rollun\parser\DataStore\Entity\LoaderTaskInterface;
use rollun\parser\DataStore\Entity\ParserTask;
use rollun\parser\DataStore\Entity\ParserTaskFactory;
use rollun\parser\DataStore\Entity\ParserTaskInterface;
use rollun\parser\Loader\Loader\LoaderAbstractFactory;
use rollun\parser\Loader\Loader\LoaderInterface;
use rollun\parser\Loader\Manager\Base as BaseLoaderManager;
use rollun\parser\Loader\Manager\BaseFactory as BaseLoaderManagerFactory;
use rollun\service\Parser\FreeProxyList\DataStore\Entity\ProxyInterface as ProxyEntityStoreInterface;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'dependencies' => [
                'invokables' => [
                    UserAgentGenerator::class => UserAgentGenerator::class,
                ],
                'factories' => [
                    ParserTask::class => ParserTaskFactory::class,
                    BaseLoaderManager::class => BaseLoaderManagerFactory::class
                ],
                'abstract_factories' => [
                    LoaderAbstractFactory::class,
                ],
                'aliases' => [
                    ParserTaskInterface::class => ParserTask::class,
                    LoaderTaskInterface::class => __NAMESPACE__ . 'aspectLoaderTaskDataStore',
                    LoaderInterface::class => __NAMESPACE__ . 'baseLoader',
                ]
            ],
            LoaderAbstractFactory::class => [
                __NAMESPACE__ . 'baseLoader' => [
                    LoaderAbstractFactory::KEY_PROXY_DATASTORE => ProxyEntityStoreInterface::class,
                    LoaderAbstractFactory::KEY_OPTIONS => [
                        LoaderInterface::CREATE_TASK_IF_NO_PROXIES_OPTION => 1,
                        LoaderInterface::COOKIE_DOMAIN_OPTION => '.ebay.com',
                        LoaderInterface::USE_PROXY_OPTION => 1,
                        LoaderInterface::MAX_ATTEMPTS_OPTION => 10,
                        LoaderInterface::CONNECTION_TIMEOUT_OPTION => 10,
                        LoaderInterface::ALLOW_REDIRECT_OPTION => 1,
                    ],
                ],
            ],
            BaseLoaderManagerFactory::class => [
                BaseLoaderManagerFactory::KEY_LOADER => LoaderInterface::class,
                BaseLoaderManagerFactory::KEY_LOADER_TASK_DATASTORE => LoaderTaskInterface::class,
                BaseLoaderManagerFactory::KEY_PARSER_TASK_DATASTORE => ParserTaskInterface::class,
            ],
            ParserTaskFactory::class => [
                ParserTaskFactory::KEY_PARSER_TASK_DATASTORE =>  __NAMESPACE__ . 'parserTaskDataStore',
                ParserTaskFactory::KEY_STORE_DIR => 'data/documents',
            ],
            DataStoreAbstractFactory::KEY_DATASTORE => [
                __NAMESPACE__ . 'parserTaskDataStore' => [
                    'class' => CsvBase::class,
                    'filename' => 'data/datastores/parser_tasks.csv',
                    'delimiter' => ',',
                ],
                __NAMESPACE__ . 'loaderTaskDataStore' => [
                    'class' => CsvBase::class,
                    'filename' => 'data/datastores/loader_tasks.csv',
                    'delimiter' => ',',
                ],
                __NAMESPACE__ . 'aspectLoaderTaskDataStore' => [
                    'class' => LoaderTask::class,
                    'dataStore' => __NAMESPACE__ . 'loaderTaskDataStore',
                ],
            ],
        ];
    }
}
