<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Manager;

use GuzzleHttp\Client;
use Parser\DataStore\HtmlDataStore;
use Parser\DataStore\HtmlDataStoreInterface;
use Parser\Loader\Loader;
use Parser\Loader\LoaderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use rollun\datastore\DataStore\Memory;
use rollun\logger\SimpleLogger;
use Zend\Diactoros\Response\EmptyResponse;

class LoaderManager implements RequestHandlerInterface
{
    protected $loader;

    protected $htmlDataStore;

    protected $logger;

    /**
     * LoaderManager constructor.
     * @param LoaderInterface|null $loader
     * @param HtmlDataStoreInterface|null $dataStore
     */
    public function __construct(
        LoaderInterface $loader = null,
        HtmlDataStoreInterface $dataStore = null
    ) {
        $this->loader = $loader;
        $this->htmlDataStore = $dataStore;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \rollun\datastore\DataStore\DataStoreException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->loader = new Loader(
            new UserAgentGenerator(),
            new ProxyManager([
                'http://103.28.226.83:46351',
                'http://58.26.10.67:8080',
            ]),
            function($proxy, $headers) {
                return new Client([
                    'proxy' => $proxy,
                    'headers' => $headers,
                ]);
            },
            new SimpleLogger()
        );

        $this->htmlDataStore = new HtmlDataStore(new Memory(), 'data');

        $uri = 'https://www.ebay.com/sch/i.html?_nkw=tusk&_sacat=6000&_sop=2&_ipg=200';
        $html = $this->loader->load($uri) ?? '';
        $this->htmlDataStore->create([
            'html' => $html,
            'time' => time(),
            'status' => 0,
        ]);

        return new EmptyResponse(200);
    }
}
