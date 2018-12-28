<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\FreeProxyList\Parser\Parser;

use phpQuery as PhpQuery;
use rollun\parser\Parser\Parser\HtmlParser;

class HomePage extends HtmlParser
{
    const PARSER_NAME = 'proxy';

    public function parse(string $data): array
    {
        $document = PhpQuery::newDocument($data);

        $proxiesTr = $document->find('#proxylisttable tbody tr');
        $proxies = [];

        foreach ($proxiesTr as $tr) {
            $pq = pq($tr);
            $host = $pq->find('td')->eq(0)->text();
            $port = $pq->find('td')->eq(1)->text();
            $isHttp = $pq->find('td')->eq(6)->text();

            $scheme = $isHttp == 'yes' ? 'https' : 'http';

            $proxies[] = "$scheme://$host:$port";
        }

        return $proxies;
    }
}
