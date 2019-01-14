<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\Ebay\Parser\Parser\Search;

use rollun\parser\Parser\Parser\HtmlParser;
use phpQuery as PhpQuery;

class SoldEbayMotors extends HtmlParser
{
    public const PARSER_NAME = 'soldEbaySimpleSearch';

    public function parse(string $data): array
    {
        $document = PhpQuery::newDocument($data);
        $itemCards = $document->find('#ListViewInner > li');
        $products = [];

        foreach ($itemCards as $key => $itemCard) {
            $pq = pq($itemCard);

            if ($pq->find('.kand-expansion')->count()) {
                continue;
            }

            $products[$key]['uri'] = $pq->find('.lvtitle a')->attr('href');
            $urlComponents = parse_url($products[$key]['uri']);
            $path = $urlComponents['path'];
            $parts = explode('/', $path);

            $products[$key]['item_id'] = end($parts);

            if ($pq->find('.prRange')->count()) {
                $priceRange = $pq->find('.prRange')->text();
                [$from, ,$to] = explode(' ', $priceRange);
                $products[$key]['price'] = trim($from) . '-' . trim($to);
            } else {
                $products[$key]['price'] = trim($pq->find('.lvprice > span')->text());
            }

            $products[$key]['shipping']['cost'] = trim($pq->find('.lvshipping .fee')->text());

            // Filter trash
            $products[$key]['shipping']['cost'] = str_replace(
                [' shipping', '+'],
                '',
                $products[$key]['shipping']['cost']
            );

            $products[$key]['shipping'] = implode(' ', $products[$key]['shipping']);
            $products[$key]['title'] = trim($pq->find('.lvtitle a')->text());

            $sellerInfo = trim($pq->find('.lvdetails li')->eq(1)->text());
            preg_match('/Seller:\s+([\w\W]+)\(.+\)/', $sellerInfo, $matches);
            $products[$key]['seller'] = $matches[1] ?? '';

            $products[$key]['date'] = $pq->find('.timeleft .tme span')->text();
        }

        $result['products'] = $products;
        $result['nextPage'] = $document->find('#Pagination .pages .curr + a')->attr('href');

        return $result;
    }
}
