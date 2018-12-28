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
        LoggerInterface $logger = null
    ) {
        parent::__construct($loader, $loaderTask, $parserTask);
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


        $loader = $this->getLoader($loaderTask['options']);
        $document = $loader->load($loaderTask['uri']) ?? '';

        return $document;
    }
}
