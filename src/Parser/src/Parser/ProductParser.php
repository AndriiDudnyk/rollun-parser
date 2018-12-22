<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Parser;

use phpQuery as PhpQuery;

class ProductParser extends AbstractParser
{
    public const PARSER_NAME = 'ebayProduct';

    /**
     * @param string $html
     * @return array|mixed
     */
    public function parse(string $html): array
    {
        $document = PhpQuery::newDocument($html);

        $sellerUrl = $document->find('#mbgLink')->attr('href');
        $parts = parse_url($sellerUrl);
        parse_str($parts['query'], $sellerId);

        $product['sellerId'] = $sellerId['_trksid'];
        $product['title'] = $document->find('.it-ttl')->text();
        $product['price'] = $document->find('#mm-saleDscPrc')->text();
        $product['shipping']['cost'] = $document->find('#fshippingCost>span')->text();
        $product['shipping']['service'] = $document->find('#fShippingSvc')->text();

        $catLine = $document->find('.vi-VR-brumb-hasNoPrdlnks li a span');
        $product['category'] = '';

        foreach ($catLine as $cat) {
            $pq = pq($cat);
            $product['category'] .= '>' . $pq->text();
        }

        $itemImages = $document->find('#mainImgHldr>img');

        foreach ($itemImages as $img) {
            $pq = pq($img);
            $product['imgUrl'][] = $pq->attr('src');
        }

        $itemSpecs = $document->find('.itemAttr tr');
        $specs = [];

        foreach ($itemSpecs as $tr) {
            $pq = pq($tr);
            $key = count($specs);
            $specs[$key]['specName'] = $pq->find('td')->eq(0)->text();
            $specs[$key]['specDesc'] = $pq->find('td')->eq(1)->text();

            $specs[++$key]['specName'] = $pq->find('td')->eq(2)->text();
            $specs[++$key]['specDesc'] = $pq->find('td')->eq(3)->text();
        }

        $product['spec'] = $specs;

        return $product;
    }
}
