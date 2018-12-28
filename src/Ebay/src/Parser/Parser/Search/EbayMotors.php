<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\service\Parser\Ebay\Parser\Parser\Search;

use phpQuery as PhpQuery;
use rollun\parser\Parser\Parser\HtmlParser;

class EbayMotors extends HtmlParser
{
    public const PARSER_NAME = 'ebayMotorsSearch';

    public function parse(string $data): array
    {
        $document = PhpQuery::newDocument($data);
        $itemCards = $document->find('#ListViewInner > li');
        $products = [];

        foreach ($itemCards as $key => $itemCard) {
            $pq = pq($itemCard);

            $products[$key]['url'] = $pq->find('.lvtitle a')->attr('href');
            $urlComponents = parse_url($products[$key]['url']);
            $path = $urlComponents['path'];
            $parts = explode('/', $path);

            $products[$key]['item_id'] = end($parts);
            $products[$key]['img'] = $pq->find('.full-width a .img')->attr('src');

            if ($pq->find('.prRange')->count()) {
                $priceRange = $pq->find('.prRange')->text();
                [$from, ,$to] = explode(' ', $priceRange);
                $products[$key]['price'] = trim($from) . '-' . trim($to);
            } else {
                $products[$key]['price'] = trim($pq->find('.lvprice span')->text());
            }

            $products[$key]['shipping']['cost'] = trim($pq->find('.lvshipping .fee')->text());

            // Filter trash
            $products[$key]['shipping']['cost'] = str_replace(
                [' shipping', '+'],
                '',
                $products[$key]['shipping']['cost']
            );

            $products[$key]['shipping'] = implode(' ', $products[$key]['shipping']);

            $sellerInfo = trim($pq->find('.lvdetails li')->eq(2)->text());
            preg_match('/Seller:\s+([\w\W]+)\(.+\)/', $sellerInfo, $matches);
            $products[$key]['seller'] = $matches[1];

            $hotnessText = trim($pq->find('.watch a')->text());

            if (stristr($hotnessText, 'Watch')) {
                $products[$key]['watch'] = $hotnessText;
            }

            if (stristr($hotnessText, 'Sold')) {
                $products[$key]['sold'] = $hotnessText;
            }
        }

        return $products;
    }
}