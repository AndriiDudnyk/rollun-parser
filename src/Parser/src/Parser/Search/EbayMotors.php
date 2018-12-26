<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Parser\Search;

use Parser\Parser\AbstractParser;
use phpQuery as PhpQuery;

class EbayMotors extends AbstractParser
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
            $priceRange = $pq->find('.prRange')->text();
            [$products[$key]['price'],,] = explode(' ', $priceRange);

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
