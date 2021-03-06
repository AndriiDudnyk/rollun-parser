<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

declare(strict_types = 1);

namespace rollun\service\Parser\Ebay;

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
use rollun\parser\DataStore\Page\PageDetectorFactory;
use rollun\parser\Loader\Loader\LoaderInterface;
use rollun\parser\Parser\Parser\LazyParserAbstractFactory;
use rollun\service\Parser\Ebay\Helper\SearchPage as SearchPageHelper;
use rollun\service\Parser\Ebay\Helper\SearchPageFactory;

// Parsers
use rollun\service\Parser\Ebay\Parser\Parser\Product as ProductParser;
use rollun\service\Parser\Ebay\Parser\Parser\Compatible as CompatibleParser;
use rollun\service\Parser\Ebay\Parser\Parser\Search\Simple as SimpleSearchParser;
use rollun\service\Parser\Ebay\Parser\Parser\Search\EbayMotors as EbayMotorsSearchParser;

// Parser managers
use rollun\service\Parser\Ebay\Parser\Manager\Product as ProductParserManager;
use rollun\service\Parser\Ebay\Parser\Manager\Compatible as CompatibleParserManager;
use rollun\service\Parser\Ebay\Parser\Manager\Search as SearchParserManager;
use rollun\service\Parser\Ebay\Parser\Manager\SoldSearch as SoldSearchParserManager;

use rollun\service\Parser\Ebay\Parser\Manager\Factory\SearchFactory as SearchParserManagerFactory;
use rollun\service\Parser\Ebay\Parser\Manager\Factory\ProductFactory as ProductParserManagerFactory;
use rollun\service\Parser\Ebay\Parser\Manager\Factory\CompatibleFactory as CompatibleParserManagerFactory;
use rollun\service\Parser\Ebay\Parser\Manager\Factory\SoldSearchFactory
    as SoldEbayMotorsSearchParserManagerFactory;
use rollun\parser\Parser\Manager\AbstractFactory as AbstractParserManagerFactory;

// Loader managers
use rollun\parser\Loader\Manager\Base as BaseLoaderManager;
use rollun\service\Parser\Ebay\Loader\Manager\Search as SearchLoaderManager;
use rollun\service\Parser\Ebay\Loader\Manager\SoldSearch as SoldProductSearchLoaderManager;
use rollun\service\Parser\Ebay\Loader\Manager\SearchFactory as SearchLoaderManagerFactory;
use rollun\parser\Loader\Manager\AbstractFactory as AbstractLoaderManagerFactory;
use rollun\parser\Loader\Manager\BaseFactory as BaseLoaderManagerFactory;

// Entity datastores
use rollun\service\Parser\Ebay\DataStore\Entity\Interfaces\ProductInterface as ProductEntityStoreInterface;
use rollun\service\Parser\Ebay\DataStore\Entity\Interfaces\CompatibleInterface as CompatibleEntityStoreInterface;
use rollun\service\Parser\Ebay\DataStore\Entity\Interfaces\SoldProductInterface as SoldProductEntityStoreInterface;
use rollun\parser\DataStore\Entity\LoaderTaskInterface as LoaderTaskStoreEntityInterface;
use rollun\parser\DataStore\Entity\ParserTaskInterface as ParserTaskStoreEntityInterface;
use rollun\service\Parser\Ebay\DataStore\Entity\Product as ProductEntityStore;
use rollun\service\Parser\Ebay\DataStore\Entity\SoldProduct as SoldProductEntityStore;
use rollun\service\Parser\Ebay\DataStore\Entity\Compatible as CompatibleEntityStore;

// Page datastores
use rollun\service\Parser\Ebay\DataStore\Page\Compatible as CompatiblePageStore;
use rollun\service\Parser\Ebay\DataStore\Page\Product as ProductPageStore;
use rollun\service\Parser\Ebay\DataStore\Page\Search\Simple as SimpleSearchPageStore;
use rollun\service\Parser\Ebay\DataStore\Page\Search\EbayMotors as EbayMotorsSearchPageStore;

use rollun\parser\DataStore\Page\Factory\BaseFactory as BasePageStoreFactory;
use rollun\parser\DataStore\Page\Factory\AbstractPageFactory as AbstractPageStoreFactory;
use rollun\service\Parser\Ebay\DataStore\Page\Search\SearchFactory as BaseSearchPageStoreFactory;
use rollun\parser\DataStore\Page\PageDetectorFactory as PageStoreDetectorFactory;

use rollun\parser\Parser\ParserResolver\SimpleAbstractFactory as SimpleParserResolverAbstractFactory;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.zendframework.com/zend-component-installer/
 */
class ConfigProvider
{
    const __NAMESPACE__ = __NAMESPACE__;

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
                    ProductParserManager::class => ProductParserManagerFactory::class,
                    SearchParserManager::class => SearchParserManagerFactory::class,
                    CompatibleParserManager::class => CompatibleParserManagerFactory::class,
                    SoldSearchParserManager::class => SoldEbayMotorsSearchParserManagerFactory::class,

                    // Store factories
                    CompatiblePageStore::class => BasePageStoreFactory::class,
                    ProductPageStore::class => BasePageStoreFactory::class,
                    SimpleSearchPageStore::class => BaseSearchPageStoreFactory::class,
                    EbayMotorsSearchPageStore::class => BaseSearchPageStoreFactory::class,

                    SearchPageHelper::class => SearchPageFactory::class,

                    __NAMESPACE__ . 'searchLoaderManager' => SearchLoaderManagerFactory::class,
                    __NAMESPACE__ . 'baseLoaderManager' => BaseLoaderManagerFactory::class,
                    __NAMESPACE__ . 'soldSearchLoaderManager' => SearchLoaderManagerFactory::class,
                ],
                'aliases' => [
                    // Page store (out)
                    'ebay-product-page-store' => ProductPageStore::class,
                    'ebay-simple-search-page-store' => SimpleSearchPageStore::class,
                    'ebay-motors-search-page-store' => EbayMotorsSearchPageStore::class,
                    'ebay-compatible-page-store' => CompatiblePageStore::class,
                ],
            ],
            SimpleParserResolverAbstractFactory::class => [
                __NAMESPACE__ . 'searchParserResolver' => [
                    SimpleSearchParser::class ,
                    EbayMotorsSearchParser::class,
                ],
            ],
            LazyParserAbstractFactory::class => [
                __NAMESPACE__ . 'searchLazyParser' => [
                    LazyParserAbstractFactory::KEY_PARSER_NAME => 'ebaySearch',
                    LazyParserAbstractFactory::KEY_PARSER_RESOLVER => __NAMESPACE__ . 'searchParserResolver',
                ],
                __NAMESPACE__ . 'soldSearchLazyParser' => [
                    LazyParserAbstractFactory::KEY_PARSER_NAME => 'soldEbaySearch',
                    LazyParserAbstractFactory::KEY_PARSER_RESOLVER => __NAMESPACE__ . 'searchParserResolver',
                ],
            ],
            PageDetectorFactory::class => [
                'ebay-product-page-store' => "/https\:\/\/www\.ebay\.com\/itm\/[0-9]+/",
                'ebay-simple-search-page-store' => "/https\:\/\/www\.ebay\.com\/sch\/eBay-Motors\//",
                'ebay-motors-search-page-store' => '/https\:\/\/www\.ebay\.com\/sch\//',
                'ebay-compatible-page-store' => "/https\:\/\/frame\.ebay\.com\/ebaymotors\/ws\/eBayISAPI\.dll"
                    . "?GetFitmentData/",
            ],
            PageStoreDetectorFactory::class => [
                ProductPageStore::class => "/https\:\/\/www\.ebay\.com\/itm\/[0-9]+/",
                SimpleSearchPageStore::class => "/https\:\/\/www\.ebay\.com\/sch\/eBay-Motors\//",
                EbayMotorsSearchPageStore::class => '/https\:\/\/www\.ebay\.com\/sch\//',
                CompatiblePageStore::class => "/https\:\/\/frame\.ebay\.com\/ebaymotors\/ws\/eBayISAPI\.dll"
                    . "?GetFitmentData/",

            ],
            AbstractLoaderManagerFactory::KEY => [
                __NAMESPACE__ . 'searchLoaderManager' => [
                    BaseLoaderManagerFactory::KEY_CLASS => SearchLoaderManager::class,
                    SearchLoaderManagerFactory::KEY_LOADER => LoaderInterface::class,
                    SearchLoaderManagerFactory::KEY_LOADER_TASK_DATASTORE => LoaderTaskInterface::class,
                    SearchLoaderManagerFactory::KEY_PARSER_TASK_DATASTORE => ParserTaskInterface::class,
                    SearchLoaderManagerFactory::KEY_SEARCH_PAGE_HELPER => SearchPageHelper::class,
                    SearchLoaderManagerFactory::KEY_PARSER_NAMES => [
                        'ebaySearch'
                    ],
                ],
                __NAMESPACE__ . 'soldSearchLoaderManager' => [
                    BaseLoaderManagerFactory::KEY_CLASS => SoldProductSearchLoaderManager::class,
                    SearchLoaderManagerFactory::KEY_LOADER => LoaderInterface::class,
                    SearchLoaderManagerFactory::KEY_LOADER_TASK_DATASTORE => LoaderTaskInterface::class,
                    SearchLoaderManagerFactory::KEY_PARSER_TASK_DATASTORE => ParserTaskInterface::class,
                    SearchLoaderManagerFactory::KEY_SEARCH_PAGE_HELPER => SearchPageHelper::class,
                    SearchLoaderManagerFactory::KEY_PARSER_NAMES => [
                        'SoldEbaySearch'
                    ],
                ],
                __NAMESPACE__ . 'baseLoaderManager' => [
                    BaseLoaderManagerFactory::KEY_CLASS => BaseLoaderManager::class,
                    BaseLoaderManagerFactory::KEY_LOADER => LoaderInterface::class,
                    BaseLoaderManagerFactory::KEY_LOADER_TASK_DATASTORE => LoaderTaskInterface::class,
                    BaseLoaderManagerFactory::KEY_PARSER_TASK_DATASTORE => ParserTaskInterface::class,
                    BaseLoaderManagerFactory::KEY_PARSER_NAMES => [
                        ProductParser::PARSER_NAME,
                        CompatibleParser::PARSER_NAME,
                    ],
                ],
            ],
            SearchPageFactory::class => [
                SearchPageFactory::KEY_REDIRECT_URI => 'https://www.ebay.com/sch/FindingCustomization/'
                    . '?_fcdm=1&_fcss=12&_fcps=3&_fcippl=2&_fcso=1&_fcpd=1&_fcsbm=1&_pppn=v3'
                    . '&_fcpe=7%7C5%7C3%7C2%7C4&_fcie=1%7C36&_fcse=10%7C42%7C43&_fcsp=',
                SearchPageFactory::KEY_CLIENT => LoaderInterface::class,
            ],
            AbstractPageStoreFactory::KEY => [
                ProductPageStore::class => [
                    BasePageStoreFactory::KEY_LOADER => LoaderInterface::class,
                    BasePageStoreFactory::KEY_PARSER => ProductParser::class,
                ],
                SimpleSearchPageStore::class => [
                    BaseSearchPageStoreFactory::KEY_LOADER => LoaderInterface::class,
                    BaseSearchPageStoreFactory::KEY_PARSER => SimpleSearchParser::class,
                    BaseSearchPageStoreFactory::KEY_SEARCH_PAGE_HELPER => SearchPageHelper::class,
                ],
                EbayMotorsSearchPageStore::class => [
                    BaseSearchPageStoreFactory::KEY_LOADER => LoaderInterface::class,
                    BaseSearchPageStoreFactory::KEY_PARSER => EbayMotorsSearchParser::class,
                    BaseSearchPageStoreFactory::KEY_SEARCH_PAGE_HELPER => SearchPageHelper::class,
                ],
                CompatiblePageStore::class => [
                    BasePageStoreFactory::KEY_LOADER => LoaderInterface::class,
                    BasePageStoreFactory::KEY_PARSER => CompatibleParser::class,
                ],
            ],
            CallbackAbstractFactoryAbstract::KEY => [
                // Loaders
                __NAMESPACE__ . 'ebayProductLoaders' => [
                    MultiplexerAbstractFactory::KEY_CLASS => Multiplexer::class,
                    MultiplexerAbstractFactory::KEY_CALLBACKS_SERVICES => [
                        __NAMESPACE__ . 'baseLoaderProcess',
                        __NAMESPACE__ . 'searchLoaderProcess',
                    ],
                ],

                // Parsers
                __NAMESPACE__ . 'ebayProductParsers' => [
                    MultiplexerAbstractFactory::KEY_CLASS => Multiplexer::class,
                    MultiplexerAbstractFactory::KEY_CALLBACKS_SERVICES => [
                        __NAMESPACE__ . 'searchParserProcess',
                        __NAMESPACE__ . 'productParserProcess',
                        __NAMESPACE__ . 'compatibleParserProcess',
                    ],
                ],

                // Loaders + Parsers in Multiplexer
                __NAMESPACE__ . 'productMultiplexer' => [
                    MultiplexerAbstractFactory::KEY_CLASS => Multiplexer::class,
                    MultiplexerAbstractFactory::KEY_CALLBACKS_SERVICES => [
                        __NAMESPACE__ . 'ebayProductLoaders',
                        __NAMESPACE__ . 'ebayProductParsers',
                    ],
                ],
                __NAMESPACE__ . 'soldProductMultiplexer' => [
                    MultiplexerAbstractFactory::KEY_CLASS => Multiplexer::class,
                    MultiplexerAbstractFactory::KEY_CALLBACKS_SERVICES => [
                        __NAMESPACE__ . 'soldSearchLoaderProcess',
                        __NAMESPACE__ . 'soldSearchParserProcess',
                    ],
                ],

                // Tickers
                __NAMESPACE__ . 'productTicker' => [
                    TickerAbstractFactory::KEY_CLASS => Ticker::class,
                    TickerAbstractFactory::KEY_TICKS_COUNT => intval(getenv('EBAY_PRODUCT_TICK_COUNT')),
                    TickerAbstractFactory::KEY_TICK_DURATION => intval(60 / getenv('EBAY_PRODUCT_TICK_COUNT')),
                    TickerAbstractFactory::KEY_CALLBACK => __NAMESPACE__ . 'productMultiplexer',
                ],
                __NAMESPACE__ . 'soldProductTicker' => [
                    TickerAbstractFactory::KEY_CLASS => Ticker::class,
                    TickerAbstractFactory::KEY_TICKS_COUNT => intval(60 / getenv('EBAY_SOLD_PRODUCT_TICK_COUNT')),
                    TickerAbstractFactory::KEY_TICK_DURATION => intval(getenv('EBAY_SOLD_PRODUCT_TICK_COUNT')),
                    TickerAbstractFactory::KEY_CALLBACK => __NAMESPACE__ . 'soldProductMultiplexer',
                ],
            ],
            InterruptAbstractFactoryAbstract::KEY => [
                'ebayProductProcess' => [
                    ProcessAbstractFactory::KEY_CLASS => Process::class,
                    ProcessAbstractFactory::KEY_CALLBACK_SERVICE => __NAMESPACE__ . 'productTicker',
                ],
                'ebaySoldProductProcess' => [
                    ProcessAbstractFactory::KEY_CLASS => Process::class,
                    ProcessAbstractFactory::KEY_CALLBACK_SERVICE => __NAMESPACE__ . 'soldProductTicker',
                ],
                __NAMESPACE__ . 'baseLoaderProcess' => [
                    ProcessAbstractFactory::KEY_CLASS => Process::class,
                    ProcessAbstractFactory::KEY_CALLBACK_SERVICE => __NAMESPACE__ . 'baseLoaderManager',
                ],
                __NAMESPACE__ . 'searchLoaderProcess' => [
                    ProcessAbstractFactory::KEY_CLASS => Process::class,
                    ProcessAbstractFactory::KEY_CALLBACK_SERVICE => __NAMESPACE__ . 'searchLoaderManager',
                ],
                __NAMESPACE__ . 'soldSearchLoaderProcess' => [
                    ProcessAbstractFactory::KEY_CLASS => Process::class,
                    ProcessAbstractFactory::KEY_CALLBACK_SERVICE => __NAMESPACE__ . 'soldSearchLoaderManager',
                ],
                __NAMESPACE__ . 'searchParserProcess' => [
                    ProcessAbstractFactory::KEY_CLASS => Process::class,
                    ProcessAbstractFactory::KEY_CALLBACK_SERVICE => SearchParserManager::class,
                ],
                __NAMESPACE__ . 'productParserProcess' => [
                    ProcessAbstractFactory::KEY_CLASS => Process::class,
                    ProcessAbstractFactory::KEY_CALLBACK_SERVICE => ProductParserManager::class,
                ],
                __NAMESPACE__ . 'compatibleParserProcess' => [
                    ProcessAbstractFactory::KEY_CLASS => Process::class,
                    ProcessAbstractFactory::KEY_CALLBACK_SERVICE => CompatibleParserManager::class,
                ],
                __NAMESPACE__ . 'soldSearchParserProcess' => [
                    ProcessAbstractFactory::KEY_CLASS => Process::class,
                    ProcessAbstractFactory::KEY_CALLBACK_SERVICE => SoldSearchParserManager::class,
                ],
            ],
            AbstractParserManagerFactory::KEY => [
                SearchParserManager::class => [
                    SearchParserManagerFactory::KEY_PARSER => __NAMESPACE__ . 'searchLazyParser',
                    SearchParserManagerFactory::KEY_PARSER_TASK_DATASTORE => ParserTaskStoreEntityInterface::class,
                    SearchParserManagerFactory::KEY_LOADER_TASK_DATASTORE => LoaderTaskStoreEntityInterface::class,
                    SearchParserManagerFactory::KEY_PARSE_RESULT_DATASTORE => ProductEntityStoreInterface::class,
                    SearchParserManagerFactory::KEY_OPTIONS => [
                        'createProductParseTask' => 1,
                        'productUri' => 'https://www.ebay.com/itm',
                        'maxCorruptRecords' => 30,
                        'saveCorruptedProducts' => 1,
                        'throughPagination' => 1,
                    ],
                ],
                SoldSearchParserManager::class => [
                    SearchParserManagerFactory::KEY_PARSER => __NAMESPACE__ . 'soldSearchLazyParser',
                    SearchParserManagerFactory::KEY_PARSER_TASK_DATASTORE => ParserTaskStoreEntityInterface::class,
                    SearchParserManagerFactory::KEY_LOADER_TASK_DATASTORE => LoaderTaskStoreEntityInterface::class,
                    SearchParserManagerFactory::KEY_PARSE_RESULT_DATASTORE => SoldProductEntityStoreInterface::class,
                    SearchParserManagerFactory::KEY_OPTIONS => [
                        'maxCorruptRecords' => 30,
                        'saveCorruptedProducts' => 1,
                        'throughPagination' => 0,
                    ],
                ],
                ProductParserManager::class => [
                    ProductParserManagerFactory::KEY_PARSER => ProductParser::class,
                    ProductParserManagerFactory::KEY_PARSER_TASK_DATASTORE => ParserTaskStoreEntityInterface::class,
                    ProductParserManagerFactory::KEY_PARSE_RESULT_DATASTORE => ProductEntityStoreInterface::class,
                    ProductParserManagerFactory::KEY_LOADER_TASK_DATASTORE => LoaderTaskStoreEntityInterface::class,
                    ProductParserManagerFactory::KEY_OPTIONS => [
                        'createCompatibleTask' => 1,
                        'compatibleUriEbayId' => 'https://frame.ebay.com/ebaymotors/ws/eBayISAPI.dll'
                            . '?GetFitmentData&req=1&cid=177773&ct=100&page=1&pid=',
                        'compatibleUriItemId' => 'https://frame.ebay.com/ebaymotors/ws/eBayISAPI.dll'
                            . '?GetFitmentData&req=2&ct=1000&page=1&item=',
                    ],
                ],
                CompatibleParserManager::class => [
                    CompatibleParserManagerFactory::KEY_PARSER => CompatibleParser::class,
                    CompatibleParserManagerFactory::KEY_PARSER_TASK_DATASTORE => ParserTaskStoreEntityInterface::class,
                    CompatibleParserManagerFactory::KEY_LOADER_TASK_DATASTORE => LoaderTaskStoreEntityInterface::class,
                    CompatibleParserManagerFactory::KEY_PARSE_RESULT_DATASTORE => CompatibleEntityStoreInterface::class,
                    CompatibleParserManagerFactory::KEY_OPTIONS => [
                        'createCompatibleTask' => 1,
                    ],
                ],
            ],
            DataStoreAbstractFactory::KEY_DATASTORE => [
                __NAMESPACE__ . 'productEntityStore' => [
                    'class' => LoggedDbTable::class,
                    'tableGateway' => 'products',
                ],
                __NAMESPACE__ . 'soldProductEntityStore' => [
                    'class' => LoggedDbTable::class,
                    'tableGateway' => 'sold_products',
                ],
                __NAMESPACE__ . 'compatibleEntityStore' => [
                    'class' => LoggedDbTable::class,
                    'tableGateway' => 'compatibles',
                ],
                __NAMESPACE__ . 'productEntityStoreAutoGeneratedId' => [
                    'class' => AutoGenerateIdAspect::class,
                    'dataStore' => __NAMESPACE__ . 'productEntityStore',
                ],
                __NAMESPACE__ . 'soldProductEntityStoreAutoGeneratedId' => [
                    'class' => AutoGenerateIdAspect::class,
                    'dataStore' => __NAMESPACE__ . 'soldProductEntityStore',
                ],
                __NAMESPACE__ . 'compatibleEntityStoreAutoGeneratedId' => [
                    'class' => AutoGenerateIdAspect::class,
                    'dataStore' => __NAMESPACE__ . 'compatibleEntityStore',
                ],
                ProductEntityStoreInterface::class => [
                    'class' => ProductEntityStore::class,
                    'dataStore' => __NAMESPACE__ . 'productEntityStoreAutoGeneratedId',
                ],
                SoldProductEntityStoreInterface::class => [
                    'class' => SoldProductEntityStore::class,
                    'dataStore' => __NAMESPACE__ . 'soldProductEntityStoreAutoGeneratedId',
                ],
                CompatibleEntityStoreInterface::class => [
                    'class' => CompatibleEntityStore::class,
                    'dataStore' => __NAMESPACE__ . 'compatibleEntityStoreAutoGeneratedId',
                ],
            ],
            'tableGateway' => [
                'products' => [],
                'compatibles' => [],
                'sold_products' => [],
            ],
        ];
    }
}
