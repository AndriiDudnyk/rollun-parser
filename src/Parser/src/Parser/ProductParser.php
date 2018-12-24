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
     * @param string $data
     * @return array|mixed
     */
    public function parse(string $data): array
    {
        $document = PhpQuery::newDocument($data);

        $sellerUrl = $document->find('#mbgLink')->attr('href');
        $parts = parse_url($sellerUrl);
        parse_str($parts['query'], $sellerId);

        $products['title'] = $document->find('.it-ttl')->text();

        if ($document->find('#vi-cdown_timeLeft')->count()) {
            $products['price'] = $document->find('#prcIsum_bidPrice')->text();
        } else {
            $products['price'] = $document->find('#prcIsum')->text();
        }

        $products['shipping']['cost'] = $document->find('#fshippingCost>span')->text();
        $products['shipping']['service'] = $document->find('#fShippingSvc')->text();

        $catLine = $document->find('.vi-VR-brumb-hasNoPrdlnks li a span');
        $products['category'] = '';

        foreach ($catLine as $cat) {
            $pq = pq($cat);
            $products['category'] .= '>' . $pq->text();
        }

        $itemImages = $document->find('#mainImgHldr>img');

        foreach ($itemImages as $img) {
            $pq = pq($img);
            $products['imgs'][] = $pq->attr('src');
        }

        $itemSpecs = $document->find('.itemAttr tr');
        $specs = [];

        foreach ($itemSpecs as $tr) {
            $pq = pq($tr);
            $key = count($specs);

            $specs[$key]['name'] = trim(trim($pq->find('td')->eq(0)->text()), ':');
            $specs[$key]['value'] = trim($pq->find('td')->eq(1)->text());

            if ($pq->find('td')->eq(2)->count()) {
                $key++;
                $specs[$key]['name'] =  trim(trim($pq->find('td')->eq(2)->text()), ':');
                $specs[$key]['value'] = trim($pq->find('td')->eq(3)->text());
            }
        }

        $products['specs'] = $specs;

        return $products;
    }
}
