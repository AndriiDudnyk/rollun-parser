<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

declare(strict_types = 1);

namespace rollun\service\Parser\Ebay;

use rollun\callback\Callback\Factory\CallbackAbstractFactoryAbstract;
use rollun\callback\Callback\Factory\MultiplexerAbstractFactory;
use rollun\callback\Callback\Interrupter\Factory\InterruptAbstractFactoryAbstract;
use rollun\callback\Callback\Interrupter\Factory\ProcessAbstractFactory;
use rollun\callback\Callback\Interrupter\Process;
use rollun\callback\Callback\Multiplexer;
use rollun\datastore\DataStore\CsvBase;
use rollun\datastore\DataStore\Factory\DataStoreAbstractFactory;
use rollun\service\Parser\Ebay\Helper\SearchPage;
use rollun\service\Parser\Ebay\Helper\SearchPageFactory;

// Parsers
use rollun\service\Parser\Ebay\Parser\Parser\Product as ProductParser;
use rollun\service\Parser\Ebay\Parser\Parser\Compatible as CompatibleParser;
use rollun\service\Parser\Ebay\Parser\Parser\Search\Simple as SimpleSearchParser;
use rollun\service\Parser\Ebay\Parser\Parser\Search\EbayMotors as EbayMotorsSearchParser;

// Parser managers
use rollun\service\Parser\Ebay\Parser\Manager\Product as ProductParserManager;
use rollun\service\Parser\Ebay\Parser\Manager\Compatible as CompatibleParserManager;
use rollun\service\Parser\Ebay\Parser\Manager\Search\Simple as SimpleSearchParserManager;
use rollun\service\Parser\Ebay\Parser\Manager\Search\EbayMotors as EbayMotorsSearchParserManager;

use rollun\service\Parser\Ebay\Parser\Manager\Factory\SearchFactory as SearchParserManagerFactory;
use rollun\parser\Parser\Manager\BaseFactory as BaseParserManagerFactory;

// Loader managers
use rollun\parser\Loader\Manager\Base as BaseLoaderManager;

// Entity datastores
use rollun\service\Parser\Ebay\DataStore\Entity\Interfaces\ProductSearchInterface as ProductSearchEntityStoreInterface;
use rollun\service\Parser\Ebay\DataStore\Entity\Interfaces\ProductInterface as ProductEntityStoreInterface;
use rollun\service\Parser\Ebay\DataStore\Entity\Interfaces\CompatibleInterface as CompatibleEntityStoreInterface;
use rollun\parser\DataStore\Entity\LoaderTaskInterface as LoaderTaskStoreEntityInterface;
use rollun\parser\DataStore\Entity\ParserTaskInterface as ParserTaskStoreEntityInterface;
use rollun\service\Parser\Ebay\DataStore\Entity\Product as ProductEntityStore;
use rollun\service\Parser\Ebay\DataStore\Entity\Compatible as CompatibleEntityStore;
use rollun\service\Parser\Ebay\DataStore\Entity\ProductSearch as ProductSearchEntityStore;

// Page datastores
use rollun\service\Parser\Ebay\DataStore\Page\Compatible as CompatiblePageStore;
use rollun\service\Parser\Ebay\DataStore\Page\Product as ProductPageStore;
use rollun\service\Parser\Ebay\DataStore\Page\Search\Simple as SimpleSearchPageStore;
use rollun\service\Parser\Ebay\DataStore\Page\Search\EbayMotors as EbayMotorsSearchPageStore;

use rollun\parser\DataStore\Page\Factory\BaseFactory as BasePageStoreFactory;
use rollun\parser\DataStore\Page\Factory\AbstractPageFactory as AbstractPageStoreFactory;
use rollun\service\Parser\Ebay\DataStore\Page\Search\SearchFactory as BaseSearchPageStoreFactory;
use rollun\parser\DataStore\Page\PageDetectorFactory as PageStoreDetectorFactory;


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
                    ProductParser::class => ProductParser::class,
                    SimpleSearchParser::class => SimpleSearchParser::class,
                    CompatibleParser::class => CompatibleParser::class,
                    EbayMotorsSearchParser::class => EbayMotorsSearchParser::class,
                ],
                'factories' => [
                    // Manager factories
                    ProductParserManager::class => BaseParserManagerFactory::class,
                    SimpleSearchParserManager::class => SearchParserManagerFactory::class,
                    CompatibleParserManager::class => BaseParserManagerFactory::class,
                    EbayMotorsSearchParserManager::class => SearchParserManagerFactory::class,

                    // Store factories
                    CompatiblePageStore::class => BasePageStoreFactory::class,
                    ProductPageStore::class => BasePageStoreFactory::class,
                    SimpleSearchPageStore::class => BaseSearchPageStoreFactory::class,
                    EbayMotorsSearchPageStore::class => BaseSearchPageStoreFactory::class,

                    SearchPage::class => SearchPageFactory::class,
                ],
                'aliases' => [
                    // Page store (out)
                    'ebay-product-page-store' => ProductPageStore::class,
                    'ebay-simple-search-page-store' => SimpleSearchPageStore::class,
                    'ebay-motors-search-page-store' => EbayMotorsSearchPageStore::class,
                    'ebay-compatible-page-store' => CompatiblePageStore::class,
                ],
            ],
            PageStoreDetectorFactory::class => [
                ProductPageStore::class => "/https\:\/\/www\.ebay\.com\/itm\/[0-9]+/",
                SimpleSearchPageStore::class => "/https\:\/\/www\.ebay\.com\/sch\/eBay-Motors\//",
                EbayMotorsSearchPageStore::class => '/https\:\/\/www\.ebay\.com\/sch\//',
                CompatiblePageStore::class => "/https\:\/\/frame\.ebay\.com\/ebaymotors\/ws\/eBayISAPI\.dll"
                    . "?GetFitmentData/",

            ],
            SearchPageFactory::class => [
                SearchPageFactory::KEY_REDIRECT_URI => 'https://www.ebay.com/sch/FindingCustomization/'
                    . '?_fcdm=1&_fcss=12&_fcps=3&_fcippl=2&_fcso=1&_fcpd=1&_fcsbm=1&_pppn=v3'
                    . '&_fcpe=7%7C5%7C3%7C2%7C4&_fcie=1%7C36&_fcse=10%7C42%7C43&_fcsp=',
                SearchPageFactory::KEY_CLIENT => 'parseLoader',
            ],
            AbstractPageStoreFactory::KEY => [
                ProductPageStore::class => [
                    BasePageStoreFactory::KEY_LOADER => 'parseLoader',
                    BasePageStoreFactory::KEY_PARSER => ProductParser::class,
                ],
                SimpleSearchPageStore::class => [
                    BaseSearchPageStoreFactory::KEY_LOADER => 'parseLoader',
                    BaseSearchPageStoreFactory::KEY_PARSER => SimpleSearchParser::class,
                    BaseSearchPageStoreFactory::KEY_SEARCH_PAGE_HELPER => SearchPage::class,
                    BaseSearchPageStoreFactory::KEY_SEARCH_PAGE_HELPER => SearchPage::class,
                ],
                EbayMotorsSearchPageStore::class => [
                    BaseSearchPageStoreFactory::KEY_LOADER => 'parseLoader',
                    BaseSearchPageStoreFactory::KEY_PARSER => EbayMotorsSearchParser::class,
                    BaseSearchPageStoreFactory::KEY_SEARCH_PAGE_HELPER => SearchPage::class,
                ],
                CompatiblePageStore::class => [
                    BasePageStoreFactory::KEY_LOADER => 'parseLoader',
                    BasePageStoreFactory::KEY_PARSER => CompatibleParser::class,
                ],
            ],
            CallbackAbstractFactoryAbstract::KEY => [
                'ebay-loaders' => [
                    MultiplexerAbstractFactory::KEY_CLASS => Multiplexer::class,
                    MultiplexerAbstractFactory::KEY_CALLBACKS_SERVICES => [
                        __NAMESPACE__ . 'loaderProcess',
                    ],
                ],
                'ebay-parsers' => [
                    MultiplexerAbstractFactory::KEY_CLASS => Multiplexer::class,
                    MultiplexerAbstractFactory::KEY_CALLBACKS_SERVICES => [
                        //                        __NAMESPACE__ . 'simpleSearchParserProcess',
                        //                        __NAMESPACE__ . 'productParserProcess',
                        //                        __NAMESPACE__ . 'compatibleParserProcess',
                        __NAMESPACE__ . 'ebayMotorsSearchParserProcess',
                    ],
                ],
            ],
            BaseParserManagerFactory::KEY => [
                SimpleSearchParserManager::class => [
                    SearchParserManagerFactory::KEY_PARSER => SimpleSearchParser::class,
                    SearchParserManagerFactory::KEY_PARSER_TASK_DATASTORE => ParserTaskStoreEntityInterface::class,
                    SearchParserManagerFactory::KEY_TASK_DATASTORE => LoaderTaskStoreEntityInterface::class,
                    SearchParserManagerFactory::KEY_PARSE_RESULT_DATASTORE => ProductSearchEntityStoreInterface::class,
                    SearchParserManagerFactory::KEY_OPTIONS => [
                        'createProductParseTask' => 1,
                        'productUri' => 'https://www.ebay.com/itm',
                        'compatibleUriEpid' => 'https://frame.ebay.com/ebaymotors/ws/eBayISAPI.dll'
                            . '?GetFitmentData&req=1&cid=177773&ct=100&page=1&pid=',
                        'compatibleUriItmid' => 'https://frame.ebay.com/ebaymotors/ws/eBayISAPI.dll'
                            . '?GetFitmentData&req=2&ct=1000&page=1&item=',
                        'maxCorruptRecords' => 30,
                        'saveCorruptedProducts' => 1,
                    ],
                ],
                EbayMotorsSearchParserManager::class => [
                    SearchParserManagerFactory::KEY_PARSER => EbayMotorsSearchParser::class,
                    SearchParserManagerFactory::KEY_PARSER_TASK_DATASTORE => ParserTaskStoreEntityInterface::class,
                    SearchParserManagerFactory::KEY_PARSE_RESULT_DATASTORE => ProductSearchEntityStoreInterface::class,
                    SearchParserManagerFactory::KEY_TASK_DATASTORE => LoaderTaskStoreEntityInterface::class,
                    SearchParserManagerFactory::KEY_OPTIONS => [
                        'createProductParseTask' => 1,
                        'productUri' => 'https://www.ebay.com/itm',
                        'compatibleUriEpid' => 'https://frame.ebay.com/ebaymotors/ws/eBayISAPI.dll'
                            . '?GetFitmentData&req=1&cid=177773&ct=100&page=1&pid=',
                        'compatibleUriItmid' => 'https://frame.ebay.com/ebaymotors/ws/eBayISAPI.dll'
                            . '?GetFitmentData&req=2&ct=1000&page=1&item=',
                        'maxCorruptRecords' => 30,
                        'saveCorruptedProducts' => 1,
                    ],
                ],
                ProductParserManager::class => [
                    BaseParserManagerFactory::KEY_PARSER => ProductParser::class,
                    BaseParserManagerFactory::KEY_PARSER_TASK_DATASTORE => ParserTaskStoreEntityInterface::class,
                    BaseParserManagerFactory::KEY_PARSE_RESULT_DATASTORE => ProductEntityStoreInterface::class,
                    BaseParserManagerFactory::KEY_OPTIONS => [
                        'maxCorruptRecords' => 30,
                        'saveCorruptedProducts' => 1,
                    ],
                ],
                CompatibleParserManager::class => [
                    BaseParserManagerFactory::KEY_PARSER => CompatibleParser::class,
                    BaseParserManagerFactory::KEY_PARSER_TASK_DATASTORE => ParserTaskStoreEntityInterface::class,
                    BaseParserManagerFactory::KEY_PARSE_RESULT_DATASTORE => CompatibleEntityStoreInterface::class,
                    BaseParserManagerFactory::KEY_OPTIONS => [],
                ],
            ],
            InterruptAbstractFactoryAbstract::KEY => [
                __NAMESPACE__ . 'loaderProcess' => [
                    ProcessAbstractFactory::KEY_CLASS => Process::class,
                    ProcessAbstractFactory::KEY_CALLBACK_SERVICE => BaseLoaderManager::class,
                ],
                __NAMESPACE__ . 'simpleSearchParserProcess' => [
                    ProcessAbstractFactory::KEY_CLASS => Process::class,
                    ProcessAbstractFactory::KEY_CALLBACK_SERVICE => SimpleSearchParserManager::class,
                ],
                __NAMESPACE__ . 'productParserProcess' => [
                    ProcessAbstractFactory::KEY_CLASS => Process::class,
                    ProcessAbstractFactory::KEY_CALLBACK_SERVICE => ProductParserManager::class,
                ],
                __NAMESPACE__ . 'compatibleParserProcess' => [
                    ProcessAbstractFactory::KEY_CLASS => Process::class,
                    ProcessAbstractFactory::KEY_CALLBACK_SERVICE => CompatibleParserManager::class,
                ],
                __NAMESPACE__ . 'ebayMotorsSearchParserProcess' => [
                    ProcessAbstractFactory::KEY_CLASS => Process::class,
                    ProcessAbstractFactory::KEY_CALLBACK_SERVICE => EbayMotorsSearchParserManager::class,
                ],
            ],
            DataStoreAbstractFactory::KEY_DATASTORE => [
                __NAMESPACE__ . 'productEntityStore' => [
                    'class' => CsvBase::class,
                    'filename' => 'data/datastores/products.csv',
                    'delimiter' => ',',
                ],
                __NAMESPACE__ . 'productSearchEntityStore' => [
                    'class' => CsvBase::class,
                    'filename' => 'data/datastores/search_products.csv',
                    'delimiter' => ',',
                ],
                __NAMESPACE__ . 'compatibleEntityStore' => [
                    'class' => CsvBase::class,
                    'filename' => 'data/datastores/compatibles.csv',
                    'delimiter' => ',',
                ],
                ProductEntityStoreInterface::class => [
                    'class' => ProductEntityStore::class,
                    'dataStore' => __NAMESPACE__ . 'productEntityStore',
                ],
                ProductSearchEntityStoreInterface::class => [
                    'class' => ProductSearchEntityStore::class,
                    'dataStore' => __NAMESPACE__ . 'productSearchEntityStore',
                ],
                CompatibleEntityStoreInterface::class => [
                    'class' => CompatibleEntityStore::class,
                    'dataStore' => __NAMESPACE__ . 'compatibleEntityStore',
                ],
            ],
        ];
    }
}
