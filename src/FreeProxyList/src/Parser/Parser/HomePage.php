<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\FreeProxyList\Parser\Parser;

use phpQuery as PhpQuery;
use rollun\parser\Parser\Parser\HtmlParser;
use rollun\parser\Parser\Parser\ParserInterface;
use rollun\parser\Parser\ParserResolver\ParserResolverInterface;

final class HomePage extends HtmlParser implements ParserResolverInterface
{
    const PARSER_NAME = 'proxy';

    public function parse(string $data): array
    {
        $document = PhpQuery::newDocument($data);

        $proxiesTr = $document->find('#proxylisttable tbody tr');
        $proxies = [];

        foreach ($proxiesTr as $tr) {
            $pq = pq($tr);

            $country = trim($pq->find('td')->eq(3)->text());

            if ($country != 'United States') {
                continue;
            }

            $host = trim($pq->find('td')->eq(0)->text());
            $port = trim($pq->find('td')->eq(1)->text());
            $isHttp = trim($pq->find('td')->eq(6)->text());


            $scheme = $isHttp == 'yes' ? 'https' : 'http';

            $proxies[] = "$scheme://$host:$port";
        }

        return $proxies;
    }

    public function getParser($document): ?ParserInterface
    {
        return new self();
    }

    public function canParse(string $data): bool
    {
        $document = PhpQuery::newDocument($data);

        return boolval($document->find('#proxylisttable tbody tr'));
    }
}
