<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace Parser\Parser\Search;

use Parser\Parser\AbstractParser;
use phpQuery as PhpQuery;

class Simple extends AbstractParser
{
    public const PARSER_NAME = 'ebaySearch';

    /**
     * @param string $data
     * @return array
     */
    public function parse(string $data): array
    {
        $document = PhpQuery::newDocument($data);
        $itemCards = $document->find('.s-item__wrapper');
        $products = [];

        foreach ($itemCards as $key => $itemCard) {
            $pq = pq($itemCard);

            $products[$key]['url'] = $pq->find('.s-item__link')->attr('href');
            $urlComponents = parse_url($products[$key]['url']);
            $path = $urlComponents['path'];
            $pathParts = explode('/', $path);

            $products[$key]['item_id'] = end($pathParts);
            $products[$key]['img'] = $pq->find('.s-item__image-img')->attr('src');
            $products[$key]['price'] = $pq->find('span.s-item__price')->text();
            $products[$key]['shipping']['cost'] = $pq->find('.s-item__shipping')->text();

            // Filter trash
            $products[$key]['shipping']['cost'] = str_replace(
                [' shipping', '+'],
                '',
                $products[$key]['shipping']['cost']
            );

            $products[$key]['shipping'] = implode(' ', $products[$key]['shipping']);

            $sellerInfo = $pq->find('.s-item__seller-info-text')->text();
            [,$sellerId,,,] = explode(' ', $sellerInfo);
            $products[$key]['seller'] = $sellerId;

            $hotnessText = $pq->find('.s-item__hotness>.NEGATIVE')->text();

            if (stristr($hotnessText, 'Watching')) {
                $products[$key]['watching'] = $hotnessText;
            }

            if (stristr($hotnessText, 'Sold')) {
                $products[$key]['sold'] = $hotnessText;
            }
        }

        return $products;
    }
}
