<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\Ebay\Loader\Manager;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Log\LoggerInterface;
use rollun\parser\DataStore\Entity\LoaderTaskInterface;
use rollun\parser\DataStore\Entity\ParserTaskInterface;
use rollun\parser\Loader\Loader\LoaderInterface;
use rollun\parser\Loader\Manager\Base as BaseLoaderManager;
use rollun\service\Parser\Ebay\Helper\SearchPage as SearchPageHelper;
use RuntimeException;

class Search extends BaseLoaderManager
{
    protected $searchPageHelper;

    public function __construct(
        LoaderInterface $loader,
        LoaderTaskInterface $loaderTask,
        ParserTaskInterface $parserTask,
        SearchPageHelper $searchPageHelper,
        array $parserNames,
        LoggerInterface $logger = null
    ) {
        parent::__construct($loader, $loaderTask, $parserTask, $parserNames);
        $this->searchPageHelper = $searchPageHelper;
    }

    /**
     * @param $loaderTask
     * @return string
     * @throws ClientExceptionInterface
     * @throws RuntimeException
     */
    protected function getDocument($loaderTask)
    {
        $trueUri = $this->searchPageHelper->getTrueUri($loaderTask['uri']);
        $options = array_merge($loaderTask['options'], [
            LoaderInterface::COOKIES_OPTION => $this->searchPageHelper->getCookies($loaderTask['uri']),
            LoaderInterface::COOKIE_DOMAIN_OPTION => $this->searchPageHelper->getCookieDomain($loaderTask['uri']),
        ]);

        $loader = $this->getLoader($options);
        $document = $loader->load($trueUri) ?? '';

        return $document;
    }

    public function __sleep()
    {
        $properties = parent::__sleep();
        return array_merge($properties, ['searchPageHelper']);
    }
}
