<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Parser;

use phpQuery as PhpQuery;

class SellerParser extends AbstractParser
{
    public const PARSER_NAME = 'ebaySeller';

    public function parse(string $html): array
    {
        $document = PhpQuery::newDocument($html);

        $sellerUrl = $document->find('#mbgLink')->attr('href');
        $parts = parse_url($sellerUrl);
        parse_str($parts['query'], $sellerId);

        $seller['sellerId'] = $sellerId['_trksid'];
        $seller['sellerScore'] = $document->find('.mbg-l')->children('a')->eq(0)->text();

        $seller['shipping']['cost'] = $document->find('#fshippingCost>span')->text();
        $seller['shipping']['service'] = $document->find('#fShippingSvc')->text();

        $seller['sold'] = $document->find('.vi-qty-pur-lnk')->children('a')->text();
        $seller['sold'] = str_replace(' sold', '', $seller['sold']);

        return $seller;
    }
}
