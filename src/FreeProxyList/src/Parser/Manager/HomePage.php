<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\FreeProxyList\Manager\Parser;

use Psr\Log\LoggerInterface;
use rollun\parser\DataStore\Entity\ParserTaskInterface;
use rollun\parser\Parser\Manager\Base as BaseParserManager;
use rollun\service\Parser\FreeProxyList\DataStore\Entity\ProxyInterface;
use rollun\service\Parser\FreeProxyList\Parser\Parser\HomePage as HomePageParser;

class HomePage extends BaseParserManager
{
    /**
     * @var ProxyInterface
     */
    protected $entity;

    public function __construct(
        HomePageParser $parser,
        ProxyInterface $proxyDataStore,
        ParserTaskInterface $parserTask,
        array $options,
        LoggerInterface $logger = null
    ) {
        parent::__construct($parser, $proxyDataStore, $parserTask, $options);
    }

    protected function saveResult(array $records)
    {
        foreach ($records as $uri) {
            $this->entity->addNewUri($uri);
        }
    }
}
